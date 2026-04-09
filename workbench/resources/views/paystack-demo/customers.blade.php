<x-paystack-demo.layout title="Customers Demo" heading="Customers"
    description="Create, update, and list customer records." :pages="$pages" :result="$result ?? null"
    :result-label="$resultLabel ?? null" current-path="/paystack/demo/customers">
    <section class="border border-slate-200 bg-white p-5">
        <div class="grid gap-4">
            <form method="post" action="/paystack/demo/customers" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="create">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Create customer</p>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-700 md:col-span-2">Email
                        <input name="email" type="email" value="customer@example.com"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">First name
                        <input name="first_name" type="text" value="Jane"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Last name
                        <input name="last_name" type="text" value="Doe"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Phone
                        <input name="phone" type="text" value="+27123456789"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                </div>
                <button
                    class="mt-4 bg-orange-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-500">Create</button>
            </form>

            <div class="grid gap-4 md:grid-cols-2">
                <form method="post" action="/paystack/demo/customers"
                    class="border border-slate-100 bg-slate-50/60 p-4">
                    @csrf
                    <input type="hidden" name="action" value="fetch">
                    <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Fetch customer</p>
                    <label class="block text-sm font-medium text-slate-700">Customer identifier
                        <input name="customer_identifier" type="text" placeholder="CUS_123 or email@example.com"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <button
                        class="mt-4 w-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Fetch</button>
                </form>

                <form method="post" action="/paystack/demo/customers"
                    class="border border-slate-100 bg-slate-50/60 p-4">
                    @csrf
                    <input type="hidden" name="action" value="update">
                    <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Update customer</p>
                    <label class="block text-sm font-medium text-slate-700">Customer code
                        <input name="customer_code" type="text" placeholder="CUS_123"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <div class="mt-3 grid gap-3">
                        <label class="block text-sm font-medium text-slate-700">Email
                            <input name="email" type="email" placeholder="Optional"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">First name
                            <input name="first_name" type="text" placeholder="Optional"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Last name
                            <input name="last_name" type="text" placeholder="Optional"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                    </div>
                    <button
                        class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Update</button>
                </form>

                <form method="post" action="/paystack/demo/customers"
                    class="border border-slate-100 bg-slate-50/60 p-4">
                    @csrf
                    <input type="hidden" name="action" value="validate">
                    <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Validate customer</p>
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="block text-sm font-medium text-slate-700 md:col-span-2">Customer code
                            <input name="customer_code" type="text" placeholder="CUS_123"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Country
                            <input name="country" type="text" value="NG"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Type
                            <input name="type" type="text" value="bank_account"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">First name
                            <input name="first_name" type="text" value="Jane"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Last name
                            <input name="last_name" type="text" value="Doe"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">BVN
                            <input name="bvn" type="text" placeholder="200123456677"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Bank code
                            <input name="bank_code" type="text" placeholder="007"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                        <label class="block text-sm font-medium text-slate-700 md:col-span-2">Account number
                            <input name="account_number" type="text" placeholder="0123456789"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                    </div>
                    <button
                        class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Validate</button>
                </form>

                <form method="post" action="/paystack/demo/customers"
                    class="border border-slate-100 bg-slate-50/60 p-4">
                    @csrf
                    <input type="hidden" name="action" value="risk-action">
                    <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Set risk action</p>
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="block text-sm font-medium text-slate-700 md:col-span-2">Customer
                            <input name="customer" type="text" placeholder="CUS_123"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                        <label class="block text-sm font-medium text-slate-700 md:col-span-2">Risk action
                            <select name="risk_action"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-orange-400 focus:outline-none">
                                <option value="">Optional</option>
                                @foreach ($customerRiskActions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('risk_action', 'default') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <button
                        class="mt-4 w-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Set
                        risk action</button>
                </form>

                <form method="post" action="/paystack/demo/customers"
                    class="border border-slate-100 bg-slate-50/60 p-4">
                    @csrf
                    <input type="hidden" name="action" value="list">
                    <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">List customers</p>
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="block text-sm font-medium text-slate-700">Per page
                            <input name="per_page" type="number" value="10"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Page
                            <input name="page" type="number" value="1"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                        <label class="block text-sm font-medium text-slate-700 md:col-span-2">Email filter
                            <input name="list_email" type="email" placeholder="Optional"
                                class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                        </label>
                    </div>
                    <button
                        class="mt-4 border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">List</button>
                </form>
            </div>
        </div>
    </section>
</x-paystack-demo.layout>
