<x-paystack-demo.layout title="Paystack SDK Kitchen Sink" heading="Dashboard"
    description="Pick a feature from the sidebar to start testing." current-path="/paystack/demo" :pages="$pages">
    {{-- Feature grid --}}
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($pages as $page)
            <a href="{{ $page['path'] }}"
                class="group flex flex-col border border-slate-200 bg-white p-4 transition-colors hover:border-orange-300 hover:bg-orange-50/40">
                <span class="text-sm font-semibold text-slate-800 group-hover:text-orange-700">{{ $page['title'] }}</span>
                <span class="mt-1 text-xs leading-5 text-slate-500">{{ $page['description'] }}</span>
                <span
                    class="mt-3 text-xs font-medium text-orange-500 opacity-0 transition-opacity group-hover:opacity-100">Open
                    →</span>
            </a>
        @endforeach
    </section>

    {{-- Quick info cards --}}
    <section class="grid gap-3 sm:grid-cols-2">
        <div class="border border-slate-200 bg-white p-4">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">SDK Features</p>
            <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 bg-orange-400"></span> Initialize &amp;
                    verify transactions</li>
                <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 bg-orange-400"></span> Create, update &amp;
                    list customers</li>
                <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 bg-orange-400"></span> Create, update &amp;
                    list plans</li>
                <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 bg-orange-400"></span> Create, fetch,
                    enable &amp; disable subscriptions</li>
            </ul>
        </div>
        <div class="border border-slate-200 bg-white p-4">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Platform</p>
            <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 bg-slate-400"></span> Webhook intake via
                    spatie/laravel-webhook-client</li>
                <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 bg-slate-400"></span> Stored &amp;
                    processed webhook calls</li>
                <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 bg-slate-400"></span> Optional Billable
                    Eloquent trait</li>
                <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 bg-slate-400"></span> Local customer &amp;
                    subscription persistence</li>
            </ul>
        </div>
    </section>
</x-paystack-demo.layout>