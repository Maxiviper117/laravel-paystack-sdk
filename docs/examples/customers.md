# Manage Customers

Use this flow when your app needs to create Paystack customers ahead of recurring billing or keep remote customer details aligned with your local customer profile.

## Typical application flow

1. Create a Paystack customer when a billable user first enters your billing system.
2. Store the returned Paystack customer code on your local customer record.
3. Send targeted updates later when trusted profile fields change.

## Preferred service-based example

```php
namespace App\Services\Billing;

use App\Models\Account;
use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\UpdateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;

class SyncPaystackCustomer
{
    public function __construct(
        private CreateCustomerAction $createCustomer,
        private UpdateCustomerAction $updateCustomer,
    ) {}

    public function create(Account $account): string
    {
        $response = ($this->createCustomer)(
            new CreateCustomerInputData(
                email: $account->email,
                firstName: $account->first_name,
                lastName: $account->last_name,
                phone: $account->phone,
                metadata: [
                    'account_id' => $account->getKey(),
                ],
            )
        );

        $account->paystack_customer_code = $response->customer->customerCode;
        $account->save();

        return (string) $response->customer->customerCode;
    }

    public function update(Account $account): void
    {
        if ($account->paystack_customer_code === null) {
            return;
        }

        ($this->updateCustomer)(
            new UpdateCustomerInputData(
                customerCode: $account->paystack_customer_code,
                email: $account->email,
                firstName: $account->first_name,
                lastName: $account->last_name,
                phone: $account->phone,
            )
        );
    }
}
```

## Facade alternative

```php
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$created = Paystack::createCustomer(
    new CreateCustomerInputData(
        email: $account->email,
        firstName: $account->first_name,
        lastName: $account->last_name,
    )
);

$customerCode = $created->customer->customerCode;

Paystack::updateCustomer(
    new UpdateCustomerInputData(
        customerCode: $customerCode,
        phone: $account->phone,
    )
);
```

## What to store locally

- the Paystack `customerCode`
- the local model ID tied to that Paystack customer
- any local sync timestamp or audit fields your app needs

## Notes

- only send fields your app owns and trusts
- avoid blindly mirroring every local profile change to Paystack
- treat the returned `customerCode` as the durable remote identifier for future billing flows

## Next steps

- [Subscription Billing Flow](/examples/subscriptions)
- [Customers](/customers)
