# One-Time Checkout

Use this flow when your app needs to initialize a Paystack payment and send the customer to the Paystack authorization page.

## Typical application flow

1. Your controller receives an order or checkout request.
2. Your application calculates the amount server-side.
3. A billing service initializes the Paystack transaction.
4. Your app stores the Paystack reference on the local payment record.
5. Your controller redirects the customer to the returned authorization URL.

## Preferred Laravel example

```php
namespace App\Services\Billing;

use App\Models\Order;
use Maxiviper117\Paystack\PaystackManager;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;

class StartCheckout
{
    public function __construct(
        private PaystackManager $paystack,
    ) {}

    public function handle(Order $order): string
    {
        $response = $this->paystack->initializeTransaction(
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

        $order->payment_reference = $response->reference;
        $order->save();

        return $response->authorizationUrl;
    }
}
```

Controller entrypoint:

```php
namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use App\Models\Order;
use App\Services\Billing\StartCheckout;

class CheckoutController
{
    public function store(Order $order, StartCheckout $startCheckout): RedirectResponse
    {
        return redirect()->away($startCheckout->handle($order));
    }
}
```

## Facade alternative

```php
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$response = Paystack::initializeTransaction(
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

$authorizationUrl = $response->authorizationUrl;
$reference = $response->reference;
```

## Action alternative

```php
namespace App\Services\Billing;

use App\Models\Order;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;

class StartCheckoutWithAction
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

## What to persist locally

At minimum, store:

- your local order or payment identifier
- the Paystack transaction reference
- the amount you expected to charge
- the current local payment state

## Security notes

- compute the amount on the server from trusted order data
- do not trust a client-supplied amount or currency
- do not mark the order paid from the redirect alone
- always verify the transaction or process the matching webhook before settling the payment

## Next steps

- [Verify a Transaction](/examples/verify-transaction)
- [Webhook Processing](/examples/webhooks)
- [Transactions](/transactions)
