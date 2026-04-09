<x-paystack-demo.layout title="Plans Demo" heading="Plans" description="Create, update, fetch, and list plans."
    :pages="$pages" :result="$result ?? null" :result-label="$resultLabel ?? null" current-path="/paystack/demo/plans">
    <section class="border border-slate-200 bg-white p-5">
        <form method="post" action="/paystack/demo/plans" class="border border-slate-100 bg-slate-50/60 p-4">
            @csrf
            <input type="hidden" name="action" value="create">
            <div class="grid gap-3 md:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">Name
                    <input name="name" type="text" value="Workbench Plan"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <label class="block text-sm font-medium text-slate-700">Amount
                    <input name="amount" type="number" step="0.01" value="25.00"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <label class="block text-sm font-medium text-slate-700">Interval
                    <input name="interval" type="text" value="monthly"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <label class="block text-sm font-medium text-slate-700">Description
                    <input name="description" type="text" value="Created from the workbench demo page."
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
            </div>
            <button
                class="mt-4 bg-orange-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-500">Create
                plan</button>
        </form>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/plans" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="update">
                <label class="block text-sm font-medium text-slate-700">Plan code
                    <input name="plan_code" type="text" placeholder="PLN_123"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <div class="mt-3 grid gap-3">
                    <label class="block text-sm font-medium text-slate-700">Name
                        <input name="name" type="text" placeholder="Optional"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Amount
                        <input name="amount" type="number" step="0.01" value="25.00"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Interval
                        <input name="interval" type="text" value="monthly"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Description
                        <input name="description" type="text" placeholder="Optional"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                </div>
                <button
                    class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Update</button>
            </form>

            <form method="post" action="/paystack/demo/plans" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="fetch">
                <label class="block text-sm font-medium text-slate-700">Plan identifier
                    <input name="plan_identifier" type="text" placeholder="PLN_123"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button
                    class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Fetch</button>
            </form>
        </div>

        <form method="post" action="/paystack/demo/plans" class="mt-4 border border-slate-200 bg-slate-50 p-4">
            @csrf
            <input type="hidden" name="action" value="list">
            <div class="grid gap-3 md:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">Per page
                    <input name="per_page" type="number" value="10"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <label class="block text-sm font-medium text-slate-700">Page
                    <input name="page" type="number" value="1"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
            </div>
            <button
                class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">List</button>
        </form>
    </section>
</x-paystack-demo.layout>