# Disputes

Disputes currently support list, fetch, transaction-scoped lookup, update, add evidence, upload URL generation, resolve, and export operations.

## List disputes

```php
namespace App\Services\Billing;

use Maxiviper117\Paystack\Actions\Dispute\ListDisputesAction;
use Maxiviper117\Paystack\Data\Input\Dispute\ListDisputesInputData;

class ListPaystackDisputes
{
    public function __construct(
        private ListDisputesAction $listDisputes,
    ) {}

    public function handle(): void
    {
        $response = ($this->listDisputes)(
            ListDisputesInputData::from([
                'from' => '2026-01-01',
                'to' => '2026-12-31',
                'perPage' => 25,
                'page' => 1,
                'status' => 'pending',
            ])
        );
    }
}
```

`ListDisputesInputData` supports the documented list filters:

- `from`
- `to`
- `perPage`
- `page`
- `transaction`
- `status`

`status` is enum-backed through `DisputeStatus` with the documented values `awaiting-merchant-feedback`, `awaiting-bank-feedback`, `pending`, and `resolved`.

The same `DisputeStatus` enum is used on dispute response DTOs so fetched and resolved disputes stay type-safe in PHP.

`ExportDisputesAction` reuses the same input DTO so exports and lists stay aligned.

## Fetch and transaction disputes

```php
namespace App\Services\Billing;

use Maxiviper117\Paystack\Actions\Dispute\FetchDisputeAction;
use Maxiviper117\Paystack\Actions\Dispute\ListTransactionDisputesAction;
use Maxiviper117\Paystack\Data\Input\Dispute\FetchDisputeInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\ListTransactionDisputesInputData;

class FetchPaystackDisputes
{
    public function __construct(
        private FetchDisputeAction $fetchDispute,
        private ListTransactionDisputesAction $listTransactionDisputes,
    ) {}

    public function handle(): void
    {
        $dispute = ($this->fetchDispute)(FetchDisputeInputData::from(['id' => 2867]));
        $transactionDispute = ($this->listTransactionDisputes)(ListTransactionDisputesInputData::from(['id' => 5991760]));
    }
}
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
