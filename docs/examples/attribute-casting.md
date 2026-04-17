# Attribute Casting

Use this flow when you need to work with monetary amounts in a human-friendly format while storing them as integers (kobo) in the database. Laravel's attribute casting provides automatic conversion between storage and presentation formats.

## Why Use Custom Casts for Money

- **Precision** — Store amounts as integers (kobo) to avoid floating-point errors
- **Human-friendly** — Work with Naira/USD in code, kobo in database
- **Consistency** — Same conversion logic everywhere
- **Paystack compatibility** — Paystack expects amounts in smallest currency unit
- **Safety** — Prevents accidental decimal storage

## The Kobo Cast

Create a custom cast that converts between kobo (integer) and currency (decimal):

```php
<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Kobo implements CastsAttributes
{
    /**
     * Cast the stored integer (kobo) to currency amount (float) for use in code.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value  Stored value (integer kobo)
     * @param  array<string, mixed>  $attributes
     * @return float|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?float
    {
        if ($value === null) {
            return null;
        }

        return (float) $value / 100;
    }

    /**
     * Cast the currency amount (float) to kobo (integer) for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value  Input value (float or int)
     * @param  array<string, mixed>  $attributes
     * @return int|null
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        // Handle both float (50.00) and integer (5000) inputs
        if (is_float($value) || is_string($value)) {
            return (int) round((float) $value * 100);
        }

        return (int) $value;
    }
}
```

**Using the cast on a model:**

```php
<?php

namespace App\Models;

use App\Casts\Kobo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => Kobo::class,
            'amount_refunded' => Kobo::class,
            'fees' => Kobo::class,
            'paid_at' => 'datetime',
        ];
    }
}
```

**Working with the cast:**

```php
// Store: Pass currency amount, stores as kobo
$payment = Payment::query()->create([
    'amount' => 500.00,  // Stores as 50000 in database
    'reference' => 'ref_123',
]);

// Retrieve: Get currency amount from kobo storage
$payment = Payment::query()->first();
echo $payment->amount; // 500.00 (float)

// Direct assignment also works
$payment->amount = 1000.50; // Stores as 100050
echo $payment->amount; // 1000.5 (float)

// Works with integers too (for kobo input)
$payment->amount = 50000; // Stores as 50000
echo $payment->amount; // 500 (float)
```

## Currency-Aware Cast with Symbol

Extend the cast to include currency formatting:

```php
<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Money implements CastsAttributes
{
    public function __construct(
        private string $currency = 'NGN',
        private string $symbol = '₦',
    ) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): MoneyValue
    {
        if ($value === null) {
            return new MoneyValue(null, $this->currency, $this->symbol);
        }

        return new MoneyValue(
            amount: (float) $value / 100,
            currency: $this->currency,
            symbol: $this->symbol,
            kobo: (int) $value,
        );
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        // Handle MoneyValue objects
        if ($value instanceof MoneyValue) {
            return $value->kobo;
        }

        // Handle float/string inputs
        if (is_float($value) || is_string($value)) {
            return (int) round((float) $value * 100);
        }

        return (int) $value;
    }
}
```

**Money value object:**

```php
<?php

namespace App\Casts;

readonly class MoneyValue
{
    public function __construct(
        public ?float $amount,
        public string $currency = 'NGN',
        public string $symbol = '₦',
        public ?int $kobo = null,
    ) {}

    public function formatted(): string
    {
        if ($this->amount === null) {
            return '-';
        }

        return $this->symbol . number_format($this->amount, 2);
    }

    public function forPaystack(): ?int
    {
        return $this->kobo;
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
```

**Using the Money cast:**

```php
<?php

namespace App\Models;

use App\Casts\Money;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => Money::class . ':NGN,₦',
            'amount_usd' => Money::class . ':USD,$',
        ];
    }
}

// Usage
$payment = Payment::query()->first();

echo $payment->amount->formatted(); // "₦500.00"
echo $payment->amount->forPaystack(); // 50000
echo $payment->amount; // "₦500.00" (via __toString)

// Pass to Paystack SDK
Paystack::initializeTransaction(
    InitializeTransactionInputData::from([
        'email' => 'test@example.com',
        'amount' => $payment->amount->forPaystack(), // 50000
    ])
);
```

## Array/Object Cast for Complex Amounts

Cast multiple amount fields together:

