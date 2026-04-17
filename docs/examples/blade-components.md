# Blade Components

Use this flow when you need reusable UI components for payment-related interfaces in your Laravel application. Blade components provide a clean, maintainable way to build consistent payment interfaces across your application.

## Why Use Blade Components for Payments

Payment interfaces often repeat similar patterns throughout an application:

- **Consistency** — Status badges, buttons, and tables look the same everywhere
- **Maintainability** — Change styling in one place, update everywhere
- **Reusability** — Same components work in admin dashboards and customer portals
- **Testability** — Component logic can be unit tested independently
- **Developer experience** — Simple syntax like `<x-payment-status />` is cleaner than raw HTML

## Typical Component Scenarios

1. **Payment status badges** — Visual indicators showing transaction state
2. **Transaction history tables** — Reusable data presentation with consistent formatting
3. **Subscription management cards** — Display subscription details with actions
4. **Checkout buttons** — Branded Paystack payment buttons
5. **Receipt displays** — Formatted transaction receipts

## Payment Status Badge Component

Status badges provide immediate visual feedback about payment state. This component supports multiple sizes and automatically assigns colors and icons based on status.

```php
namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PaymentStatus extends Component
{
    public function __construct(
        public string $status,
        public ?string $size = 'md',
    ) {}

    public function color(): string
    {
        return match ($this->status) {
            'success', 'paid' => 'green',
            'pending', 'processing' => 'yellow',
            'failed', 'abandoned' => 'red',
            'reversed', 'refunded' => 'gray',
            default => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this->status) {
            'success', 'paid' => 'check-circle',
            'pending', 'processing' => 'clock',
            'failed', 'abandoned' => 'x-circle',
            'reversed', 'refunded' => 'arrow-uturn-left',
            default => 'question-mark-circle',
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.payment-status');
    }
}
```

```blade
{{-- resources/views/components/payment-status.blade.php --}}
@php
$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-0.5 text-sm',
    'lg' => 'px-3 py-1 text-base',
];
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full font-medium {{ $sizes[$size] }} bg-{{ $color() }}-100 text-{{ $color() }}-800">
    <x-icon :name="$icon()" class="w-4 h-4" />
    {{ ucfirst($status) }}
</span>
```

**How the component works:**

- **`color()` method** — Maps payment statuses to semantic colors (green for success, red for failures, yellow for pending)
- **`icon()` method** — Associates each status with an appropriate icon for quick visual recognition
- **Size variants** — Three sizes (sm, md, lg) fit different contexts from tables to headers
- **CSS classes** — Uses Tailwind utility classes for styling (easily adaptable to other frameworks)

**Color psychology in payment UIs:**

- **Green** — Success, completion, positive outcome
- **Red** — Failure, error, requires attention
- **Yellow** — Pending, in progress, waiting
- **Gray** — Neutral states like refunds or reversals

## Usage in Views

Using the component is straightforward with simple, readable syntax:

```blade
{{-- Single payment status --}}
<x-payment-status status="success" />

{{-- Different sizes for different contexts --}}
<x-payment-status status="pending" size="sm" />
<x-payment-status status="failed" size="lg" />

{{-- In a table with dynamic status --}}
<table class="min-w-full">
    <thead>
        <tr>
            <th>Reference</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($payments as $payment)
            <tr>
                <td>{{ $payment->reference }}</td>
                <td>
                    <x-payment-status :status="$payment->status" size="sm" />
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
```

**Best practices for status display:**

- Use smaller sizes in dense tables to prevent row height issues
- Use larger sizes in detail views where the status is a focal point
- Always use the dynamic `:status` attribute binding for real data

## Transaction History Component

Transaction tables appear in admin dashboards, customer portals, and receipts. This component handles empty states, pagination, and consistent formatting.

```php
namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TransactionHistory extends Component
{
    public function __construct(
        public \Illuminate\Database\Eloquent\Collection $transactions,
        public bool $showPagination = true,
        public ?string $emptyMessage = null,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.transaction-history');
    }
}
```

```blade
{{-- resources/views/components/transaction-history.blade.php --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    @if ($transactions->isEmpty())
        <div class="p-6 text-center text-gray-500">
            {{ $emptyMessage ?? 'No transactions found.' }}
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $transaction->reference }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction->currency }} {{ number_format($transaction->amount / 100, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-payment-status :status="$transaction->status" size="sm" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('transactions.show', $transaction) }}" class="text-indigo-600 hover:text-indigo-900">
                                View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($showPagination)
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $transactions->links() }}
            </div>
        @endif
    @endif
</div>
```

**Component features:**

- **Empty state handling** — Shows a friendly message when no transactions exist
- **Nested components** — Reuses the `<x-payment-status>` component for consistency
- **Amount formatting** — Converts kobo/cents to standard currency display
- **Optional pagination** — Can be disabled for views showing limited records
- **Customizable empty message** — Different contexts may need different messaging

**Data formatting considerations:**

