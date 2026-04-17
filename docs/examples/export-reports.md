# Export and Reports

Use this flow when you need to generate CSV/Excel exports of Paystack data for accounting, reconciliation, or analysis. Laravel's export capabilities make it easy to transform payment data into formats accountants and analysts can work with.

## Why Generate Exports for Paystack Data

- **Accounting** — Monthly transaction reports for bookkeeping
- **Reconciliation** — Match Paystack settlements with local records
- **Analysis** — Export data for business intelligence tools
- **Compliance** — Audit trails and regulatory reporting
- **Portability** — Share data with stakeholders who don't have system access

## Typical Export Scenarios

1. **Transaction reports** — Daily/weekly/monthly transaction summaries
2. **Revenue reports** — Revenue by channel, currency, or date range
3. **Refund reports** — Track refund volumes and processing times
4. **Subscription reports** — MRR, churn, and renewal analytics
5. **Settlement reconciliation** — Match Paystack payouts to transactions

## Simple CSV Export

Basic CSV export using Laravel's `csv` response:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentExportController extends Controller
{
    public function exportCsv(Request $request): StreamedResponse
    {
        $filename = 'payments_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        $payments = Payment::query()
            ->forUser(auth()->user())
            ->when($request->filled('from'), function ($query) use ($request): void {
                $query->whereDate('created_at', '>=', $request->input('from'));
            })
            ->when($request->filled('to'), function ($query) use ($request): void {
                $query->whereDate('created_at', '<=', $request->input('to'));
            })
            ->paid()
            ->cursor(); // Memory-efficient for large datasets

        return response()->stream(function () use ($payments): void {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, [
                'Reference',
                'Date',
                'Amount (Kobo)',
                'Amount (NGN)',
                'Currency',
                'Status',
                'Channel',
                'Customer Email',
            ]);

            // Data rows
            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $payment->reference,
                    $payment->created_at->format('Y-m-d H:i:s'),
                    $payment->amount,
                    number_format($payment->amount / 100, 2),
                    $payment->currency,
                    $payment->status,
                    $payment->channel,
                    $payment->user->email,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
```

**Memory-efficient with cursor:**

The `cursor()` method uses Laravel's lazy collection to process records one at a time, keeping memory usage low even for large exports.

## Excel Export with Spatie Package

For more complex Excel exports, use `spatie/simple-excel`:

```bash
composer require spatie/simple-excel
```

```php
<?php

namespace App\Exports;

use App\Models\Payment;
use Carbon\Carbon;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentExport
{
    public function export(
        ?Carbon $from = null,
        ?Carbon $to = null,
        ?int $userId = null
    ): StreamedResponse {
        $filename = 'payments_' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($from, $to, $userId): void {
            $writer = SimpleExcelWriter::create('php://output');

            // Add header row with styling
            $writer->addRow([
                'Reference' => 'Reference',
                'Date' => 'Date',
                'Amount' => 'Amount',
                'Currency' => 'Currency',
                'Status' => 'Status',
                'Channel' => 'Channel',
                'Customer' => 'Customer Email',
            ]);

            Payment::query()
                ->when($userId, fn ($q) => $q->where('user_id', $userId))
                ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
                ->paid()
                ->with('user')
                ->chunk(1000, function ($payments) use ($writer): void {
                    foreach ($payments as $payment) {
                        $writer->addRow([
                            'Reference' => $payment->reference,
                            'Date' => $payment->created_at->format('Y-m-d H:i:s'),
                            'Amount' => $payment->amount / 100,
                            'Currency' => $payment->currency,
                            'Status' => $payment->status,
                            'Channel' => $payment->channel,
                            'Customer' => $payment->user?->email,
                        ]);
                    }
                });

            $writer->close();
        }, $filename);
    }
}
```

## Revenue Report by Channel

Generate a summary report showing revenue breakdown by payment channel:

```php
<?php

namespace App\Services\Reports;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RevenueByChannelReport
{
    /**
     * @return Collection<int, object>
     */
    public function generate(
        Carbon $from,
        Carbon $to
    ): Collection {
        return Payment::query()
            ->paid()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('channel, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('channel')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn ($row) => (object) [
                'channel' => $row->channel,
                'transaction_count' => $row->count,
                'total_kobo' => $row->total_amount,
                'total_ngn' => number_format($row->total_amount / 100, 2),
                'average_transaction' => number_format($row->total_amount / $row->count / 100, 2),
                'percentage' => 0, // Calculated below
            ])
            ->tap(function ($collection): void {
                $total = $collection->sum('total_kobo');
                $collection->each(function ($row) use ($total): void {
                    $row->percentage = $total > 0
                        ? round(($row->total_kobo / $total) * 100, 2)
                        : 0;
                });
            });
    }

    public function toCsv(Collection $data): string
    {
        $lines = [
            'Channel,Transactions,Total (NGN),Average (NGN),Percentage',
        ];

        foreach ($data as $row) {
            $lines[] = implode(',', [
                $row->channel,
                $row->transaction_count,
                $row->total_ngn,
                $row->average_transaction,
                $row->percentage . '%',
            ]);
        }

        return implode("\n", $lines);
    }
}
```

**Controller usage:**

```php
<?php

namespace App\Http\Controllers;

use App\Services\Reports\RevenueByChannelReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function __construct(
        private RevenueByChannelReport $revenueReport,
    ) {}

    public function revenueByChannel(Request $request): Response
    {
        $from = Carbon::parse($request->input('from', now()->subMonth()));
        $to = Carbon::parse($request->input('to', now()));

        $data = $this->revenueReport->generate($from, $to);

        if ($request->boolean('download')) {
            $csv = $this->revenueReport->toCsv($data);

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="revenue_by_channel.csv"',
            ]);
        }

        return response()->json([
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'data' => $data,
        ]);
    }
}
```

## Daily Settlement Reconciliation

Match Paystack settlements with your local transaction records:

```php
<?php

