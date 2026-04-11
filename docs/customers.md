# Customers

The Customer API lets you create, retrieve, update, list, validate, and risk-manage Paystack customers from your Laravel application. Every operation is an action class you can inject into controllers, services, or queue jobs. Each action accepts a typed input DTO and returns a typed response DTO — no raw arrays on the public surface.

## Overview

The package ships six customer actions:

| Action | Input DTO | Purpose |
|--------|-----------|---------|
| `ListCustomersAction` | `ListCustomersInputData` | Paginated list with optional filters |
| `CreateCustomerAction` | `CreateCustomerInputData` | Create a new Paystack customer |
| `FetchCustomerAction` | `FetchCustomerInputData` | Retrieve a customer by email or code |
| `UpdateCustomerAction` | `UpdateCustomerInputData` | Update fields on an existing customer |
| `ValidateCustomerAction` | `ValidateCustomerInputData` | Submit identity verification data |
| `SetCustomerRiskAction` | `SetCustomerRiskActionInputData` | Set allow/deny risk flag |

Every action can be injected into a Laravel controller method and invoked immediately. Each action implements `__invoke`, so you can call it like a function.

## RESTful Customers Controller

A single resource controller covers the full customer lifecycle. Inject the specific action via method signature:

```php
namespace App\Http\Controllers\Billing;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\FetchCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\ListCustomersAction;
use Maxiviper117\Paystack\Actions\Customer\SetCustomerRiskAction;
use Maxiviper117\Paystack\Actions\Customer\UpdateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\ValidateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\FetchCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Data\Input\Customer\SetCustomerRiskActionInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ValidateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\CustomerRiskAction;

class CustomersController
{
    /**
     * List customers with pagination and filters.
     *
     * GET /billing/customers
     */
    public function index(
        Request $request,
        ListCustomersAction $listCustomers,
    ): JsonResponse {
        $response = $listCustomers(
            ListCustomersInputData::from([
                'perPage' => (int) $request->input('per_page', 50),
                'page' => (int) $request->input('page', 1),
                'email' => $request->input('email'),
                'from' => $request->input('from'),
                'to' => $request->input('to'),
            ])
        );

        return response()->json($response);
    }

    /**
     * Create a new customer.
     *
     * POST /billing/customers
     */
    public function store(
        Request $request,
        CreateCustomerAction $createCustomer,
    ): JsonResponse {
        $response = $createCustomer(
            CreateCustomerInputData::from([
                'email' => (string) $request->input('email'),
                'firstName' => (string) $request->input('first_name', ''),
                'lastName' => (string) $request->input('last_name', ''),
                'phone' => (string) $request->input('phone', ''),
                'metadata' => ['app_user_id' => auth()->id()],
            ])
        );

        return response()->json($response, 201);
    }

    /**
     * Fetch a single customer by code or email.
     *
     * GET /billing/customers/{customerCode}
     */
    public function show(
        string $customerCode,
        FetchCustomerAction $fetchCustomer,
    ): JsonResponse {
        $response = $fetchCustomer(
            FetchCustomerInputData::from([
                'emailOrCode' => $customerCode,
            ])
        );

        return response()->json($response);
    }

    /**
     * Update customer details.
     *
     * PATCH /billing/customers/{customerCode}
     */
    public function update(
        string $customerCode,
        Request $request,
        UpdateCustomerAction $updateCustomer,
    ): JsonResponse {
        $response = $updateCustomer(
            UpdateCustomerInputData::from([
                'customerCode' => $customerCode,
                'firstName' => $request->input('first_name'),
                'lastName' => $request->input('last_name'),
                'phone' => $request->input('phone'),
                'metadata' => $request->input('metadata'),
            ])
        );

        return response()->json($response);
    }

    /**
     * Validate a customer's identity with Paystack.
     *
     * POST /billing/customers/{customerCode}/validate
     */
    public function validate(
        string $customerCode,
        Request $request,
        ValidateCustomerAction $validateCustomer,
    ): JsonResponse {
        $response = $validateCustomer(
            ValidateCustomerInputData::from([
                'customerCode' => $customerCode,
                'country' => (string) $request->input('country', 'NG'),
                'type' => (string) $request->input('type', 'bank_account'),
                'accountNumber' => (string) $request->input('account_number'),
                'bvn' => (string) $request->input('bvn'),
                'bankCode' => (string) $request->input('bank_code'),
            ])
        );

        return response()->json($response);
    }

    /**
     * Set risk action (allow/deny) for a customer.
     *
     * POST /billing/customers/{customerCode}/risk
     */
    public function setRisk(
        string $customerCode,
        Request $request,
        SetCustomerRiskAction $setCustomerRiskAction,
    ): JsonResponse {
        $response = $setCustomerRiskAction(
            SetCustomerRiskActionInputData::from([
                'customer' => $customerCode,
                'riskAction' => $request->enum('risk_action', CustomerRiskAction::class) ?? CustomerRiskAction::Default,
            ])
        );

        return response()->json($response);
    }
}
```