- Amounts are stored by Paystack in the smallest currency unit (kobo for NGN, cents for USD). Always divide by 100 for display
- Dates should be formatted consistently across your application
- Reference codes are typically displayed in full for customer support purposes

## Subscription Card Component

Subscription cards display recurring billing information with relevant actions. This component adapts based on subscription status and provides management options.

```php
namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SubscriptionCard extends Component
{
    public function __construct(
        public \App\Models\Subscription $subscription,
        public bool $showActions = true,
    ) {}

    public function isActive(): bool
    {
        return in_array($this->subscription->status, ['active', 'non-renewing']);
    }

    public function isCancelled(): bool
    {
        return $this->subscription->status === 'cancelled';
    }

    public function nextPaymentText(): string
    {
        if ($this->isCancelled()) {
            return 'Ends on ' . $this->subscription->ends_at?->format('M d, Y');
        }

        return 'Next payment on ' . $this->subscription->next_payment_date?->format('M d, Y');
    }

    public function render(): View|Closure|string
    {
        return view('components.subscription-card');
    }
}
```

```blade
{{-- resources/views/components/subscription-card.blade.php --}}
<div class="bg-white rounded-lg shadow-md p-6 border-l-4 {{ $isActive() ? 'border-green-500' : 'border-gray-400' }}">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ $subscription->plan_name }}</h3>
            <p class="text-sm text-gray-500">{{ $subscription->paystack_subscription_code }}</p>
        </div>
        <x-payment-status :status="$subscription->status" />
    </div>

    <div class="space-y-2 mb-4">
        <div class="flex justify-between">
            <span class="text-gray-600">Amount:</span>
            <span class="font-medium">{{ $subscription->currency }} {{ number_format($subscription->amount / 100, 2) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-600">Interval:</span>
            <span class="font-medium capitalize">{{ $subscription->interval }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-600">{{ $isCancelled() ? 'Ends:' : 'Next Payment:' }}</span>
            <span class="font-medium">{{ $nextPaymentText() }}</span>
        </div>
    </div>

    @if ($showActions && $isActive())
        <div class="flex gap-2 mt-4 pt-4 border-t border-gray-200">
            <a href="{{ route('subscriptions.manage', $subscription) }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                Manage
            </a>

            @if (! $isCancelled())
                <form method="POST" action="{{ route('subscriptions.cancel', $subscription) }}"
                      onsubmit="return confirm('Are you sure you want to cancel this subscription?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-white text-red-600 text-sm font-medium rounded-md border border-red-300 hover:bg-red-50">
                        Cancel
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
```

**Adaptive UI based on status:**

- **Border color** — Green for active subscriptions, gray for inactive
- **Next payment text** — Shows "Next payment" for active subscriptions, "Ends on" for cancelled ones
- **Action buttons** — Only shown for active subscriptions that aren't cancelled
- **Cancel confirmation** — JavaScript confirm dialog prevents accidental cancellations

**Subscription states explained:**

- **Active** — Subscription is current and will renew
- **Non-renewing** — Subscription is active but won't renew at period end (customer cancelled)
- **Cancelled** — Subscription has ended or will end at period end
- **Past due** — Payment failed but subscription may still be active with grace period

## Pay with Paystack Button Component

The checkout button is your primary conversion element. This component creates a branded Paystack button that submits to your initialization endpoint.

```php
namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PaystackButton extends Component
{
    public function __construct(
        public string $email,
        public int $amount,
        public string $reference,
        public string $currency = 'NGN',
        public ?string $callbackUrl = null,
        public array $metadata = [],
        public ?string $label = null,
        public ?string $class = null,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.paystack-button');
    }
}
```

```blade
{{-- resources/views/components/paystack-button.blade.php --}}
@php
$buttonClass = $class ?? 'inline-flex items-center justify-center px-6 py-3 bg-[#3BB75E] text-white font-medium rounded-lg hover:bg-[#2fa14e] transition-colors';
@endphp

<form method="POST" action="{{ route('checkout.initialize') }}" class="inline">
    @csrf
    <input type="hidden" name="email" value="{{ $email }}">
    <input type="hidden" name="amount" value="{{ $amount }}">
    <input type="hidden" name="reference" value="{{ $reference }}">
    <input type="hidden" name="currency" value="{{ $currency }}">

    @if ($callbackUrl)
        <input type="hidden" name="callback_url" value="{{ $callbackUrl }}">
    @endif

    @foreach ($metadata as $key => $value)
        <input type="hidden" name="metadata[{{ $key }}]" value="{{ $value }}">
    @endforeach

    <button type="submit" class="{{ $buttonClass }}">
        <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
        </svg>
        {{ $label ?? 'Pay with Paystack' }}
    </button>
</form>
```

**Component design decisions:**

- **Form-based approach** — Posts to your server first, which then initializes with Paystack. This prevents exposing your Paystack keys to the frontend
- **Hidden inputs** — All payment parameters are included as hidden form fields
- **Paystack brand color** — Uses Paystack's green (#3BB75E) for brand consistency
- **Customizable label** — Supports custom button text while defaulting to "Pay with Paystack"
- **CSS class override** — Allows complete styling customization when needed