namespace App\Services\Reports;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SettlementReconciliationReport
{
    /**
     * Generate reconciliation report for a settlement period.
     *
     * @return array<string, mixed>
     */
    public function generate(Carbon $settlementDate): array
    {
        // Paystack typically settles T+1 (next business day)
        $transactionDate = $settlementDate->copy()->subDay();

        $transactions = Payment::query()
            ->paid()
            ->whereDate('paid_at', $transactionDate)
            ->get();

        $summary = [
            'settlement_date' => $settlementDate->toDateString(),
            'transaction_date' => $transactionDate->toDateString(),
            'total_transactions' => $transactions->count(),
            'gross_amount_kobo' => $transactions->sum('amount'),
            'gross_amount_ngn' => $transactions->sum('amount') / 100,
            'channels' => $transactions->groupBy('channel')
                ->map(fn ($group) => [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount') / 100,
                ]),
            'transaction_ids' => $transactions->pluck('reference')->toArray(),
        ];

        return $summary;
    }

    public function toCsv(array $summary): string
    {
        $lines = [
            'Settlement Reconciliation Report',
            "Settlement Date: {$summary['settlement_date']}",
            "Transaction Date: {$summary['transaction_date']}",
            '',
            'Metric,Value',
            "Total Transactions,{$summary['total_transactions']}",
            "Gross Amount (NGN),{$summary['gross_amount_ngn']}",
            '',
            'Channel Breakdown',
            'Channel,Count,Amount (NGN)',
        ];

        foreach ($summary['channels'] as $channel => $data) {
            $lines[] = "{$channel},{$data['count']},{$data['amount']}";
        }

        $lines[] = '';
        $lines[] = 'Transaction References';
        $lines[] = implode(',', $summary['transaction_ids']);

        return implode("\n", $lines);
    }
}
```

## Subscription Metrics Export

Export subscription metrics for financial planning:

```php
<?php

namespace App\Services\Reports;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SubscriptionMetricsReport
{
    /**
     * @return array<string, mixed>
     */
    public function generate(): array
    {
        $now = now();

        return [
            'generated_at' => $now->toIso8601String(),
            'active_subscriptions' => Subscription::query()->active()->count(),
            'mrr' => $this->calculateMrr(),
            'upcoming_renewals' => $this->getUpcomingRenewals(7),
            'recent_cancellations' => $this->getRecentCancellations(30),
            'plan_breakdown' => $this->getPlanBreakdown(),
        ];
    }

    private function calculateMrr(): float
    {
        // Sum monthly plan amounts for active subscriptions
        return Subscription::query()
            ->active()
            ->with('plan')
            ->get()
            ->sum(fn ($sub) => $sub->plan?->amount ?? 0) / 100;
    }

