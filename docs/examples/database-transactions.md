# Database Transactions

Use this flow when you need to ensure consistency between your local database and Paystack operations. Laravel's database transactions guarantee that either all operations succeed together, or all are rolled back if something fails.

## Why Use Database Transactions for Payments

Payment flows often involve multiple database writes that must stay in sync:

- **Order + Payment** — Create an order and record the payment reference atomically
- **Payment + Inventory** — Decrement stock only if payment initialization succeeds
- **Refund + Balance** — Update customer balance and create refund record together
- **Subscription + Invoice** — Create subscription and generate invoice atomically

Without transactions, a failure mid-flow leaves your database in an inconsistent state.

## Basic Transaction Pattern

Wrap Paystack SDK calls and local database operations in a transaction. If the Paystack call fails, the transaction rolls back and no local records are created.

```php
namespace App\Services\Billing;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class CreateOrderWithPayment
{
    public function handle(array $orderData, array $paymentData): array
    {
        return DB::transaction(function () use ($orderData, $paymentData): array {
            // 1. Create the order locally
            $order = Order::query()->create([
                'user_id' => $orderData['user_id'],
                'total_amount' => $orderData['amount'],
                'status' => 'pending_payment',
            ]);

            // 2. Initialize payment with Paystack
            $response = Paystack::initializeTransaction(
                InitializeTransactionInputData::from([
                    'email' => $paymentData['email'],
                    'amount' => $orderData['amount'],
                    'reference' => 'order_' . $order->id . '_' . time(),
                    'callbackUrl' => route('payment.callback'),
                    'metadata' => [
                        'order_id' => $order->id,
                    ],
                ])
            );

            // 3. Store the payment reference locally
            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'reference' => $response->reference,
                'amount' => $orderData['amount'],
                'status' => 'pending',
                'authorization_url' => $response->authorizationUrl,
            ]);

            // 4. Update order with payment reference
            $order->update(['payment_id' => $payment->id]);

            return [
                'order' => $order,
                'payment' => $payment,
                'authorization_url' => $response->authorizationUrl,
            ];
        });
    }
}
```

**What happens on failure:**

- If `Order::create()` fails → Nothing is created, exception propagates
- If Paystack API call fails → Order is rolled back, no orphaned order without payment
- If `Payment::create()` fails → Order is rolled back, no Paystack transaction without local record
- If final `order->update()` fails → Everything is rolled back

## Transaction with Inventory Management

When payment success requires inventory changes, wrap both operations together:

```php
namespace App\Services\Billing;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class ProcessOrderWithInventory
{
    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     */
    public function handle(
        int $userId,
        array $items,
        string $email,
        int $totalAmount
    ): array {
        return DB::transaction(function () use ($userId, $items, $email, $totalAmount): array {
            // 1. Reserve inventory (decrement stock)
            $reservedProducts = [];

            foreach ($items as $item) {
                $product = Product::query()
                    ->lockForUpdate() // Prevent concurrent modifications
                    ->find($item['product_id']);

                if ($product === null || $product->stock < $item['quantity']) {
                    throw new \RuntimeException(
                        "Insufficient stock for product: {$product?->name}"
                    );
                }

                $product->decrement('stock', $item['quantity']);
                $reservedProducts[] = $product;
            }

            // 2. Create order
            $order = Order::query()->create([
                'user_id' => $userId,
                'status' => 'pending_payment',
                'total_amount' => $totalAmount,
            ]);

            // 3. Attach items to order
            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            // 4. Initialize Paystack transaction
            $response = Paystack::initializeTransaction(
                InitializeTransactionInputData::from([
                    'email' => $email,
                    'amount' => $totalAmount,
                    'reference' => 'order_' . $order->id . '_' . uniqid(),
                    'metadata' => [
                        'order_id' => $order->id,
                        'items' => $items,
                    ],
                ])
            );

            // 5. Create payment record
            $order->payment()->create([
                'reference' => $response->reference,
                'amount' => $totalAmount,
                'status' => 'pending',
                'authorization_url' => $response->authorizationUrl,
            ]);

            return [
                'order' => $order,
                'authorization_url' => $response->authorizationUrl,
            ];
        });
    }
}
```

**Key patterns:**

- **`lockForUpdate()`** — Prevents race conditions when decrementing stock
- **Fail fast** — Check stock before any writes; throw immediately if insufficient
- **Atomic inventory** — Stock is only decremented if the entire flow succeeds

## Handling Transaction Rollback

