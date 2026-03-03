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
        callbackUrl: 'https://example.com/payments/callback',
    )
);
```

## Verify a transaction

```php
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;

$action = app(VerifyTransactionAction::class);

$response = $action(
    new VerifyTransactionInputData(reference: 'paystack-reference')
);
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
