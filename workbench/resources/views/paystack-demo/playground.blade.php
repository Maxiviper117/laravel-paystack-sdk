<x-paystack-demo.layout
    title="Legacy Playground"
    heading="Playground"
    description="The old single-page playground has been split into separate feature pages."
    :pages="$pages"
    current-path="/paystack/demo/playground"
>
    <section class="border border-slate-200 bg-white p-5">
        <p class="text-sm text-slate-600">
            The legacy playground has been replaced by dedicated feature pages. Each page isolates one SDK feature so it is easier to test and inspect.
        </p>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="/paystack/demo/transactions" class="bg-orange-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-300">Transactions</a>
            <a href="/paystack/demo/customers" class="border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-100">Customers</a>
            <a href="/paystack/demo/plans" class="border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-100">Plans</a>
            <a href="/paystack/demo/subscriptions" class="border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-100">Subscriptions</a>
        </div>
    </section>
</x-paystack-demo.layout>
