# Manager and Facade Usage

The package is designed around injected action classes and typed DTOs. `PaystackManager` and the `Paystack` facade expose the same DTO-first operations for apps that prefer a convenience layer.

## Recommended default: injected actions

Use injected actions when you are writing application services, listeners, jobs, or controllers that you want to keep explicit and testable.

```php
namespace App\Services\Billing;

use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;

class ReconcilePayment
{
    public function __construct(
        private VerifyTransactionAction $verifyTransaction,
    ) {}

    public function handle(string $reference): string
    {
        return ($this->verifyTransaction)(
            new VerifyTransactionInputData(reference: $reference)
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
            ->listCustomers(new ListCustomersInputData(perPage: 25))
            ->customers;
    }
}
```

## Facade example

Use the facade when concise Laravel-style integration matters more than explicit dependency injection.

```php
use Maxiviper117\Paystack\Data\Input\Plan\ListPlansInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$plans = Paystack::listPlans(
    new ListPlansInputData(perPage: 10)
)->plans;
```

## Choosing between them

- prefer injected actions in application services, listeners, and jobs
- use `PaystackManager` when one service needs several package operations
- use the facade for short, convenient call sites
- keep all usage DTO-first regardless of which entrypoint you choose

## Related pages

- [Getting Started](/getting-started)
- [Examples Overview](/examples/)
- [Transactions](/transactions)
- [Customers](/customers)
