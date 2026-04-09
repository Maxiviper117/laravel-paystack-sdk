# Transactions

Transactions currently support initialize, verify, fetch, and list operations.

## Initialize a transaction

```php
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;

$action = app(InitializeTransactionAction::class);

$response = $action(
    new InitializeTransactionInputData(
        email: 'customer@example.com',
        amount: 15.50,
        channels: ['card', 'bank_transfer'],
        callbackUrl: 'https://example.com/payments/callback',
        reference: 'order_123',
        plan: 'PLN_123',
        invoiceLimit: 3,
        currency: 'NGN',
        splitCode: 'SPL_123',
        subaccount: 'ACCT_123',
        transactionCharge: 250,
        bearer: 'subaccount',
    )
);
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
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;

$action = app(VerifyTransactionAction::class);

$response = $action(
    new VerifyTransactionInputData(reference: 'paystack-reference')
);
```

In PHP, timestamp fields on response DTOs are typed. For example, `$response->transaction->paidAt` is a `CarbonImmutable|null`.

If you are in a controller or route that should return JSON, you can return the response DTO directly:

```php
use Illuminate\Http\Request;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;

Route::get('/billing/paystack/callback', function (
    Request $request,
    VerifyTransactionAction $verifyTransaction,
) {
    return $verifyTransaction(
        new VerifyTransactionInputData(
            reference: (string) $request->query('reference', '')
        )
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
    new InitializeTransactionInputData(
        email: 'customer@example.com',
        amount: 15.50,
        callbackUrl: 'https://example.com/payments/callback',
    )
);
```

## Need a workflow example?

- [One-Time Checkout](/examples/checkout)
- [Verify a Transaction](/examples/verify-transaction)
