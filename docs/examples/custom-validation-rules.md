# Custom Validation Rules

Use this flow when you need reusable validation rules for Paystack-specific formats like reference codes, plan codes, and customer codes. Laravel's custom validation rules keep your form requests clean and ensure consistent validation across your application.

## Why Use Custom Rules for Paystack Data

- **Reusability** — Define once, use in any form request or controller
- **Consistency** — Same validation logic everywhere
- **Testability** — Rules are small, focused classes that are easy to test
- **Clarity** — Rule names like `paystack_reference` are self-documenting
- **Maintainability** — Update Paystack format changes in one place

## Typical Validation Scenarios

1. **Paystack reference format** — Alphanumeric with hyphens/underscores, max 100 chars
2. **Plan code format** — Must start with `PLN_`
3. **Customer code format** — Must start with `CUS_`
4. **Amount in kobo** — Minimum 100 (1 NGN), must be integer
5. **Currency codes** — Must be supported Paystack currencies
6. **Authorization codes** — Format for stored card authorizations

## Paystack Reference Rule

Paystack references are alphanumeric strings that may include hyphens and underscores. This rule validates the format without making API calls.

```php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PaystackReference implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        if (strlen($value) > 100) {
            $fail('The :attribute must not exceed 100 characters.');

            return;
        }

        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            $fail('The :attribute contains invalid characters. Only letters, numbers, hyphens, and underscores are allowed.');
        }
    }
}
```

**Using in a form request:**

```php
namespace App\Http\Requests;

use App\Rules\PaystackReference;
use Illuminate\Foundation\Http\FormRequest;

class VerifyPaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reference' => ['required', new PaystackReference],
        ];
    }
}
```

## Paystack Plan Code Rule

Plan codes always start with `PLN_` followed by alphanumeric characters.

```php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PaystackPlanCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        if (! str_starts_with($value, 'PLN_')) {
            $fail('The :attribute must start with PLN_.');

            return;
        }

        $codePart = substr($value, 4);

        if (strlen($codePart) < 3) {
            $fail('The :attribute must have at least 3 characters after PLN_.');

            return;
        }

        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $codePart)) {
            $fail('The :attribute contains invalid characters after PLN_.');
        }
    }
}
```

**Using in a subscription form:**

```php
namespace App\Http\Requests;

use App\Rules\PaystackPlanCode;
use Illuminate\Foundation\Http\FormRequest;

class CreateSubscriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'plan_code' => ['required', new PaystackPlanCode],
            'customer_code' => ['required', 'starts_with:CUS_'],
        ];
    }
}
```

## Paystack Amount Rule

Paystack amounts are in the smallest currency unit (kobo for NGN, cents for USD). This rule validates the minimum amount and ensures it's an integer.

```php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PaystackAmount implements ValidationRule
{
    public function __construct(
        private int $minAmount = 100, // 1 NGN minimum
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_int($value) && ! (is_string($value) && ctype_digit($value))) {
            $fail('The :attribute must be a whole number (no decimals).');

            return;
        }

        $amount = (int) $value;

        if ($amount < $this->minAmount) {
            $fail("The :attribute must be at least {$this->minAmount} (minimum charge amount).");
        }
    }
}
```

**Using with custom minimum:**

```php
use App\Rules\PaystackAmount;

public function rules(): array
{
    return [
        // Default 100 kobo minimum
        'amount' => ['required', new PaystackAmount],

        // Custom minimum for premium plans (5000 kobo = 50 NGN)
        'premium_amount' => ['required', new PaystackAmount(5000)],
    ];
}
```

## Paystack Currency Rule

Restrict currencies to those supported by Paystack for your integration.

```php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PaystackCurrency implements ValidationRule
{
    /**
     * @param  array<int, string>  $allowedCurrencies
     */
    public function __construct(
        private array $allowedCurrencies = ['NGN', 'USD', 'GHS', 'ZAR'],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        if (strlen($value) !== 3) {
            $fail('The :attribute must be a 3-letter ISO currency code.');

            return;
        }

        if (! in_array(strtoupper($value), $this->allowedCurrencies, true)) {
            $fail('The :attribute must be one of: ' . implode(', ', $this->allowedCurrencies) . '.');
        }
    }
}
```

