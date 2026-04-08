<x-paystack-demo.layout title="Paystack Workbench Demo"
    heading="Interactive demo pages for every Paystack feature in this package."
    description="Use the feature pages to initialize and verify transactions, manage customers and plans, create subscriptions, inspect webhook intake, and exercise the optional billing layer."
    :pages="$pages">
    <section
        class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-200/60">
        <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,146,60,0.16),transparent_28%),radial-gradient(circle_at_bottom_left,rgba(99,102,241,0.14),transparent_28%)]">
        </div>
        <div class="relative max-w-3xl">
            <h2 class="max-w-3xl text-2xl font-black tracking-tight text-slate-900 sm:text-4xl">Choose a focused demo
                page for the feature you want to test.</h2>
            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                Each page isolates one slice of the SDK, so you can exercise the request/response flow without the noise
                of the full playground.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="/paystack/demo/transactions"
                    class="inline-flex items-center rounded-2xl bg-orange-400 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-orange-300">Open
                    transactions</a>
                <a href="/paystack/demo/playground"
                    class="inline-flex items-center rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-bold text-slate-900 transition hover:bg-slate-100">Legacy
                    playground</a>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($pages as $page)
            <a href="{{ $page['path'] }}"
                class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-lg shadow-slate-200/60 transition hover:-translate-y-1 hover:border-orange-400/40 hover:bg-slate-50">
                <div
                    class="mb-4 inline-flex rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">
                    {{ $page['title'] }}
                </div>
                <h3 class="text-xl font-bold text-slate-900">{{ $page['title'] }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $page['description'] }}</p>
                <div class="mt-5 text-sm font-semibold text-orange-600 transition group-hover:text-orange-500">Open page →
                </div>
            </a>
        @endforeach
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6">
        <h3 class="text-2xl font-bold text-slate-900">What to test</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">SDK</p>
                <p class="mt-2 text-slate-600">Transactions, customers, plans, subscriptions, and manager/facade usage.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Platform</p>
                <p class="mt-2 text-slate-600">Webhook intake, stored calls, processed events, and the optional Billable
                    Eloquent layer.</p>
            </div>
        </div>
    </section>
</x-paystack-demo.layout>