# Verify a Transaction

Use this flow after Paystack redirects a customer back to your app or when you need to reconcile a stored payment reference from an internal admin flow.

## Typical application flow

1. Your app receives a callback with a Paystack reference.
2. Your app loads the matching local payment record.
3. A billing service verifies that reference against Paystack.
4. Your app checks the returned transaction status and reference.
5. Your app updates the local payment record idempotently.

## Preferred service-based example

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
            new VerifyTransactionInputData(reference: $payment->payment_reference)
        );

        if ($response->transaction->reference !== $payment->payment_reference) {
            throw new \RuntimeException('Verified Paystack reference did not match the local payment.');
        }

        if ($response->transaction->status !== 'success') {
            return;
        }

        if ($payment->status === 'paid') {
            return;
        }

        $payment->status = 'paid';
        $payment->paid_at = $response->transaction->paidAt;
        $payment->save();
    }
}
```

`$response->transaction->paidAt` is a `CarbonImmutable|null`, so it works cleanly with Eloquent date-casted attributes and can be formatted explicitly if your local column is string-backed.

Callback controller entrypoint:

```php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Billing\ConfirmPaystackPayment;
use Illuminate\Http\Request;

class PaystackCallbackController
{
    public function __invoke(Request $request, ConfirmPaystackPayment $confirmPaystackPayment)
    {
        $reference = (string) $request->query('reference', '');

        $payment = Payment::query()
            ->where('payment_reference', $reference)
            ->firstOrFail();

        $confirmPaystackPayment->handle($payment);

        return redirect()->route('billing.success');
    }
}
```

## Facade alternative

```php
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$response = Paystack::verifyTransaction(
    new VerifyTransactionInputData(reference: $payment->payment_reference)
);

if ($response->transaction->status === 'success') {
    // Mark your local payment as paid.
}
```

## Security notes

- never trust the callback query string by itself
- always verify the reference against Paystack before marking payment complete
- make the local update idempotent so repeat callbacks or retries do not duplicate side effects
- match the verified reference to the payment record you expected to confirm

## Next steps

- [Webhook Processing](/examples/webhooks)
- [Transactions](/transactions)
- [Configuration](/configuration)
