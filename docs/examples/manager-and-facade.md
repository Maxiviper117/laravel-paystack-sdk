# Manager and Facade Usage

The package is designed around a Laravel-first convenience layer and typed DTOs. `Paystack` and `PaystackManager` expose the same DTO-first operations for application code. Injectable action classes remain available for custom integrations.

## Recommended default: facade

Use the facade when you want concise Laravel-style integration in controllers, services, listeners, or jobs.

```php
namespace App\Services\Billing;

use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class ReconcilePayment
{
    public function handle(string $reference): string
    {
        return Paystack::verifyTransaction(
            VerifyTransactionInputData::from(['reference' => $reference])
        )->transaction->status;
    }
}
```

## Manager example

Use `PaystackManager` when you want a single injected service that exposes the package operations without importing each action separately.

```php
namespace App\Services\Billing;

use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\PaystackManager;

class FindBillableCustomers
{
    public function __construct(
        private PaystackManager $paystack,
    ) {}

    public function handle(): array
    {
        return $this->paystack
            ->listCustomers(ListCustomersInputData::from(['perPage' => 25]))
            ->customers;
    }
}
```

## Action example

Use an action directly when you need custom composition or a lower-level integration point.

```php
namespace App\Services\Billing;

use Maxiviper117\Paystack\Actions\Plan\ListPlansAction;
use Maxiviper117\Paystack\Data\Input\Plan\ListPlansInputData;

class ListBillingPlans
{
    public function __construct(
        private ListPlansAction $listPlans,
    ) {}

    public function handle(): array
    {
        return ($this->listPlans)(
            ListPlansInputData::from(['perPage' => 10])
        )->plans;
    }
}
```

## Choosing between them

- prefer the facade in controllers and short application flows
- use `PaystackManager` when one service needs several package operations
- use actions only when you need explicit custom composition
- keep all usage DTO-first regardless of which entrypoint you choose

## Related pages

- [Getting Started](/getting-started)
- [Examples Overview](/examples/)
- [Transactions](/transactions)
- [Customers](/customers)
