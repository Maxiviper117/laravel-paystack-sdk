<x-paystack-demo.layout title="Billing Sync Demo" heading="Billing sync"
    description="Search Paystack records first, then sync a chosen record locally." :pages="$pages"
    :result="$result ?? null" :result-label="$resultLabel ?? null" current-path="/paystack/demo/billing-sync">
    @php
        $resource = $resource ?? 'customers';
        $resourceOptions = $resourceOptions ?? [];
        $search = $search ?? [
            'resource' => $resource,
            'title' => 'Customers',
            'description' => 'Search remote customers and sync the one you want locally.',
            'fields' => [],
            'items' => [],
            'meta' => null,
            'searched' => false,
        ];
        $snapshot = $snapshot ?? [];
    @endphp

    <section class="border border-slate-200 bg-white p-5">
        <div class="rounded border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            This page only syncs Paystack data into the local mirror. Pick a resource, search Paystack, then press Sync on the record you want to store locally.
            Customers are mirrored one row per billable model; subscriptions are mirrored one row per Paystack `subscription_code`.
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($resourceOptions as $option)
                <a href="/paystack/demo/billing-sync?resource={{ $option['value'] }}"
                    class="inline-flex items-center gap-2 border px-3 py-2 text-sm font-medium transition-colors {{ $resource === $option['value'] ? 'border-orange-300 bg-orange-50 text-orange-900' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                    <span>{{ $option['label'] }}</span>
                </a>
            @endforeach
        </div>

        <form method="get" action="/paystack/demo/billing-sync" class="mt-5 border border-slate-100 bg-slate-50/60 p-4">
            <input type="hidden" name="resource" value="{{ $search['resource'] ?? $resource }}">
            <input type="hidden" name="search" value="1">
            <p class="mb-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">{{ $search['title'] ?? 'Search' }}</p>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($search['fields'] as $field)
                    <label class="block text-sm font-medium text-slate-700 {{ $field['name'] === 'plan_identifier' ? 'md:col-span-2 xl:col-span-3' : (count($search['fields']) > 4 && $loop->last ? 'xl:col-span-2' : '') }}">
                        {{ $field['label'] }}
                        <input name="{{ $field['name'] }}" type="{{ $field['type'] }}"
                            value="{{ $field['value'] }}"
                            placeholder="{{ $field['placeholder'] ?? '' }}"
                            class="mt-1 w-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-orange-400 focus:outline-none">
                    </label>
                @endforeach
            </div>

            <button class="mt-4 bg-orange-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-500">
                Search {{ $search['title'] ?? 'records' }}
            </button>
        </form>

        @if (($search['searched'] ?? false) && !empty($search['items']))
            <div class="mt-4 grid gap-4">
                @foreach ($search['items'] as $item)
                    <article class="border border-slate-100 bg-slate-50/60 p-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900">{{ $item['label'] }}</p>
                                <p class="mt-1 font-mono text-xs text-slate-500">{{ $item['identifier'] }}</p>
                            </div>

                            <form method="post" action="/paystack/demo/billing-sync" class="shrink-0">
                                @csrf
                                <input type="hidden" name="action" value="{{ $item['sync_action'] }}">
                                <input type="hidden" name="resource" value="{{ $search['resource'] }}">
                                <input type="hidden" name="identifier" value="{{ $item['identifier'] }}">
                                <input type="hidden" name="search" value="1">
                                @foreach ($search['fields'] as $field)
                                    <input type="hidden" name="{{ $field['name'] }}" value="{{ $field['value'] }}">
                                @endforeach
                                <button class="border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    Sync
                                </button>
                            </form>
                        </div>

                        <dl class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($item['summary'] as $label => $value)
                                <div class="rounded border border-slate-200 bg-white px-3 py-2">
                                    <dt class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">{{ $label }}</dt>
                                    <dd class="mt-1 break-words text-sm text-slate-700">
                                        {{ is_bool($value) ? ($value ? 'true' : 'false') : ($value ?? '—') }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </article>
                @endforeach
            </div>
        @elseif (($search['searched'] ?? false))
            <div class="mt-4 border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                No remote {{ strtolower($search['title'] ?? 'records') }} matched the current search.
            </div>
        @else
            <div class="mt-4 border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                Use the search form above to query Paystack and choose a record to sync locally.
            </div>
        @endif
    </section>

    <section class="border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Local mirror snapshot</p>
                <p class="mt-1 text-sm text-slate-500">Current local records after the last sync action.</p>
            </div>
        </div>

        @if (!empty($snapshot))
            <pre class="mt-3 overflow-auto border-l-2 border-orange-400 bg-slate-50 px-4 py-3 font-mono text-xs leading-6 text-slate-700">{{ json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        @else
            <p class="mt-2 text-sm text-slate-400">Run a sync to see the mirrored local records.</p>
        @endif
    </section>
</x-paystack-demo.layout>