## Routes

Register the routes behind a prefix. Add auth middleware if the endpoint should be protected:

```php
use App\Http\Controllers\Billing\CustomersController;
use Illuminate\Support\Facades\Route;

Route::prefix('billing')->middleware('auth:sanctum')->group(function () {
    Route::get('/customers', [CustomersController::class, 'index']);
    Route::post('/customers', [CustomersController::class, 'store']);
    Route::get('/customers/{customerCode}', [CustomersController::class, 'show']);
    Route::patch('/customers/{customerCode}', [CustomersController::class, 'update']);
    Route::post('/customers/{customerCode}/validate', [CustomersController::class, 'validate']);
    Route::post('/customers/{customerCode}/risk', [CustomersController::class, 'setRisk']);
});
```

## Actions in detail

### Create a customer

`CreateCustomerAction` creates a Paystack customer and returns the remote customer record. The only required field is `email`. Optional fields include `firstName`, `lastName`, `phone`, and `metadata`.

```php
use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;

class CustomerRegistrationService
{
    public function __construct(
        private CreateCustomerAction $createCustomer,
    ) {}

    public function register(User $user): string
    {
        $response = ($this->createCustomer)(
            CreateCustomerInputData::from([
                'email' => $user->email,
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'phone' => $user->phone,
                'metadata' => [
                    'app_user_id' => $user->getKey(),
                ],
            ])
        );

        return $response->customer->customerCode;
    }
}
```

The returned `CreateCustomerResponseData` contains a `CustomerData` object with the remote customer code (`customerCode`), email, name, phone, metadata, and the raw Paystack payload.

### Fetch a customer

`FetchCustomerAction` retrieves a customer by email or customer code. The Paystack customer code is the primary identifier for future billing operations.

```php
use Maxiviper117\Paystack\Actions\Customer\FetchCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\FetchCustomerInputData;

class CustomerProfileService
{
    public function __construct(
        private FetchCustomerAction $fetchCustomer,
    ) {}

    public function getRemoteCustomer(string $customerCode): object
    {
        $response = ($this->fetchCustomer)(
            FetchCustomerInputData::from(['emailOrCode' => $customerCode])
        );

        return $response->customer;
    }

    public function getRemoteCustomerByEmail(string $email): object
    {
        $response = ($this->fetchCustomer)(
            FetchCustomerInputData::from(['emailOrCode' => $email])
        );

        return $response->customer;
    }
}
```

### Update a customer

`UpdateCustomerAction` updates the fields on an existing Paystack customer. Only the fields you pass will be changed — omitted fields are left untouched on the remote record.

```php
use Maxiviper117\Paystack\Actions\Customer\UpdateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;

class CustomerSyncService
{
    public function __construct(
        private UpdateCustomerAction $updateCustomer,
    ) {}

    public function syncProfile(string $customerCode, array $changes): void
    {
        ($this->updateCustomer)(
            UpdateCustomerInputData::from([
                'customerCode' => $customerCode,
                'firstName' => $changes['first_name'] ?? null,
                'lastName' => $changes['last_name'] ?? null,
                'phone' => $changes['phone'] ?? null,
                'metadata' => ['updated_at' => now()->toIso8601String()],
            ])
        );
    }
}
```

### List customers