When a transaction fails, you may need to clean up external state. Use `DB::beforeExecuting` or catch exceptions to handle Paystack-side cleanup:

```php
namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class SafeOrderCreation
{
    public function handle(array $data): ?array
    {
        $paystackReference = null;

        try {
            return DB::transaction(function () use ($data, &$paystackReference): array {
                // Create local records...
                $order = \App\Models\Order::query()->create($data);

                // Initialize Paystack
                $response = Paystack::initializeTransaction(
                    InitializeTransactionInputData::from([
                        'email' => $data['email'],
                        'amount' => $data['amount'],
                        'reference' => 'order_' . $order->id,
                    ])
                );

                $paystackReference = $response->reference;

                // More local operations that might fail...
                $this->createPaymentRecord($order, $response);
                $this->updateInventory($data['items']);

                return ['order' => $order, 'url' => $response->authorizationUrl];
            });
        } catch (\Exception $e) {
            // Transaction rolled back, but Paystack transaction exists
            // Log for manual cleanup if needed
            if ($paystackReference !== null) {
                \Illuminate\Support\Facades\Log::warning(
                    'Transaction rolled back but Paystack reference created',
                    [
                        'reference' => $paystackReference,
                        'error' => $e->getMessage(),
                    ]
                );
            }

            throw $e;
        }
    }
}
```

## Nested Transactions and Savepoints

Laravel supports nested transactions via savepoints. This is useful when you have service methods that each use transactions:

```php
namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data): \App\Models\Order
    {
        return DB::transaction(function () use ($data): \App\Models\Order {
            $order = \App\Models\Order::query()->create($data);

            // Nested transaction (savepoint)
            $this->reserveInventory($order->items);

            return $order;
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem>  $items
     */
    private function reserveInventory($items): void
    {
        DB::transaction(function () use ($items): void {
            foreach ($items as $item) {
                $item->product->decrement('stock', $item->quantity);
            }
        });
    }
}
```

**How savepoints work:**

- Outer `DB::transaction()` creates a transaction
- Inner `DB::transaction()` creates a savepoint within that transaction
- If the inner transaction fails, only the savepoint is rolled back
- If the outer transaction fails, everything is rolled back

## Transaction Retry

For transient failures (deadlocks, temporary locks), use the `attempts` parameter:

```php
namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

class RetryableOrderCreation
{
    public function handle(array $data): array
    {
        // Retry up to 3 times with exponential backoff
        return DB::transaction(function () use ($data): array {
            // High-contention operations like inventory reservation
            $order = \App\Models\Order::query()->create($data);

            foreach ($data['items'] as $item) {
                \App\Models\Product::query()
                    ->where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->decrement('stock', $item['quantity']);
            }

            return ['order' => $order];
        }, 3); // 3 attempts
    }
}
```

**When to retry:**

- Deadlock exceptions (MySQL error 1213)
- Lock wait timeout exceeded
- High-contention inventory operations during flash sales

## Testing Transaction Behavior

Test that your transactions roll back correctly on failure:

```php
use App\Services\Billing\CreateOrderWithPayment;
use Illuminate\Support\Facades\DB;
use Maxiviper117\Paystack\Facades\Paystack;

test('transaction rolls back when paystack fails', function (): void {
    Paystack::shouldReceive('initializeTransaction')
        ->once()
        ->andThrow(new \RuntimeException('API Error'));

    $service = new CreateOrderWithPayment;

    $initialOrderCount = \App\Models\Order::query()->count();

    try {
        $service->handle(
            ['user_id' => 1, 'amount' => 50000],
            ['email' => 'test@example.com']
        );
    } catch (\RuntimeException) {
        // Expected
    }

    // No order should have been created
    expect(\App\Models\Order::query()->count())->toBe($initialOrderCount);
});

test('inventory is restored on transaction failure', function (): void {
    $product = \App\Models\Product::factory()->create(['stock' => 10]);

    DB::transaction(function () use ($product): void {
        $product->decrement('stock', 5);

        throw new \RuntimeException('Simulated failure');
    });

    // Stock should be restored
    expect($product->fresh()->stock)->toBe(10);
});
```

## Related pages

- [Queued Jobs](/examples/queued-jobs) — Process transactions asynchronously when synchronous flow isn't required
- [Testing Paystack Integrations](/examples/testing) — Test transaction rollback and failure scenarios
- [Error Handling](/examples/error-handling) — Handle exceptions that trigger transaction rollbacks
- [Manager and Facade Usage](/examples/manager-and-facade) — SDK methods used within transactions