**Security considerations:**

- Never include your Paystack secret key in frontend code
- Always validate the amount on the server before initializing transactions
- The form posts to your server, which makes the actual Paystack API call

## Usage Examples

Here's how these components work together in real pages:

```blade
{{-- Checkout page --}}
<div class="max-w-md mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Complete Your Order</h1>

    <div class="bg-gray-50 p-4 rounded-lg mb-6">
        <p class="text-gray-600">Order Total:</p>
        <p class="text-3xl font-bold">₦{{ number_format($order->total / 100, 2) }}</p>
    </div>

    <x-paystack-button
        :email="$order->customer_email"
        :amount="$order->total"
        :reference="$order->payment_reference"
        currency="NGN"
        :callback-url="route('checkout.callback')"
        :metadata="['order_id' => $order->id]"
        label="Pay Now"
    />
</div>

{{-- Customer dashboard --}}
<div class="space-y-6">
    <h2 class="text-xl font-semibold">Your Subscriptions</h2>

    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($subscriptions as $subscription)
            <x-subscription-card :subscription="$subscription" />
        @endforeach
    </div>

    <h2 class="text-xl font-semibold mt-8">Recent Transactions</h2>
    <x-transaction-history :transactions="$transactions" />
</div>

{{-- Admin payment detail --}}
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Payment Details</h1>
        <x-payment-status :status="$payment->status" size="lg" />
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-gray-600">Reference</dt>
                <dd class="font-medium">{{ $payment->reference }}</dd>
            </div>
            <div>
                <dt class="text-gray-600">Amount</dt>
                <dd class="font-medium">{{ $payment->currency }} {{ number_format($payment->amount / 100, 2) }}</dd>
            </div>
            <div>
                <dt class="text-gray-600">Customer</dt>
                <dd class="font-medium">{{ $payment->customer_email }}</dd>
            </div>
            <div>
                <dt class="text-gray-600">Date</dt>
                <dd class="font-medium">{{ $payment->created_at->format('M d, Y H:i') }}</dd>
            </div>
        </dl>
    </div>
</div>
```

## Testing Components

Unit test your components to ensure they compute values correctly and handle edge cases.

```php
namespace Tests\Unit\View\Components;

use App\View\Components\PaymentStatus;
use App\View\Components\SubscriptionCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    public function test_renders_correct_color_for_success_status(): void
    {
        $component = new PaymentStatus('success');

        $this->assertEquals('green', $component->color());
        $this->assertEquals('check-circle', $component->icon());
    }

    public function test_renders_correct_color_for_failed_status(): void
    {
        $component = new PaymentStatus('failed');

        $this->assertEquals('red', $component->color());
        $this->assertEquals('x-circle', $component->icon());
    }

    public function test_renders_gray_for_unknown_status(): void
    {
        $component = new PaymentStatus('unknown_status');

        $this->assertEquals('gray', $component->color());
    }
}

class SubscriptionCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_identifies_active_subscriptions(): void
    {
        $subscription = \App\Models\Subscription::factory()->make([
            'status' => 'active',
        ]);

        $component = new SubscriptionCard($subscription);

        $this->assertTrue($component->isActive());
        $this->assertFalse($component->isCancelled());
    }

    public function test_identifies_cancelled_subscriptions(): void
    {
        $subscription = \App\Models\Subscription::factory()->make([
            'status' => 'cancelled',
        ]);

        $component = new SubscriptionCard($subscription);

        $this->assertFalse($component->isActive());
        $this->assertTrue($component->isCancelled());
    }

    public function test_shows_end_date_for_cancelled_subscriptions(): void
    {
        $subscription = \App\Models\Subscription::factory()->make([
            'status' => 'cancelled',
            'ends_at' => '2024-12-31',
        ]);

        $component = new SubscriptionCard($subscription);
        $text = $component->nextPaymentText();

        $this->assertStringContainsString('Ends on', $text);
        $this->assertStringContainsString('Dec 31, 2024', $text);
    }
}
```

**Testing approach:**

- **Test computation methods** like `color()` and `isActive()` directly
- **Use factory models** to create test data without database persistence
- **Test edge cases** like unknown statuses or missing dates
- **Verify conditional logic** for different subscription states

## Component Best Practices

1. **Keep components focused** — Each component should do one thing well
2. **Use computed properties** — Methods like `color()` and `isActive()` keep Blade templates clean
3. **Provide sensible defaults** — Default sizes, labels, and messages reduce boilerplate
4. **Support customization** — Allow CSS class overrides and custom labels
5. **Handle empty/null states** — Components should gracefully handle missing data
6. **Document props** — PHPDoc on constructor parameters helps IDE autocomplete
7. **Test logic** — Unit test component methods, feature test rendered output

## Related Pages

- [One-Time Checkout](/examples/checkout) — Payment flow that uses these components
- [Subscription Billing Flow](/examples/subscriptions) — Subscription management UI patterns
- [Manage Customers](/examples/customers) — Customer portal components
