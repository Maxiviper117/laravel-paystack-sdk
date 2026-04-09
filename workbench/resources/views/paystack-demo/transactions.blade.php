@php
    $authorizationUrl = data_get($result ?? null, 'authorizationUrl');
    $callbackReference = $callbackReference ?? null;
    $verifiedReference = data_get($result ?? null, 'transaction.reference');
    $verifiedStatus = data_get($result ?? null, 'transaction.status');
    $callbackVerificationStatus = strtolower((string) $verifiedStatus);
    $isCallbackVerified = !empty($callbackReference) && $verifiedReference === $callbackReference && $callbackVerificationStatus === 'success';
    $isCallbackFailed = !empty($callbackReference) && $verifiedReference === $callbackReference && $callbackVerificationStatus !== '' && $callbackVerificationStatus !== 'success';
    $verificationNotice = $verificationNotice ?? null;
@endphp

<x-paystack-demo.layout title="Transactions Demo" heading="Transactions"
    description="Initialize a checkout or verify a returned reference." :pages="$pages" :result="$result ?? null"
    :result-label="$resultLabel ?? null" current-path="/paystack/demo/transactions">
    <section class="border border-slate-200 bg-white p-5">
        <div class="grid gap-4 md:grid-cols-2">
            <form method="post" action="/paystack/demo/transactions" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="initialize">
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-slate-700">Email
                        <input name="email" type="email" value="customer@example.com"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Amount
                        <input name="amount" type="number" step="0.01" value="15.50"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Channels
                        <input name="channels" type="text" value="card,bank_transfer" placeholder="card,bank_transfer"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Callback URL
                        <input name="callback_url" type="text" value="{{ url('/paystack/demo/transactions') }}"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Reference
                        <input name="reference" type="text" placeholder="ref_123"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Plan
                        <input name="plan" type="text" placeholder="PLN_123"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Invoice Limit
                        <input name="invoice_limit" type="number" step="1" min="0" placeholder="3"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Currency
                        <input name="currency" type="text" placeholder="NGN"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Split Code
                        <input name="split_code" type="text" placeholder="SPL_123"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Subaccount
                        <input name="subaccount" type="text" placeholder="ACCT_123"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Transaction Charge
                        <input name="transaction_charge" type="number" step="1" min="0" placeholder="250"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                    <label class="block text-sm font-medium text-slate-700">Bearer
                        <input name="bearer" type="text" placeholder="account"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                </div>
                <button
                    class="mt-4 w-full bg-orange-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-500">Initialize</button>
            </form>

            <form method="post" action="/paystack/demo/transactions" class="border border-slate-100 bg-slate-50/60 p-4">
                @csrf
                <input type="hidden" name="action" value="verify">
                <label class="block text-sm font-medium text-slate-700">Reference
                    <input name="reference" type="text" value="{{ $callbackReference ?? '' }}" placeholder="reference"
                        class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                </label>
                @if (!empty($callbackReference))
                    <p class="mt-2 text-xs font-semibold {{ $isCallbackVerified ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ $isCallbackVerified ? 'Loaded from the callback URL and confirmed in UI.' : 'Loaded from the callback URL and checking…' }}
                    </p>
                @endif
                <button
                    class="mt-4 w-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Verify</button>
                @if (!empty($verificationNotice))
                    <div
                        class="mt-4 border {{ $verificationNotice['tone'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }} p-3">
                        <p class="text-[11px] font-semibold uppercase tracking-widest">{{ $verificationNotice['title'] }}
                        </p>
                        <p class="mt-2 text-sm leading-6">{{ $verificationNotice['message'] }}</p>
                    </div>
                @endif
            </form>
        </div>

        @if (!empty($callbackReference))
            <section class="mt-4 border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-emerald-700">Redirect
                            verification</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">The callback reference was read from the URL and
                            verified automatically.</p>
                    </div>
                    <span
                        class="inline-flex border border-emerald-200 bg-white px-3 py-1 text-xs font-semibold text-emerald-700">
                        {{ $isCallbackVerified ? 'Confirmed' : ($isCallbackFailed ? 'Failed' : 'Verifying') }}
                    </span>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    <div class="border border-emerald-200 bg-white p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Ref</p>
                        <p class="mt-2 break-all font-mono text-sm text-slate-900">{{ $callbackReference }}</p>
                        <p
                            class="mt-2 text-xs font-semibold {{ $isCallbackVerified ? 'text-emerald-700' : ($isCallbackFailed ? 'text-rose-700' : 'text-slate-500') }}">
                            {{ $isCallbackVerified ? 'Confirmed in UI' : ($isCallbackFailed ? 'Verification failed in UI' : 'Waiting for verification') }}
                        </p>
                    </div>

                    <div class="border border-emerald-200 bg-white p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Status</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $verifiedStatus ?? 'unknown' }}</p>
                        @if (!empty($verifiedReference))
                            <p class="mt-2 break-all text-xs text-slate-500">Verified ref: {{ $verifiedReference }}</p>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        @if (!empty($authorizationUrl))
            <section class="mt-4 border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Checkout URL</p>
                <div class="mt-3 flex flex-col gap-3">
                    <a href="{{ $authorizationUrl }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 border border-orange-400 bg-white px-4 py-2 text-sm font-semibold text-orange-700 hover:bg-orange-50">
                        Open authorization URL
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 10.5 21 3m0 0h-6.75M21 3v6.75M10.5 13.5 3 21m0 0h6.75M3 21v-6.75" />
                        </svg>
                    </a>
                    <p class="break-all text-xs leading-6 text-slate-500">{{ $authorizationUrl }}</p>
                </div>
            </section>
        @endif
    </section>
</x-paystack-demo.layout>