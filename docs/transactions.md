# Transactions

Transactions currently support initialize, verify, fetch, and list operations.

## Initialize a transaction

```php
namespace App\Services\Billing;

use App\Models\Order;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;

class StartCheckout
{
    public function __construct(
        private InitializeTransactionAction $initializeTransaction,
    ) {}

    public function handle(Order $order): string
    {
        $response = ($this->initializeTransaction)(
            InitializeTransactionInputData::from([
                'email' => $order->customer_email,
                'amount' => $order->total_amount,
                'channels' => ['card', 'bank_transfer'],
                'callbackUrl' => route('billing.paystack.callback'),
                'reference' => 'order_'.$order->getKey(),
                'currency' => 'NGN',
                'metadata' => [
                    'order_id' => $order->getKey(),
                ],
            ])
        );

        return $response->authorizationUrl;
    }
}
```

`InitializeTransactionInputData` covers the documented initialize body parameters:

- `email`
- `amount`
- `channels`
- `currency`
- `reference`
- `callback_url`
- `plan`
- `invoice_limit`
- `metadata`
- `split_code`
- `subaccount`
- `transaction_charge`
- `bearer`

Additional request fields can still be passed through `extra` when needed.

## Verify a transaction

```php
namespace App\Services\Billing;

use App\Models\Payment;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;

class ConfirmPaystackPayment
{
    public function __construct(
        private VerifyTransactionAction $verifyTransaction,
    ) {}

    public function handle(Payment $payment): void
    {
        $response = ($this->verifyTransaction)(
            VerifyTransactionInputData::from([
                'reference' => $payment->payment_reference,
            ])
        );

        if ($response->transaction->status !== 'success') {
            return;
        }

        $payment->status = 'paid';
        $payment->paid_at = $response->transaction->paidAt;
        $payment->save();
    }
}
```

In PHP, timestamp fields on response DTOs are typed. For example, `$response->transaction->paidAt` is a `CarbonImmutable|null`.

If you are in a controller or route that should return JSON, you can return the response DTO directly:

```php
use Illuminate\Http\Request;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;

Route::get('/billing/paystack/callback', function (
    Request $request,
    VerifyTransactionAction $verifyTransaction,
) {
    return $verifyTransaction(
        VerifyTransactionInputData::from([
            'reference' => (string) $request->query('reference', ''),
        ])
    );
});
```

## Fetch and list

Available action classes:

- `FetchTransactionAction`
- `ListTransactionsAction`

Matching input DTOs:

- `FetchTransactionInputData`
- `ListTransactionsInputData`

Matching response DTOs:

- `FetchTransactionResponseData`
- `ListTransactionsResponseData`

`FetchTransactionInputData` expects the numeric transaction `id` documented by Paystack.

`ListTransactionsInputData` supports the documented query filters for `customer`, `terminalId`/`terminalid`, `status`, `from`, `to`, `amount`, and `reference`, in addition to the standard `perPage` and `page` pagination inputs.

The list `status` filter is enum-backed through `TransactionStatus` with the documented values `failed`, `success`, and `abandoned`.

Transaction response DTOs also expose the broader backed `TransactionStatus` enum for verified and listed transactions, including `ongoing`, `pending`, `processing`, `queued`, and `reversed`.

## Amount handling

`InitializeTransactionInputData` accepts the amount in major currency units and converts it to Paystack subunits during request serialization. For example, `15.50` becomes `1550`.

## Facade usage

```php
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$response = Paystack::initializeTransaction(
    InitializeTransactionInputData::from([
        'email' => 'customer@example.com',
        'amount' => 15.50,
        'callbackUrl' => 'https://example.com/payments/callback',
    ])
);
```

## Action alternative

```php
namespace App\Services\Billing;

use App\Models\Order;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;

class StartCheckoutWithActions
{
    public function __construct(
        private InitializeTransactionAction $initializeTransaction,
        private VerifyTransactionAction $verifyTransaction,
    ) {}

    public function initialize(Order $order): string
    {
        $response = ($this->initializeTransaction)(
            InitializeTransactionInputData::from([
                'email' => $order->customer_email,
                'amount' => $order->total_amount,
                'callbackUrl' => route('billing.paystack.callback'),
            ])
        );

        return $response->authorizationUrl;
    }

    public function verify(string $reference): string
    {
        return ($this->verifyTransaction)(
            VerifyTransactionInputData::from([
                'reference' => $reference,
            ])
        )->transaction->status;
    }
}
```

## Why no static `::make()` / `::run()`?

This package keeps actions as injectable Laravel services. You call them through dependency injection and `__invoke`, and you can avoid constructor `new` by using DTO `::from([...])`.

## Need a workflow example?

- [One-Time Checkout](/examples/checkout)
- [Verify a Transaction](/examples/verify-transaction)
