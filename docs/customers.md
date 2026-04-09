# Customers

Customers currently support create, fetch, update, validate, set risk action, and list operations.

## Create a customer

```php
use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;

$action = app(CreateCustomerAction::class);

$response = $action(
    new CreateCustomerInputData(
        email: 'customer@example.com',
        firstName: 'Jane',
        lastName: 'Doe',
    )
);
```

## Update a customer

```php
use Maxiviper117\Paystack\Actions\Customer\UpdateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;

$action = app(UpdateCustomerAction::class);

$response = $action(
    new UpdateCustomerInputData(
        code: 'CUS_123',
        firstName: 'Janet',
    )
);
```

## Fetch a customer

```php
use Maxiviper117\Paystack\Actions\Customer\FetchCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\FetchCustomerInputData;

$action = app(FetchCustomerAction::class);

$response = $action(
    new FetchCustomerInputData(emailOrCode: 'CUS_123')
);
```

## Validate a customer

```php
use Maxiviper117\Paystack\Actions\Customer\ValidateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\ValidateCustomerInputData;

$action = app(ValidateCustomerAction::class);

$response = $action(
    new ValidateCustomerInputData(
        customerCode: 'CUS_123',
        country: 'NG',
        type: 'bank_account',
        accountNumber: '0123456789',
        bvn: '200123456677',
        bankCode: '007',
    )
);
```

## Set a risk action

```php
use Maxiviper117\Paystack\Actions\Customer\SetCustomerRiskAction;
use Maxiviper117\Paystack\Data\Input\Customer\SetCustomerRiskActionInputData;

$action = app(SetCustomerRiskAction::class);

$response = $action(
    new SetCustomerRiskActionInputData(
        customer: 'CUS_123',
        riskAction: 'deny',
    )
);
```

## List customers

Use `ListCustomersAction` with `ListCustomersInputData` to query the supported customer list filters and pagination inputs exposed by the package.

## Returned data

Customer operations return typed response DTOs:

- `CreateCustomerResponseData`
- `FetchCustomerResponseData`
- `UpdateCustomerResponseData`
- `ValidateCustomerResponseData`
- `SetCustomerRiskActionResponseData`
- `ListCustomersResponseData`

## Need a workflow example?

- [Manage Customers](/examples/customers)
- [Subscription Billing Flow](/examples/subscriptions)
