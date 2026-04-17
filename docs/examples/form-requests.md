# Form Request Validation

Use this flow when you need to validate payment-related form requests before processing with Paystack. Laravel's Form Request classes provide a centralized, reusable way to handle validation logic, authorization checks, and input transformation for your payment flows.

## Why Use Form Requests for Payment Validation

Form requests offer several benefits for payment operations:

- **Centralized validation rules** keep your controllers clean and focused on business logic
- **Authorization checks** ensure only permitted users can initiate payment actions
- **Input transformation** allows you to modify or sanitize data before validation
- **Custom error messages** provide clear feedback to users about validation failures
- **Reusable logic** can be shared across multiple controllers or routes
- **Automatic redirection** sends users back with errors when validation fails

## Typical Validation Scenarios

1. **Checkout forms** — Validate email, amount, currency, and payment channels before initializing transactions
2. **Subscription creation** — Ensure plan codes and customer codes are properly formatted
3. **Customer updates** — Validate profile data before syncing to Paystack
4. **Refund requests** — Apply business rules like refund windows and amount limits

## Checkout Form Request

This form request handles validation for initiating a Paystack checkout. It includes authorization checks, field validation, and custom error messages.

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Add your authorization logic
        return $this->user()->can('create-payments');
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'amount' => ['required', 'numeric', 'min:100'], // Paystack minimum in kobo
            'currency' => ['sometimes', 'string', 'size:3', Rule::in(['NGN', 'USD', 'GHS', 'ZAR'])],
            'channels' => ['sometimes', 'array'],
            'channels.*' => ['string', Rule::in(['card', 'bank_transfer', 'ussd', 'qr', 'mobile_money', 'bank', 'eft'])],
            'metadata' => ['sometimes', 'array'],
            'callback_url' => ['sometimes', 'url'],
            'reference' => ['sometimes', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'The minimum amount is 100 kobo (1 NGN).',
            'currency.size' => 'Currency must be a 3-letter ISO code.',
        ];
    }

    public function attributes(): array
    {
        return [
            'channels' => 'payment channels',
            'callback_url' => 'callback URL',
        ];
    }
}
```

**Understanding the validation rules:**

- **`email`** — Required and must be a valid email format; Paystack uses this to identify customers
- **`amount`** — Required numeric value with a minimum of 100 (kobo/cents). Paystack's minimum transaction is 100 kobo (1 NGN) or equivalent
- **`currency`** — Optional 3-letter ISO code restricted to supported Paystack currencies
- **`channels`** — Optional array of allowed payment methods. This restricts which payment options the customer sees
- **`metadata`** — Optional array for storing custom data that you'll receive in webhooks
- **`callback_url`** — Optional URL override for where Paystack redirects after payment
- **`reference`** — Optional custom reference (auto-generated if not provided)

**Custom messages and attributes:**

The `messages()` method provides user-friendly error messages, while `attributes()` changes how field names appear in error messages (e.g., "payment channels" instead of "channels").

## Controller Usage

The beauty of form requests is how they simplify controllers. The validation happens automatically before your controller method is called.

```php
namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Order;
use App\Services\Billing\StartCheckout;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function store(
        StoreCheckoutRequest $request,
        Order $order,
        StartCheckout $startCheckout
    ): RedirectResponse {
        // If we reach this point, validation passed
        $validated = $request->validated();

        // Amount is already validated as numeric and >= 100
        // Email is confirmed valid
        // Currency is in the allowed list
        $authorizationUrl = $startCheckout->handle($order, $validated);

        return redirect()->away($authorizationUrl);
    }
}
```

**What happens behind the scenes:**

1. Laravel automatically resolves the `StoreCheckoutRequest` before calling `store()`
2. The `authorize()` method is checked—if it returns false, a 403 response is returned
3. Validation rules are applied to the incoming request data
4. If validation fails, the user is redirected back with error messages
5. If validation passes, your controller receives the validated data via `validated()`

## Subscription Form Request

Creating subscriptions requires validating plan and customer codes, which follow specific Paystack formats. This request also demonstrates `prepareForValidation()` for data transformation.

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-subscriptions');
    }

    public function rules(): array
    {
        return [
            'plan_code' => ['required', 'string', 'starts_with:PLN_'],
            'customer_code' => ['required', 'string', 'starts_with:CUS_'],
            'authorization_code' => ['sometimes', 'nullable', 'string'],
            'start_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    public function prepareForValidation(): void
    {
        // Auto-format customer code if needed
        if ($this->has('customer_email') && ! $this->has('customer_code')) {
            $customer = \App\Models\Customer::query()
                ->where('email', $this->input('customer_email'))
                ->first();

            if ($customer) {
                $this->merge(['customer_code' => $customer->paystack_customer_code]);
            }
        }
    }
}
```

**Key features explained:**

- **`starts_with:PLN_` and `starts_with:CUS_`** — These rules validate Paystack's code format. Plan codes always start with "PLN_" and customer codes with "CUS_"
- **`after_or_equal:today`** — Ensures subscription start dates are not in the past
- **`prepareForValidation()`** — This method runs before validation, allowing you to modify or add input data. Here, it looks up a customer by email and injects their Paystack code

**Use case for `prepareForValidation()`:**

Your frontend might only collect a customer email, but Paystack requires a customer code. Instead of doing this lookup in every controller, the form request handles it automatically.