`ListCustomersAction` returns a paginated list of customers with optional date and email filters. Use `from` and `to` to restrict results to a date range.

```php
use Maxiviper117\Paystack\Actions\Customer\ListCustomersAction;
use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;

class CustomerListService
{
    public function __construct(
        private ListCustomersAction $listCustomers,
    ) {}

    public function recentCustomers(): array
    {
        $response = ($this->listCustomers)(
            ListCustomersInputData::from([
                'perPage' => 25,
                'page' => 1,
                'from' => now()->subDays(30)->toIso8601String(),
            ])
        );

        return $response->customers;
    }

    public function findCustomersByEmail(string $email): array
    {
        $response = ($this->listCustomers)(
            ListCustomersInputData::from(['email' => $email])
        );

        return $response->customers;
    }
}
```

The returned `ListCustomersResponseData` exposes a `customers` array of `CustomerData` objects and an optional `meta` object for pagination info.

### Validate a customer

`ValidateCustomerAction` submits identification data (bank account, BVN, bank code) to Paystack for customer verification. This is a server-side operation that should only be triggered from trusted workflows.

```php
use Maxiviper117\Paystack\Actions\Customer\ValidateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\ValidateCustomerInputData;

class CustomerVerificationService
{
    public function __construct(
        private ValidateCustomerAction $validateCustomer,
    ) {}

    public function verifyBankAccount(
        string $customerCode,
        string $accountNumber,
        string $bvn,
        string $bankCode,
    ): void {
        ($this->validateCustomer)(
            ValidateCustomerInputData::from([
                'customerCode' => $customerCode,
                'country' => 'NG',
                'type' => 'bank_account',
                'accountNumber' => $accountNumber,
                'bvn' => $bvn,
                'bankCode' => $bankCode,
            ])
        );
    }
}
```

### Set risk action

`SetCustomerRiskAction` flags a customer as allowed or denied for transactions. Use the `CustomerRiskAction` enum to restrict input to the three documented values:

- `CustomerRiskAction::Default` — use Paystack's default risk scoring
- `CustomerRiskAction::Allow` — allow the customer regardless of risk score
- `CustomerRiskAction::Deny` — block the customer from transactions

```php
use Maxiviper117\Paystack\Actions\Customer\SetCustomerRiskAction;
use Maxiviper117\Paystack\Data\Input\Customer\SetCustomerRiskActionInputData;
use Maxiviper117\Paystack\Data\Input\Customer\CustomerRiskAction;

class CustomerRiskService
{
    public function __construct(
        private SetCustomerRiskAction $setCustomerRiskAction,
    ) {}

    public function blockCustomer(string $customerCode): void
    {
        ($this->setCustomerRiskAction)(
            SetCustomerRiskActionInputData::from([
                'customer' => $customerCode,
                'riskAction' => CustomerRiskAction::Deny,
            ])
        );
    }

    public function allowCustomer(string $customerCode): void
    {
        ($this->setCustomerRiskAction)(
            SetCustomerRiskActionInputData::from([
                'customer' => $customerCode,
                'riskAction' => CustomerRiskAction::Allow,
            ])
        );
    }
}
```

## Response DTOs

Every customer action returns a typed response DTO. The returned objects are immutable Spatie Laravel Data instances you can access with property syntax or serialize to JSON.

### CustomerData properties

All customer responses expose a `CustomerData` object with these fields:

| Property | Type | Description |
|----------|------|-------------|
| `email` | `string` | Customer email address |
| `customerCode` | `string\|null` | Paystack customer code (e.g. `CUS_xxx`) |
| `firstName` | `string\|null` | First name |
| `lastName` | `string\|null` | Last name |
| `phone` | `string\|null` | Phone number |
| `metadata` | `array\|null` | Custom metadata object |
| `raw` | `array` | Full raw Paystack API payload |

### Accessing response data

```php
$response = $createCustomer(CreateCustomerInputData::from([
    'email' => 'user@example.com',
    'firstName' => 'Jane',
    'lastName' => 'Doe',
]));

// Typed properties
$email = $response->customer->email;        // 'user@example.com'
$code = $response->customer->customerCode;  // 'CUS_abcd1234'
$name = $response->customer->firstName;     // 'Jane'

// Access raw Paystack payload
$raw = $response->customer->raw;

// Serialize to JSON
$json = $response->toJson();
```