    /**
     * @return Collection<int, object>
     */
    private function getUpcomingRenewals(int $days): Collection
    {
        return Subscription::query()
            ->active()
            ->whereBetween('next_payment_date', [now(), now()->addDays($days)])
            ->with('user')
            ->get()
            ->map(fn ($sub) => (object) [
                'subscription_code' => $sub->subscription_code,
                'customer_email' => $sub->user?->email,
                'plan_name' => $sub->plan_name,
                'renewal_date' => $sub->next_payment_date?->toDateString(),
                'amount' => $sub->amount / 100,
            ]);
    }

    /**
     * @return Collection<int, object>
     */
    private function getRecentCancellations(int $days): Collection
    {
        return Subscription::query()
            ->where('status', 'cancelled')
            ->where('updated_at', '>=', now()->subDays($days))
            ->with('user')
            ->get()
            ->map(fn ($sub) => (object) [
                'subscription_code' => $sub->subscription_code,
                'customer_email' => $sub->user?->email,
                'cancelled_at' => $sub->updated_at->toDateString(),
            ]);
    }

    /**
     * @return Collection<int, object>
     */
    private function getPlanBreakdown(): Collection
    {
        return Subscription::query()
            ->active()
            ->selectRaw('plan_code, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('plan_code')
            ->get()
            ->map(fn ($row) => (object) [
                'plan_code' => $row->plan_code,
                'active_subscriptions' => $row->count,
                'mrr_contribution' => $row->total_amount / 100,
            ]);
    }
}
```

## Queued Export for Large Datasets

For large exports that take time, queue the job and notify when complete:

```php
<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\ExportReady;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;

class GeneratePaymentExport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300; // 5 minutes

    public function __construct(
        public int $userId,
        public string $from,
        public string $to,
    ) {}

    public function handle(): void
    {
        $user = User::query()->find($this->userId);

        if ($user === null) {
            return;
        }

        $filename = "exports/payments_{$this->userId}_{$this->from}_{$this->to}.xlsx";

        $writer = SimpleExcelWriter::create(
            Storage::disk('local')->path($filename)
        );

        \App\Models\Payment::query()
            ->forUserId($this->userId)
            ->whereBetween('created_at', [$this->from, $this->to])
            ->chunk(1000, function ($payments) use ($writer): void {
                foreach ($payments as $payment) {
                    $writer->addRow([
                        'Reference' => $payment->reference,
                        'Date' => $payment->created_at->format('Y-m-d H:i:s'),
                        'Amount' => $payment->amount / 100,
                        'Status' => $payment->status,
                        'Channel' => $payment->channel,
                    ]);
                }
            });

        $writer->close();

        // Notify user that export is ready
        $user->notify(new ExportReady(
            downloadUrl: Storage::disk('local')->url($filename),
            expiresAt: now()->addDays(7),
        ));
    }
}
```

**Dispatching the export job:**

```php
use App\Jobs\GeneratePaymentExport;

public function requestExport(Request $request): JsonResponse
{
    GeneratePaymentExport::dispatch(
        userId: auth()->id(),
        from: $request->input('from'),
        to: $request->input('to'),
    );

    return response()->json([
        'message' => 'Export is being generated. You will be notified when it is ready.',
    ]);
}
```

## Testing Exports

Test that exports contain the expected data:

```php
use App\Models\Payment;
use App\Services\Reports\RevenueByChannelReport;

test('revenue report calculates totals correctly', function (): void {
    Payment::factory()->create([
        'status' => 'success',
        'channel' => 'card',
        'amount' => 10000,
        'created_at' => now(),
    ]);

    Payment::factory()->create([
        'status' => 'success',
        'channel' => 'bank_transfer',
        'amount' => 20000,
        'created_at' => now(),
    ]);

    $report = new RevenueByChannelReport;
    $data = $report->generate(now()->startOfMonth(), now()->endOfMonth());

    expect($data)->toHaveCount(2);
    expect($data->firstWhere('channel', 'card')->total_kobo)->toBe(10000);
    expect($data->firstWhere('channel', 'bank_transfer')->total_kobo)->toBe(20000);
});
```

## Related pages

- [Query Scopes](/examples/query-scopes) — Filter data before exporting
- [API Resources](/examples/api-resources) — Transform data for JSON reports
- [Scheduled Tasks](/examples/scheduled-tasks) — Automated report generation
- [Queued Jobs](/examples/queued-jobs) — Background processing for large exports