## Refund Request with Business Rules

Refunds often have business logic beyond simple field validation—such as time limits, amount caps, or status requirements. The `after()` method lets you apply these rules after standard validation passes.

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProcessRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('process-refunds');
    }

    public function rules(): array
    {
        return [
            'transaction_reference' => ['required', 'string', 'exists:payments,reference'],
            'amount' => ['sometimes', 'integer', 'min:100'],
            'reason' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $reference = $this->input('transaction_reference');
                $payment = \App\Models\Payment::query()
                    ->where('reference', $reference)
                    ->first();

                if ($payment === null) {
                    return;
                }

                // Business rule: only successful payments can be refunded
                if ($payment->status !== 'success') {
                    $validator->errors()->add(
                        'transaction_reference',
                        'Only successful transactions can be refunded.'
                    );
                }

                // Business rule: check refund window (e.g., 30 days)
                if ($payment->created_at->diffInDays(now()) > 30) {
                    $validator->errors()->add(
                        'transaction_reference',
                        'Transaction is outside the 30-day refund window.'
                    );
                }

                // Business rule: validate refund amount doesn't exceed original
                if ($this->has('amount')) {
                    $requestedAmount = (int) $this->input('amount');
                    if ($requestedAmount > $payment->amount) {
                        $validator->errors()->add(
                            'amount',
                            'Refund amount cannot exceed the original payment amount.'
                        );
                    }
                }
            },
        ];
    }
}
```

**Understanding `after()` validation:**

Standard validation rules check format and existence. The `after()` method receives a `Validator` instance after all other rules pass, letting you:

- Query the database for related records
- Apply business logic that depends on existing data
- Add errors dynamically based on conditions

**Business rules implemented:**

1. **Status check** — Only successful payments can be refunded (you can't refund failed or pending transactions)
2. **Time window** — Refunds must be requested within 30 days of the original transaction
3. **Amount cap** — Partial refunds cannot exceed the original payment amount

## Customer Validation Request

When validating customer identification details for Paystack, different validation rules apply depending on the verification type (bank account vs. BVN).

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('validate-customers');
    }

    public function rules(): array
    {
        return [
            'country' => ['required', 'string', 'size:2'],
            'type' => ['required', 'string', Rule::in(['bank_account', 'bvn'])],
            'account_number' => ['required_if:type,bank_account', 'string', 'digits:10'],
            'bvn' => ['required_if:type,bvn', 'string', 'digits:11'],
            'bank_code' => ['required_if:type,bank_account', 'string', 'digits:3'],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
        ];
    }
}
```

**Conditional validation explained:**

- **`required_if:type,bank_account`** — The `account_number` and `bank_code` are only required when validating a bank account
- **`required_if:type,bvn`** — The `bvn` field is only required for BVN validation
- **`digits:10` and `digits:11`** — These ensure the exact digit count for Nigerian account numbers (10 digits) and BVN (11 digits)
- **`size:2`** — Country codes must be exactly 2 characters (ISO 3166-1 alpha-2)

## Testing Form Requests

Form requests should be tested in isolation to ensure validation rules work correctly. This is faster and more focused than testing through controllers.

```php
namespace Tests\Unit\Http\Requests;

use App\Http\Requests\StoreCheckoutRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreCheckoutRequestTest extends TestCase
{
    public function test_validates_required_fields(): void
    {
        $request = new StoreCheckoutRequest;

        $validator = Validator::make([
            'email' => 'test@example.com',
            'amount' => 5000,
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_rejects_invalid_currency(): void
    {
        $request = new StoreCheckoutRequest;

        $validator = Validator::make([
            'email' => 'test@example.com',
            'amount' => 5000,
            'currency' => 'INVALID',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('currency', $validator->errors()->toArray());
    }

    public function test_enforces_minimum_amount(): void
    {
        $request = new StoreCheckoutRequest;

        $validator = Validator::make([
            'email' => 'test@example.com',
            'amount' => 50, // Below minimum
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    public function test_rejects_invalid_email(): void
    {
        $request = new StoreCheckoutRequest;

        $validator = Validator::make([
            'email' => 'not-an-email',
            'amount' => 5000,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }
}
```

**Testing strategies:**

- **Test valid data** to ensure your rules allow legitimate requests
- **Test invalid data** to confirm proper rejection of bad input
- **Test boundary values** like minimum amounts, empty strings, and maximum lengths
- **Test conditional rules** by providing different values for conditional fields

## Best Practices for Payment Validation

1. **Always validate amounts on the server** — Never trust client-side amounts, even if you validate in JavaScript
2. **Use strict type checking** — Specify `numeric`, `integer`, or `string` to prevent type confusion
3. **Validate against allowed values** — Use `Rule::in()` for currencies, channels, and status values
4. **Keep business rules in form requests** — Use `after()` for complex logic that involves database queries
5. **Provide clear error messages** — Help users understand what went wrong and how to fix it
6. **Test edge cases** — Consider what happens with zero amounts, empty strings, or very large values

## Related Pages

- [One-Time Checkout](/examples/checkout) — See checkout flow implementation
- [Manage Customers](/examples/customers) — Customer management patterns
- [Subscription Billing Flow](/examples/subscriptions) — Subscription creation workflows
- [Refunds](/refunds) — Refund operation reference
