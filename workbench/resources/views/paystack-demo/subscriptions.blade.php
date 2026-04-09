<x-paystack-demo.layout title="Subscriptions Demo" heading="Subscriptions"
    description="Create, fetch, list, enable, disable, and manage subscription update links." :pages="$pages"
    :result="$result ?? null" :result-label="$resultLabel ?? null" current-path="/paystack/demo/subscriptions">
    <section class="border border-slate-200 bg-white p-5">
        <form method="post" action="/paystack/demo/subscriptions" class="border border-slate-100 bg-slate-50/60 p-4">
            @csrf
            <input type="hidden" name="action" value="create">
            <div class="grid gap-3 md:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">Customer code
                    <input name="customer" type="text" value="CUS_123"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <label class="block text-sm font-medium text-slate-700">Plan code
                    <input name="plan" type="text" value="PLN_123"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <label class="block text-sm font-medium text-slate-700">Authorization
                    <input name="authorization" type="text" placeholder="Optional"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <label class="block text-sm font-medium text-slate-700">Start date
                    <input name="start_date" type="text" placeholder="Optional"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
            </div>
            <button
                class="mt-4 bg-orange-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-500">Create
                subscription</button>
        </form>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/subscriptions"
                class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="fetch">
                <label class="block text-sm font-medium text-slate-700">Subscription identifier
                    <input name="subscription_identifier" type="text" placeholder="SUB_123"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button
                    class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Fetch</button>
            </form>

            <form method="post" action="/paystack/demo/subscriptions"
                class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="list">
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-700">Customer filter
                        <input name="customer" type="text" placeholder="Optional"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Plan filter
                        <input name="plan" type="text" placeholder="Optional"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
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
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/subscriptions"
                class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="enable">
                <label class="block text-sm font-medium text-slate-700">Code
                    <input name="code" type="text" placeholder="SUB_123"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <label class="mt-3 block text-sm font-medium text-slate-700">Token
                    <input name="token" type="text" placeholder="email token"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button
                    class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Enable</button>
            </form>

            <form method="post" action="/paystack/demo/subscriptions"
                class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="disable">
                <label class="block text-sm font-medium text-slate-700">Code
                    <input name="code" type="text" placeholder="SUB_123"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <label class="mt-3 block text-sm font-medium text-slate-700">Token
                    <input name="token" type="text" placeholder="email token"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button
                    class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Disable</button>
            </form>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/subscriptions"
                class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="generate-link">
                <label class="block text-sm font-medium text-slate-700">Code
                    <input name="code" type="text" placeholder="SUB_123"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button
                    class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Generate
                    update link</button>
            </form>

            <form method="post" action="/paystack/demo/subscriptions"
                class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="send-link">
                <label class="block text-sm font-medium text-slate-700">Code
                    <input name="code" type="text" placeholder="SUB_123"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                <button
                    class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Email
                    update link</button>
            </form>
        </div>
    </section>
</x-paystack-demo.layout>