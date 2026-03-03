# Customers

Customers currently support create, update, and list operations.

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

## List customers

Use `ListCustomersAction` with `ListCustomersInputData` to query the supported customer list filters and pagination inputs exposed by the package.

## Returned data

Customer operations return typed response DTOs:

- `CreateCustomerResponseData`
- `UpdateCustomerResponseData`
- `ListCustomersResponseData`

## Need a workflow example?

- [Manage Customers](/examples/customers)
- [Subscription Billing Flow](/examples/subscriptions)
