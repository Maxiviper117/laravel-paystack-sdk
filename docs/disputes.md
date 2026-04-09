# Disputes

Disputes currently support list, fetch, transaction-scoped lookup, update, add evidence, upload URL generation, resolve, and export operations.

## List disputes

```php
use Maxiviper117\Paystack\Actions\Dispute\ListDisputesAction;
use Maxiviper117\Paystack\Data\Input\Dispute\ListDisputesInputData;

$response = app(ListDisputesAction::class)(
    new ListDisputesInputData(
        from: '2026-01-01',
        to: '2026-12-31',
        perPage: 25,
        page: 1,
        status: 'pending',
    )
);
```

`ListDisputesInputData` supports the documented list filters:

- `from`
- `to`
- `perPage`
- `page`
- `transaction`
- `status`

`ExportDisputesAction` reuses the same input DTO so exports and lists stay aligned.

## Fetch and transaction disputes

```php
use Maxiviper117\Paystack\Actions\Dispute\FetchDisputeAction;
use Maxiviper117\Paystack\Actions\Dispute\ListTransactionDisputesAction;
use Maxiviper117\Paystack\Data\Input\Dispute\FetchDisputeInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\ListTransactionDisputesInputData;

$dispute = app(FetchDisputeAction::class)(new FetchDisputeInputData(2867));
$transactionDispute = app(ListTransactionDisputesAction::class)(new ListTransactionDisputesInputData(5991760));
```

## Update, evidence, upload, and resolve

Available action classes:

- `UpdateDisputeAction`
- `AddDisputeEvidenceAction`
- `GetDisputeUploadUrlAction`
- `ResolveDisputeAction`

Matching input DTOs:

- `UpdateDisputeInputData`
- `AddDisputeEvidenceInputData`
- `GetDisputeUploadUrlInputData`
- `ResolveDisputeInputData`

Matching response DTOs:

- `UpdateDisputeResponseData`
- `AddDisputeEvidenceResponseData`
- `GetDisputeUploadUrlResponseData`
- `ResolveDisputeResponseData`

## Returned data

The dispute DTOs keep nested transaction, customer, history, and message data typed while preserving the raw Paystack payload.

## Need a workflow example?

- [Transactions](/transactions)
- [Customers](/customers)
- [Support Matrix](/support-matrix)