## Composite Validation with Multiple Rules

Combine custom rules with Laravel's built-in rules for comprehensive validation:

```php
namespace App\Http\Requests;

use App\Rules\PaystackAmount;
use App\Rules\PaystackCurrency;
use App\Rules\PaystackPlanCode;
use App\Rules\PaystackReference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'amount' => ['required', new PaystackAmount(100)],
            'currency' => ['sometimes', new PaystackCurrency(['NGN', 'USD'])],
            'reference' => ['sometimes', new PaystackReference],
            'plan_code' => ['sometimes', new PaystackPlanCode],
            'channels' => ['sometimes', 'array'],
            'channels.*' => [Rule::in(['card', 'bank_transfer', 'ussd', 'qr', 'mobile_money'])],
            'metadata' => ['sometimes', 'array'],
            'callback_url' => ['sometimes', 'url'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Please enter a payment amount.',
            'email.required' => 'A valid email address is required for payment.',
        ];
    }
}
```

## Rule Objects with Dependencies

For rules that need to query the database (e.g., checking if a plan exists), inject dependencies:

```php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Maxiviper117\Paystack\Facades\Paystack;
use Maxiviper117\Paystack\Data\Input\Plan\FetchPlanInputData;

class ValidPaystackPlan implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        try {
            Paystack::fetchPlan(FetchPlanInputData::from(['idOrCode' => $value]));
        } catch (\Exception) {
            $fail('The selected :attribute is not a valid Paystack plan.');
        }
    }
}
```

**Note:** This rule makes an API call. Use sparingly or cache the result. Consider using a cached plan list instead for high-traffic forms.

## Testing Custom Rules

Custom rules are easy to test in isolation:

```php
use App\Rules\PaystackAmount;
use App\Rules\PaystackPlanCode;
use App\Rules\PaystackReference;

test('paystack reference accepts valid formats', function (): void {
    $rule = new PaystackReference;

    $validator = validator(['ref' => 'order_123_test'], ['ref' => $rule]);
    expect($validator->passes())->toBeTrue();

    $validator = validator(['ref' => 'order-123.test'], ['ref' => $rule]);
    expect($validator->fails())->toBeTrue(); // dots not allowed
});

test('paystack amount validates minimum', function (): void {
    $rule = new PaystackAmount(100);

    $validator = validator(['amount' => 50], ['amount' => $rule]);
    expect($validator->fails())->toBeTrue();

    $validator = validator(['amount' => 100], ['amount' => $rule]);
    expect($validator->passes())->toBeTrue();

    $validator = validator(['amount' => 100.50], ['amount' => $rule]);
    expect($validator->fails())->toBeTrue(); // no decimals
});

test('paystack plan code requires PLN_ prefix', function (): void {
    $rule = new PaystackPlanCode;

    $validator = validator(['code' => 'PLN_test123'], ['code' => $rule]);
    expect($validator->passes())->toBeTrue();

    $validator = validator(['code' => 'plan_test123'], ['code' => $rule]);
    expect($validator->fails())->toBeTrue();
});
```

## Extending Validator with Macros

For simpler rules, extend Laravel's validator with macros:

```php
namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Validator::extend('paystack_reference', function ($attribute, $value): bool {
            return is_string($value)
                && strlen($value) <= 100
                && preg_match('/^[a-zA-Z0-9_-]+$/', $value);
        }, 'The :attribute must be a valid Paystack reference.');

        Validator::extend('paystack_plan_code', function ($attribute, $value): bool {
            return is_string($value)
                && str_starts_with($value, 'PLN_')
                && strlen($value) > 7;
        }, 'The :attribute must be a valid Paystack plan code starting with PLN_.');
    }
}
```

**Using macro rules:**

```php
public function rules(): array
{
    return [
        'reference' => ['required', 'paystack_reference'],
        'plan_code' => ['required', 'paystack_plan_code'],
    ];
}
```

## Related pages

- [Form Request Validation](/examples/form-requests) — Use custom rules in form request classes
- [Testing Paystack Integrations](/examples/testing) — Test validation rules in isolation
- [Manager and Facade Usage](/examples/manager-and-facade) — SDK methods for validation rules that query Paystack