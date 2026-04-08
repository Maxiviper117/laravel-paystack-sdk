<x-paystack-demo.layout
    title="Legacy Playground"
    heading="The old single-page playground has been split into separate feature pages."
    description="Use the demo hub below to open the dedicated transaction, customer, plan, subscription, webhook, and billing pages."
    :pages="$pages"
>
    <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-200/60">
        <h2 class="text-2xl font-black tracking-tight text-slate-900 sm:text-4xl">Legacy playground</h2>
        <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
            The single-page playground has been replaced by separate feature pages so each flow is easier to test and reason about.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="/paystack/demo" class="inline-flex items-center rounded-2xl bg-orange-400 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-orange-300">Go to demo hub</a>
            <a href="/paystack/demo/transactions" class="inline-flex items-center rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-bold text-slate-900 transition hover:bg-slate-100">Transactions</a>
        </div>
    </section>
</x-paystack-demo.layout>
