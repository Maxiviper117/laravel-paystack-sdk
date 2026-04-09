@php
    $pageTitle = $title ?? 'Paystack SDK Kitchen Sink';
    $result = $result ?? null;
    $resultLabel = $resultLabel ?? null;
    $currentPath = $currentPath ?? null;
    $renderedResult = $result;

    if (is_object($renderedResult) && method_exists($renderedResult, 'toArray')) {
        $renderedResult = $renderedResult->toArray();
    }

    $secretKey = config('paystack.secret_key', '');
    $isTestMode = str_starts_with($secretKey, 'sk_test_');
    $keyDisplay = $isTestMode
        ? 'sk_test_'.substr($secretKey, 8, 8).'…'
        : ($secretKey !== '' ? 'sk_live_****' : 'key not set');

    $navSections = [
        'SDK Features' => [
            ['title' => 'Dashboard', 'path' => '/paystack/demo'],
            ['title' => 'Transactions', 'path' => '/paystack/demo/transactions'],
            ['title' => 'Customers', 'path' => '/paystack/demo/customers'],
            ['title' => 'Disputes', 'path' => '/paystack/demo/disputes'],
            ['title' => 'Plans', 'path' => '/paystack/demo/plans'],
            ['title' => 'Subscriptions', 'path' => '/paystack/demo/subscriptions'],
        ],
        'Platform' => [
            ['title' => 'Webhooks', 'path' => '/paystack/demo/webhooks'],
            ['title' => 'Billing Layer', 'path' => '/paystack/demo/billing-layer'],
        ],
        'Legacy' => [
            ['title' => 'Playground', 'path' => '/paystack/demo/playground'],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $heading ?? $pageTitle }} — Paystack SDK Kitchen Sink</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex h-screen overflow-hidden bg-white text-slate-900 antialiased">

    {{-- ── Sidebar ─────────────────────────────────── --}}
    <aside class="flex w-56 shrink-0 flex-col border-r border-slate-200 bg-white">

        {{-- App header --}}
        <div class="flex items-center gap-2.5 border-b border-slate-200 px-4 py-3.5">
            <div class="flex h-7 w-7 shrink-0 items-center justify-center bg-orange-400">
                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                </svg>
            </div>
            <span class="text-sm font-semibold tracking-tight text-slate-800">Paystack SDK Kitchen Sink</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Primary">
            @foreach ($navSections as $section => $items)
                <div class="{{ $loop->first ? '' : 'mt-6' }}">
                    <p class="mb-1 px-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400">{{ $section }}
                    </p>
                    <ul class="space-y-px">
                        @foreach ($items as $item)
                            @php $active = $currentPath === $item['path']; @endphp
                            <li>
                                <a href="{{ $item['path'] }}"
                                    class="flex items-center px-2 py-1.5 text-sm transition-colors {{ $active ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    {{ $item['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        {{-- API key / mode badge --}}
        <div class="border-t border-slate-200 px-3 py-3">
            <div class="flex items-center gap-2.5">
                <div
                    class="flex h-7 w-7 shrink-0 items-center justify-center text-[9px] font-bold {{ $isTestMode ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">
                    {{ $isTestMode ? 'TST' : 'LVE' }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-700">{{ $isTestMode ? 'Test mode' : 'Live mode' }}</p>
                    <p class="truncate font-mono text-[10px] text-slate-400">{{ $keyDisplay }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- ── Main area ────────────────────────────────── --}}
    <div class="flex flex-1 flex-col overflow-hidden bg-[#f7f7f8]">

        {{-- Top bar / breadcrumb --}}
        <header class="flex items-center border-b border-slate-200 bg-white px-6 py-2.75">
            <nav class="flex items-center gap-1.5 text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="/paystack/demo" class="hover:text-slate-900">Paystack SDK Kitchen Sink</a>
                @if (!empty($heading) && $currentPath !== '/paystack/demo')
                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-300" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                    <span class="font-medium text-slate-900">{{ $heading }}</span>
                @endif
            </nav>
        </header>

        {{-- Scrollable page body --}}
        <main class="flex-1 space-y-6 overflow-y-auto p-6">

            {{-- Page title + description --}}
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $heading ?? 'Paystack demo' }}</h1>
                @if (!empty($description))
                    <p class="mt-1 text-sm leading-6 text-slate-500">{{ $description }}</p>
                @endif
            </div>

            {{-- Page-specific content --}}
            {{ $slot }}

            {{-- API result panel --}}
            <section class="border border-slate-200 bg-white p-5">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                    {{ $resultLabel ?? 'API Response' }}
                </p>
                @if (!is_null($result))
                    <pre
                        class="mt-3 overflow-auto border-l-2 border-orange-400 bg-slate-50 pl-4 pr-4 pt-3 pb-3 font-mono text-xs leading-6 text-slate-700">{{ json_encode($renderedResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                @else
                    <p class="mt-2 text-sm text-slate-400">Submit a form above to see the live API response.</p>
                @endif
            </section>

        </main>
    </div>

</body>

</html>
