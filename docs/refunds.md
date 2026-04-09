# Refunds

Refund support covers the Paystack refund lifecycle: create a refund, retry a refund with customer bank details, fetch a refund, and list refunds.

## Create a refund

```php
use Maxiviper117\Paystack\Actions\Refund\CreateRefundAction;
use Maxiviper117\Paystack\Data\Input\Refund\CreateRefundInputData;

$refund = app(CreateRefundAction::class)(
    new CreateRefundInputData(
        transaction: 'T685312322670591',
        amount: 10000,
        currency: 'NGN',
        customerNote: 'Changed my mind',
        merchantNote: 'Approved by support',
    )
);
```

`CreateRefundInputData` accepts the refund transaction reference or id, plus optional subunit amount, currency, customer note, and merchant note.

## Retry a refund

```php
use Maxiviper117\Paystack\Actions\Refund\RetryRefundAction;
use Maxiviper117\Paystack\Data\Input\Refund\RefundAccountDetailsInputData;
use Maxiviper117\Paystack\Data\Input\Refund\RetryRefundInputData;

$refund = app(RetryRefundAction::class)(
    new RetryRefundInputData(
        id: 1234567,
        refundAccountDetails: new RefundAccountDetailsInputData(
            currency: 'NGN',
            accountNumber: '1234567890',
            bankId: '9',
        ),
    )
);
```

## Fetch and list

Available action classes:

- `FetchRefundAction`
- `ListRefundsAction`

Matching input DTOs:

- `FetchRefundInputData`
- `ListRefundsInputData`

Matching response DTOs:

- `FetchRefundResponseData`
- `ListRefundsResponseData`

`ListRefundsInputData` supports the documented `transaction`, `currency`, `from`, `to`, `perPage`, and `page` query parameters.

Refund response DTOs expose the documented refund lifecycle through the backed `RefundStatus` enum: `pending`, `processing`, `needs-attention`, `failed`, and `processed`.

## Facade usage

```php
use Maxiviper117\Paystack\Data\Input\Refund\FetchRefundInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$refund = Paystack::fetchRefund(new FetchRefundInputData(1234567));
```

## Related pages

- [Transactions](/transactions)
- [Support Matrix](/support-matrix)