### List response with pagination

```php
$response = $listCustomers(ListCustomersInputData::from(['perPage' => 10]));

foreach ($response->customers as $customer) {
    echo $customer->customerCode;
}

// Pagination metadata when multiple pages exist
if ($response->meta !== null && $response->meta->pagination !== null) {
    $total = $response->meta->pagination->total;
    $page = $response->meta->pagination->currentPage;
    $next = $response->meta->pagination->next;
}
```

## Form request validation

Use a Laravel `FormRequest` to validate customer input before passing it to the action:

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
```

```php
namespace App\Http\Controllers\Billing;

use App\Http\Requests\StoreCustomerRequest;
use Illuminate\Http\JsonResponse;
use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;

class CustomersController
{
    public function store(
        StoreCustomerRequest $request,
        CreateCustomerAction $createCustomer,
    ): JsonResponse {
        $response = $createCustomer(
            CreateCustomerInputData::from([
                'email' => $request->input('email'),
                'firstName' => $request->input('first_name'),
                'lastName' => $request->input('last_name'),
                'phone' => $request->input('phone'),
                'metadata' => ['app_user_id' => auth()->id()],
            ])
        );

        return response()->json($response, 201);
    }
}
```

## Using the facade

The `Paystack` facade exposes all customer methods for quick access without injection:

```php
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\CustomerRiskAction;
use Maxiviper117\Paystack\Data\Input\Customer\FetchCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Data\Input\Customer\SetCustomerRiskActionInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ValidateCustomerInputData;
use Maxiviper117\Paystack\Facades\Paystack;

// Create
$created = Paystack::createCustomer(CreateCustomerInputData::from([
    'email' => 'user@example.com',
    'firstName' => 'Jane',
    'lastName' => 'Doe',
]));
$customerCode = $created->customer->customerCode;

// Fetch
$fetched = Paystack::fetchCustomer(FetchCustomerInputData::from(['emailOrCode' => $customerCode]));

// Update
Paystack::updateCustomer(UpdateCustomerInputData::from([
    'customerCode' => $customerCode,
    'phone' => '+2348000000000',
]));

// List
$all = Paystack::listCustomers(ListCustomersInputData::from(['perPage' => 50]));

// Validate
Paystack::validateCustomer(ValidateCustomerInputData::from([
    'customerCode' => $customerCode,
    'country' => 'NG',
    'type' => 'bank_account',
    'accountNumber' => '0123456789',
    'bvn' => '2001234567890',
    'bankCode' => '007',
]));

// Set risk
Paystack::setCustomerRiskAction(SetCustomerRiskActionInputData::from([
    'customer' => $customerCode,
    'riskAction' => CustomerRiskAction::Deny,
]));
```

## Error handling

Actions throw `InvalidPaystackInputException` when required input fails validation before the request is sent. Other errors come from the Saloon HTTP layer (connection failures, non-200 responses). Wrap calls in try/catch if your workflow needs graceful fallback:

```php
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Saloon\Exceptions\Request\RequestException;

try {
    $response = $fetchCustomer(
        FetchCustomerInputData::from(['emailOrCode' => 'CUS_doesnotexist'])
    );
} catch (RequestException $e) {
    return response()->json(['error' => 'Customer not found'], $e->getStatus());
} catch (InvalidPaystackInputException $e) {
    return response()->json(['error' => $e->getMessage()], 422);
}
```

## Returned data reference

Customer operations return typed response DTOs:

- `CreateCustomerResponseData` → `CustomerData $customer`
- `FetchCustomerResponseData` → `CustomerData $customer`
- `UpdateCustomerResponseData` → `CustomerData $customer`
- `ListCustomersResponseData` → `array $customers`, `?MetaData $meta`
- `ValidateCustomerResponseData` → verification result
- `SetCustomerRiskActionResponseData` → risk action result

## Need a workflow example?

- [Manage Customers](/examples/customers)
- [Subscription Billing Flow](/examples/subscriptions)
