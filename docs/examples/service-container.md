# Service Container Binding

Use this flow when you need to customize Paystack SDK behavior per tenant, environment, or use case. Laravel's service container lets you bind different configurations and implementations dynamically.

## Why Use Container Binding for Paystack

- **Multi-tenancy** — Different API keys per tenant
- **Environment-specific** — Test vs. live mode switching
- **Feature flags** — Enable/disable Paystack features dynamically
- **Testing** — Swap implementations for mocks
- **Customization** — Custom connectors or middleware per context

## Basic Contextual Binding

Bind different Paystack configurations based on the current context:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Maxiviper117\Paystack\PaystackManager;

class PaystackServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind PaystackManager with tenant-aware configuration
        $this->app->singleton(PaystackManager::class, function ($app): PaystackManager {
            $config = $this->getTenantConfig();

            return new PaystackManager($app, $config);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function getTenantConfig(): array
    {
        $tenant = request()->attributes->get('current_tenant');

        if ($tenant !== null && $tenant->paystack_secret_key !== null) {
            return [
                'secret_key' => $tenant->paystack_secret_key,
                'public_key' => $tenant->paystack_public_key,
                'base_url' => config('paystack.base_url'),
            ];
        }

        return config('paystack');
    }
}
```

## Multi-Tenant Configuration

Support multiple tenants with isolated Paystack accounts:

```php
<?php

namespace App\Services\Paystack;

use Maxiviper117\Paystack\PaystackManager;

class TenantPaystackFactory
{
    public function __construct(
        private \Illuminate\Contracts\Foundation\Application $app,
    ) {}

    public function forTenant(\App\Models\Tenant $tenant): PaystackManager
    {
        $config = [
            'secret_key' => $tenant->paystack_secret_key,
            'public_key' => $tenant->paystack_public_key,
            'base_url' => config('paystack.base_url'),
            'webhook_secret' => $tenant->paystack_webhook_secret,
        ];

        return new PaystackManager($this->app, $config);
    }

    public function forCurrentTenant(): PaystackManager
    {
        $tenant = auth()->user()?->tenant;

        if ($tenant === null) {
            throw new \RuntimeException('No tenant context available');
        }

        return $this->forTenant($tenant);
    }
}
```

**Using in a controller:**

```php
<?php

namespace App\Http\Controllers;

use App\Services\Paystack\TenantPaystackFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantPaymentController extends Controller
{
    public function __construct(
        private TenantPaystackFactory $paystackFactory,
    ) {}

    public function initialize(Request $request): JsonResponse
    {
        $paystack = $this->paystackFactory->forCurrentTenant();

        $response = $paystack->initializeTransaction(
            \Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData::from([
                'email' => $request->input('email'),
                'amount' => $request->integer('amount'),
            ])
        );

        return response()->json([
            'authorization_url' => $response->authorizationUrl,
        ]);
    }
}
```

## Environment-Based Binding

Switch between test and live mode based on context:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Maxiviper117\Paystack\PaystackManager;

class EnvironmentPaystackProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('paystack.live', function ($app): PaystackManager {
            return new PaystackManager($app, [
                'secret_key' => config('paystack.live_secret_key'),
                'public_key' => config('paystack.live_public_key'),
                'base_url' => config('paystack.base_url'),
            ]);
        });

        $this->app->singleton('paystack.test', function ($app): PaystackManager {
            return new PaystackManager($app, [
                'secret_key' => config('paystack.test_secret_key'),
                'public_key' => config('paystack.test_public_key'),
                'base_url' => config('paystack.base_url'),
            ]);
        });

        // Default binding based on environment
        $this->app->singleton(PaystackManager::class, function ($app): PaystackManager {
            $useLive = request()->boolean('live_mode')
                || config('paystack.default_mode') === 'live';

            return $useLive
                ? $app->make('paystack.live')
                : $app->make('paystack.test');
        });
    }
}
```

**Using specific mode:**

```php
// Use live mode explicitly
$livePaystack = app('paystack.live');

// Use test mode explicitly
$testPaystack = app('paystack.test');

// Use default (based on config/request)
$paystack = app(PaystackManager::class);
```

## Conditional Feature Binding

Enable or disable Paystack features based on configuration:

```php
<?php

namespace App\Providers;

use App\Services\Paystack\DisabledPaystack;
use App\Services\Paystack\EnabledPaystack;
use Illuminate\Support\ServiceProvider;
use Maxiviper117\Paystack\Contracts\PaystackInterface;

class FeatureFlagPaystackProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaystackInterface::class, function ($app): PaystackInterface {
            if (! config('features.payments.enabled', true)) {
                return new DisabledPaystack;
            }

            return new EnabledPaystack(
                manager: $app->make(\Maxiviper117\Paystack\PaystackManager::class),
                features: [
                    'subscriptions' => config('features.payments.subscriptions', true),
                    'refunds' => config('features.payments.refunds', true),
                    'transfers' => config('features.payments.transfers', false),
                ],
            );
        });
    }
}
```

**Disabled implementation:**

```php
<?php

namespace App\Services\Paystack;

use Maxiviper117\Paystack\Contracts\PaystackInterface;
use Maxiviper117\Paystack\Exceptions\FeatureDisabledException;

class DisabledPaystack implements PaystackInterface
{
    public function initializeTransaction($data): never
    {
        throw new FeatureDisabledException('Payments are currently disabled');
    }

    public function verifyTransaction($data): never
    {
        throw new FeatureDisabledException('Payments are currently disabled');
    }

    // ... all methods throw FeatureDisabledException
}
```

**Enabled implementation with feature checks:**

```php
<?php

namespace App\Services\Paystack;

use Maxiviper117\Paystack\Contracts\PaystackInterface;
use Maxiviper117\Paystack\Exceptions\FeatureDisabledException;
use Maxiviper117\Paystack\PaystackManager;

class EnabledPaystack implements PaystackInterface
{
    /**
     * @param  array<string, bool>  $features
     */
    public function __construct(
        private PaystackManager $manager,
        private array $features,
    ) {}

    public function initializeTransaction($data): mixed
    {
        return $this->manager->initializeTransaction($data);
    }

    public function createSubscription($data): mixed
    {
        if (! $this->features['subscriptions']) {
            throw new FeatureDisabledException('Subscriptions are currently disabled');
        }

        return $this->manager->createSubscription($data);
    }

    public function createRefund($data): mixed
    {
        if (! $this->features['refunds']) {
            throw new FeatureDisabledException('Refunds are currently disabled');
        }

        return $this->manager->createRefund($data);
    }
}
```

## Scoped Bindings for Testing

Create isolated Paystack instances for testing:

```php
<?php

namespace Tests;

use Maxiviper117\Paystack\PaystackManager;

trait InteractsWithPaystack
{
    protected function mockPaystack(): \Mockery\MockInterface
    {
        $mock = \Mockery::mock(PaystackManager::class);

        $this->app->instance(PaystackManager::class, $mock);

        return $mock;
    }

    protected function fakePaystack(): void
    {
        $this->app->singleton(PaystackManager::class, function (): FakePaystackManager {
            return new FakePaystackManager;
        });
    }

    protected function withTestCredentials(): void
    {
        $this->app->singleton(PaystackManager::class, function ($app): PaystackManager {
            return new PaystackManager($app, [
                'secret_key' => 'sk_test_' . uniqid(),
                'public_key' => 'pk_test_' . uniqid(),
                'base_url' => 'https://api.paystack.co',
            ]);
        });
    }
}
```

## Custom Connector Binding

Bind a custom connector with additional middleware:

```php
<?php

namespace App\Providers;

use App\Integrations\Paystack\CustomPaystackConnector;
use Illuminate\Support\ServiceProvider;
use Maxiviper117\Paystack\Integrations\PaystackConnector;

class CustomConnectorProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaystackConnector::class, function ($app): PaystackConnector {
            return new CustomPaystackConnector(
                apiKey: config('paystack.secret_key'),
                baseUrl: config('paystack.base_url'),
                // Add custom middleware
                middleware: [
                    new \App\Integrations\Paystack\LoggingMiddleware,
                    new \App\Integrations\Paystack\RetryMiddleware,
                ],
            );
        });
    }
}
```

**Custom connector:**

```php
<?php

namespace App\Integrations\Paystack;

use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Saloon\Http\Senders\GuzzleSender;

class CustomPaystackConnector extends PaystackConnector
{
    /**
     * @param  array<int, object>  $middleware
     */
    public function __construct(
        string $apiKey,
        string $baseUrl,
        private array $middleware = [],
    ) {
        parent::__construct($apiKey, $baseUrl);
    }

    public function boot(GuzzleSender $sender): void
    {
        parent::boot($sender);

        foreach ($this->middleware as $middleware) {
            $sender->middleware()->push($middleware);
        }
    }
}
```

## Deferred Binding for Performance

Defer Paystack initialization until it's actually needed:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Maxiviper117\Paystack\PaystackManager;

class DeferredPaystackProvider extends ServiceProvider
{
    protected $defer = true; // Laravel 10 and below

    // Laravel 11+ uses ShouldDeferLoadingProvider interface instead

    public function register(): void
    {
        $this->app->singleton(PaystackManager::class, function ($app): PaystackManager {
            // This only runs when PaystackManager is first resolved
            return new PaystackManager($app, config('paystack'));
        });
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            PaystackManager::class,
            'paystack',
        ];
    }
}
```

## Testing Container Bindings

Test that your bindings resolve correctly:

```php
use Maxiviper117\Paystack\PaystackManager;

test('paystack manager is bound correctly', function (): void {
    $manager = app(PaystackManager::class);

    expect($manager)->toBeInstanceOf(PaystackManager::class);
});

test('tenant-specific configuration is used', function (): void {
    $tenant = \App\Models\Tenant::factory()->create([
        'paystack_secret_key' => 'sk_tenant_123',
    ]);

    request()->attributes->set('current_tenant', $tenant);

    $manager = app(PaystackManager::class);

    // Verify the manager uses tenant credentials
    // (Implementation depends on how you expose config in manager)
});

test('feature flags disable functionality', function (): void {
    config(['features.payments.enabled' => false]);

    $paystack = app(\Maxiviper117\Paystack\Contracts\PaystackInterface::class);

    expect(fn () => $paystack->initializeTransaction([]))
        ->toThrow(\Maxiviper117\Paystack\Exceptions\FeatureDisabledException::class);
});
```

## Related pages

- [Testing Paystack Integrations](/examples/testing) — Mock and fake Paystack in tests
- [Error Handling](/examples/error-handling) — Handle exceptions from bound implementations
- [Manager and Facade Usage](/examples/manager-and-facade) — Default SDK usage patterns
- [Middleware](/examples/middleware) — Custom middleware for connectors