```php
<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class PaymentAmounts implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): PaymentAmountsObject
    {
        return new PaymentAmountsObject(
            subtotal: isset($attributes['subtotal_kobo'])
                ? (float) $attributes['subtotal_kobo'] / 100
                : null,
            tax: isset($attributes['tax_kobo'])
                ? (float) $attributes['tax_kobo'] / 100
                : null,
            total: isset($attributes['total_kobo'])
                ? (float) $attributes['total_kobo'] / 100
                : null,
        );
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value instanceof PaymentAmountsObject) {
            return [
                'subtotal_kobo' => $value->subtotal !== null
                    ? (int) round($value->subtotal * 100)
                    : null,
                'tax_kobo' => $value->tax !== null
                    ? (int) round($value->tax * 100)
                    : null,
                'total_kobo' => $value->total !== null
                    ? (int) round($value->total * 100)
                    : null,
            ];
        }

        return [];
    }
}
```

**Value object for grouped amounts:**

```php
<?php

namespace App\Casts;

readonly class PaymentAmountsObject
{
    public function __construct(
        public ?float $subtotal = null,
        public ?float $tax = null,
        public ?float $total = null,
    ) {}

    public function formatted(): array
    {
        return [
            'subtotal' => $this->subtotal !== null
                ? '₦' . number_format($this->subtotal, 2)
                : '-',
            'tax' => $this->tax !== null
                ? '₦' . number_format($this->tax, 2)
                : '-',
            'total' => $this->total !== null
                ? '₦' . number_format($this->total, 2)
                : '-',
        ];
    }
}
```

## Immutable Money Cast

Prevent accidental modification of money values:

```php
<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ImmutableMoney implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ImmutableMoneyValue
    {
        if ($value === null) {
            return ImmutableMoneyValue::null();
        }

        return new ImmutableMoneyValue(
            kobo: (int) $value,
            currency: $attributes['currency'] ?? 'NGN',
        );
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof ImmutableMoneyValue) {
            return $value->toKobo();
        }

        return (int) round((float) $value * 100);
    }
}
```

**Immutable value object:**

```php
<?php

namespace App\Casts;

use RuntimeException;

readonly class ImmutableMoneyValue
{
    public function __construct(
        private int $kobo,
        private string $currency = 'NGN',
    ) {
        if ($kobo < 0) {
            throw new RuntimeException('Amount cannot be negative');
        }
    }

    public static function null(): self
    {
        return new self(0, 'NGN');
    }

    public function toKobo(): int
    {
        return $this->kobo;
    }

    public function toCurrency(): float
    {
        return $this->kobo / 100;
    }

    public function formatted(): string
    {
        $symbol = match ($this->currency) {
            'NGN' => '₦',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $this->currency . ' ',
        };

        return $symbol . number_format($this->toCurrency(), 2);
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new RuntimeException('Cannot add different currencies');
        }

        return new self($this->kobo + $other->kobo, $this->currency);
    }

    public function subtract(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new RuntimeException('Cannot subtract different currencies');
        }

        return new self($this->kobo - $other->kobo, $this->currency);
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
```

## Testing Custom Casts

Test that casts convert values correctly:

```php
use App\Casts\Kobo;
use App\Models\Payment;

test('kobo cast converts currency to integer for storage', function (): void {
    $payment = new Payment;
    $cast = new Kobo;

    $result = $cast->set($payment, 'amount', 500.50, []);

    expect($result)->toBe(50050);
});

test('kobo cast converts integer to currency for retrieval', function (): void {
    $payment = new Payment;
    $cast = new Kobo;

    $result = $cast->get($payment, 'amount', 50050, []);

    expect($result)->toBe(500.5);
});

test('kobo cast handles null values', function (): void {
    $payment = new Payment;
    $cast = new Kobo;

    expect($cast->set($payment, 'amount', null, []))->toBeNull();
    expect($cast->get($payment, 'amount', null, []))->toBeNull();
});

test('model uses kobo cast correctly', function (): void {
    $payment = Payment::factory()->make(['amount' => 500.00]);

    // Database stores integer
    expect($payment->getAttributes()['amount'])->toBe(50000);

    // Access returns float
    expect($payment->amount)->toBe(500.0);
});
```

## Using with Paystack SDK

The cast makes SDK integration seamless:

```php
// Model with Money cast
$payment = Payment::query()->create([
    'amount' => 500.00, // Stored as 50000
    'email' => 'customer@example.com',
]);

// Pass to Paystack - get kobo automatically
Paystack::initializeTransaction(
    InitializeTransactionInputData::from([
        'email' => $payment->email,
        'amount' => $payment->amount->forPaystack(), // 50000
    ])
);

// Display to user - get formatted currency
echo $payment->amount->formatted(); // "₦500.00"

// Calculations in code - use float
discount = $payment->amount->toCurrency() * 0.1; // 10% discount
```

## Related pages

- [API Resources](/examples/api-resources) — Transform casted values for JSON responses
- [Query Scopes](/examples/query-scopes) — Filter by casted amounts
- [Export/Reports](/examples/export-reports) — Export casted values in reports
- [Manager and Facade Usage](/examples/manager-and-facade) — SDK methods that expect kobo amounts