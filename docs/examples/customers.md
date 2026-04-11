# Manage Customers

Use this flow when your app needs to create, fetch, validate, or risk-manage Paystack customers ahead of recurring billing or keep remote customer details aligned with your local customer profile.

## Typical application flow

1. Create a Paystack customer when a billable user first enters your billing system.
2. Store the returned Paystack customer code on your local customer record.
3. Fetch the remote customer later when you need to refresh local state.
4. Validate identification details when your integration requires it.
5. Set a risk action only from a trusted server-side workflow.
6. Send targeted updates later when trusted profile fields change.

## Preferred service-based example

```php
namespace App\Services\Billing;

use App\Models\Account;
use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\FetchCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\SetCustomerRiskAction;
use Maxiviper117\Paystack\Actions\Customer\ValidateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\UpdateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\FetchCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\SetCustomerRiskActionInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ValidateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;

class SyncPaystackCustomer
{
    public function __construct(
        private CreateCustomerAction $createCustomer,
        private FetchCustomerAction $fetchCustomer,
        private UpdateCustomerAction $updateCustomer,
        private ValidateCustomerAction $validateCustomer,
        private SetCustomerRiskAction $setCustomerRiskAction,
    ) {}

    public function create(Account $account): string
    {
        $response = ($this->createCustomer)(
            CreateCustomerInputData::from([
                'email' => $account->email,
                'firstName' => $account->first_name,
                'lastName' => $account->last_name,
                'phone' => $account->phone,
                'metadata' => [
                    'account_id' => $account->getKey(),
                ],
            ])
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
            UpdateCustomerInputData::from([
                'customerCode' => $account->paystack_customer_code,
                'email' => $account->email,
                'firstName' => $account->first_name,
                'lastName' => $account->last_name,
                'phone' => $account->phone,
            ])
        );
    }

    public function fetch(Account $account): void
    {
        if ($account->paystack_customer_code === null) {
            return;
        }

        ($this->fetchCustomer)(FetchCustomerInputData::from(['emailOrCode' => $account->paystack_customer_code]));
    }

    public function validate(Account $account): void
    {
        if ($account->paystack_customer_code === null) {
            return;
        }

        ($this->validateCustomer)(
            ValidateCustomerInputData::from([
                'customerCode' => $account->paystack_customer_code,
                'country' => 'NG',
                'type' => 'bank_account',
                'accountNumber' => $account->account_number,
                'bvn' => $account->bvn,
                'bankCode' => $account->bank_code,
            ])
        );
    }

    public function deny(Account $account): void
    {
        if ($account->paystack_customer_code === null) {
            return;
        }

        ($this->setCustomerRiskAction)(
            SetCustomerRiskActionInputData::from([
                'customer' => $account->paystack_customer_code,
                'riskAction' => 'deny',
            ])
        );
    }
}
```

## Facade alternative

```php
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\FetchCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\SetCustomerRiskActionInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ValidateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$created = Paystack::createCustomer(
    CreateCustomerInputData::from([
        'email' => $account->email,
        'firstName' => $account->first_name,
        'lastName' => $account->last_name,
    ])
);

$customerCode = $created->customer->customerCode;

$fetched = Paystack::fetchCustomer(
    FetchCustomerInputData::from(['emailOrCode' => $customerCode])
);

Paystack::updateCustomer(
    UpdateCustomerInputData::from([
        'customerCode' => $customerCode,
        'phone' => $account->phone,
    ])
);

Paystack::validateCustomer(
    ValidateCustomerInputData::from([
        'customerCode' => $customerCode,
        'country' => 'NG',
        'type' => 'bank_account',
        'accountNumber' => $account->account_number,
        'bvn' => $account->bvn,
        'bankCode' => $account->bank_code,
    ])
);

Paystack::setCustomerRiskAction(
    SetCustomerRiskActionInputData::from([
        'customer' => $customerCode,
        'riskAction' => 'allow',
    ])
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
