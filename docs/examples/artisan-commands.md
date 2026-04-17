# Artisan Commands

Use this flow when you need CLI commands to manage Paystack resources, run billing operations, or support administrative tasks. Artisan commands are ideal for operations that need to run on a schedule, be triggered manually by administrators, or handle bulk data operations without requiring a web interface.

## Why Use Artisan Commands for Paystack Operations

Artisan commands provide several advantages for payment-related operations:

- **Scheduled operations**: Automatically sync data or verify pending transactions using Laravel's scheduler
- **Administrative tasks**: Give support staff CLI tools to investigate and fix payment issues
- **Bulk operations**: Process large datasets without web request timeouts
- **Background processing**: Run long-running operations without affecting web response times
- **Audit trails**: CLI operations are easily logged and monitored

## Typical Use Cases

1. **Sync customer records** from Paystack to your local database to keep data aligned
2. **Export transaction data** for accounting or reporting purposes
3. **Validate pending subscriptions or plans** to ensure data consistency
4. **Trigger refunds or dispute resolution** from the command line for support cases
5. **Verify stuck transactions** that may have been missed by webhooks

## Basic Command Structure

This example shows a customer sync command that fetches customers from Paystack and updates your local database. The command accepts options for pagination control, making it flexible for different data sizes.

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class SyncPaystackCustomers extends Command
{
    protected $signature = 'paystack:sync-customers
                            {--per-page=50 : Number of customers per page}
                            {--pages=1 : Number of pages to fetch}';

    protected $description = 'Sync Paystack customers to local database';

    public function handle(): int
    {
        $perPage = (int) $this->option('per-page');
        $pages = (int) $this->option('pages');

        $this->info('Fetching customers from Paystack...');

        for ($page = 1; $page <= $pages; $page++) {
            $response = Paystack::listCustomers(
                ListCustomersInputData::from([
                    'perPage' => $perPage,
                    'page' => $page,
                ])
            );

            $bar = $this->output->createProgressBar(count($response->customers));
            $bar->start();

            foreach ($response->customers as $customer) {
                // Update or create local customer record
                \App\Models\Customer::query()->updateOrCreate(
                    ['paystack_customer_code' => $customer->customerCode],
                    [
                        'email' => $customer->email,
                        'first_name' => $customer->firstName,
                        'last_name' => $customer->lastName,
                        'phone' => $customer->phone,
                        'metadata' => $customer->metadata,
                    ]
                );

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->info('Customer sync completed.');

        return self::SUCCESS;
    }
}
```

**Key aspects of this command:**

- **Progress bars** provide visual feedback during long-running operations
- **Pagination options** let you control memory usage for large datasets
- **`updateOrCreate`** ensures idempotent operations—running the command multiple times won't create duplicates
- **The Paystack facade** keeps the code concise while maintaining full SDK capabilities

## Transaction Verification Command

Pending transactions can sometimes get stuck if webhooks fail or callbacks are missed. This command proactively verifies pending payments and updates their status, which can be run manually or scheduled.

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class VerifyPendingTransactions extends Command
{
    protected $signature = 'paystack:verify-pending
                            {--reference= : Specific reference to verify}';

    protected $description = 'Verify pending transactions with Paystack';

    public function handle(): int
    {
        $query = \App\Models\Payment::query()
            ->where('status', 'pending');

        if ($reference = $this->option('reference')) {
            $query->where('reference', $reference);
        }

        $pendingPayments = $query->get();

        if ($pendingPayments->isEmpty()) {
            $this->warn('No pending transactions found.');

            return self::SUCCESS;
        }

        $this->info("Verifying {$pendingPayments->count()} pending transaction(s)...");

        $verified = 0;
        $failed = 0;

        foreach ($pendingPayments as $payment) {
            try {
                $response = Paystack::verifyTransaction(
                    VerifyTransactionInputData::from([
                        'reference' => $payment->reference,
                    ])
                );

                $payment->update([
                    'status' => $response->transaction->status,
                    'amount' => $response->transaction->amount,
                    'channel' => $response->transaction->channel,
                    'paid_at' => $response->transaction->paidAt,
                ]);

                $this->info("✓ Verified: {$payment->reference} - {$response->transaction->status}");
                $verified++;
            } catch (\Exception $e) {
                $this->error("✗ Failed: {$payment->reference} - {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Results: {$verified} verified, {$failed} failed");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
```

**How this command helps:**

- **Optional reference filter** allows targeting a specific transaction for investigation
- **Exception handling** continues processing even if one verification fails
- **Success/failure reporting** gives clear feedback on command completion
- **Return codes** indicate success or failure for scripting and monitoring systems

**When to use this command:**
- When you suspect webhook delivery issues
- As a daily reconciliation job to catch any missed payments
- When a customer reports a payment that isn't reflected in your system
- Before generating daily reports to ensure data accuracy

## Subscription Management Command

Support staff often need to quickly check subscription status without diving into code or databases. This command displays subscription details in a formatted table.

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maxiviper117\Paystack\Data\Input\Subscription\FetchSubscriptionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class CheckSubscriptionStatus extends Command
{
    protected $signature = 'paystack:check-subscription
                            {subscription : Subscription code to check}';

    protected $description = 'Check the status of a Paystack subscription';

    public function handle(): int
    {
        $subscriptionCode = $this->argument('subscription');

        try {
            $response = Paystack::fetchSubscription(
                FetchSubscriptionInputData::from([
                    'idOrCode' => $subscriptionCode,
                ])
            );

            $subscription = $response->subscription;

            $this->table(
                ['Property', 'Value'],
                [
                    ['Code', $subscription->subscriptionCode],
                    ['Status', $subscription->status],
                    ['Amount', $subscription->amount],
                    ['Next Payment', $subscription->nextPaymentDate?->format('Y-m-d H:i:s') ?? 'N/A'],
                    ['Created', $subscription->createdAt?->format('Y-m-d H:i:s')],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to fetch subscription: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
```

**Features of this command:**

- **Table output** presents subscription data in an easy-to-read format
- **Date formatting** makes timestamps human-readable
- **Required argument** ensures the subscription code is always provided
- **Error handling** catches API errors and displays them clearly

**Example usage:**
```bash
# Check a specific subscription
php artisan paystack:check-subscription SUB_abc123xyz

# This outputs:
# +--------------+------------------------+
# | Property     | Value                  |
# +--------------+------------------------+
# | Code         | SUB_abc123xyz          |
# | Status       | active                 |
# | Amount       | 500000                 |
# | Next Payment | 2024-12-25 00:00:00    |
# | Created      | 2024-11-25 10:30:15    |
# +--------------+------------------------+
```

## Scheduling Commands

Laravel's task scheduler makes it easy to run these commands automatically. Register them in `routes/console.php` (Laravel 11+) or `app/Console/Kernel.php` (Laravel 10 and earlier).

```php
use Illuminate\Support\Facades\Schedule;

// Verify pending transactions every 15 minutes to catch missed webhooks
Schedule::command('paystack:verify-pending')->everyFifteenMinutes();

// Daily sync of customer data to keep local database current
Schedule::command('paystack:sync-customers --pages=5')->daily();

// Weekly subscription health check
Schedule::command('paystack:check-expiring-subscriptions')->weekly();
```

**Scheduling best practices:**

- **Stagger schedules** to avoid API rate limits—don't run all Paystack commands at the same time
- **Use appropriate frequencies**—transaction verification needs to be frequent, while customer syncs can be daily
- **Monitor scheduled tasks** using Laravel's task scheduling hooks or external monitoring
- **Set up queue workers** for commands that interact with the database heavily

## Testing Commands

Commands should be tested to ensure they handle edge cases properly. Laravel's testing tools make this straightforward.

```php
namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncPaystackCustomersTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_displays_success_message(): void
    {
        $this->artisan('paystack:sync-customers', ['--pages' => 1])
            ->assertSuccessful()
            ->expectsOutput('Fetching customers from Paystack...')
            ->expectsOutput('Customer sync completed.');
    }

    public function test_command_handles_empty_results(): void
    {
        // Mock Paystack to return empty customer list
        $this->artisan('paystack:sync-customers')
            ->assertSuccessful();
    }
}
```

**Testing strategies:**

- **Mock external APIs** to avoid making real Paystack calls during tests
- **Test option validation** to ensure command signatures work correctly
- **Verify database changes** to confirm sync operations work as expected
- **Test failure scenarios** like API errors or network timeouts

## Security Considerations

When creating commands that handle payment data:

- **Never log full payment details** like card numbers, authorization codes, or customer sensitive data
- **Use confirmation prompts** for destructive operations using `$this->confirm()`
- **Restrict sensitive commands** to authorized users via middleware or by checking user roles
- **Validate inputs** just like you would in web controllers
- **Log command execution** for audit trails without exposing sensitive data

```php
// Example of a destructive operation with confirmation
public function handle(): int
{
    if (! $this->confirm('This will cancel all pending subscriptions. Continue?')) {
        return self::FAILURE;
    }
    
    // Proceed with operation...
}
```

## Related Pages

- [Manager and Facade Usage](/examples/manager-and-facade) — Learn more about using the Paystack facade
- [Customers](/customers) — Reference for customer operations
- [Transactions](/transactions) — Reference for transaction operations
- [Queued Jobs](/examples/queued-jobs) — For operations that need to run asynchronously
