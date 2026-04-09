@php
    $pageTitle = $title ?? 'Paystack Demo';
    $result = $result ?? null;
    $resultLabel = $resultLabel ?? null;
    $pages = $pages ?? [];
    $currentPath = $currentPath ?? null;
    $renderedResult = $result;

    if (is_object($renderedResult) && method_exists($renderedResult, 'toArray')) {
        $renderedResult = $renderedResult->toArray();
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-50 to-white text-slate-900">
    <main class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
        <header class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="mb-3 inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-slate-600">Workbench demo</p>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-5xl">{{ $heading ?? 'Paystack demo page' }}</h1>
                    @if (! empty($description))
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">{{ $description }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="/paystack/demo" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 hover:bg-slate-100">Demo hub</a>
                </div>
            </div>
        </header>

        @if (! empty($pages))
            <nav class="grid gap-4 lg:grid-cols-3">
                @foreach ($pages as $page)
                    @php $isActive = $currentPath === $page['path']; @endphp
                    <a href="{{ $page['path'] }}" class="rounded-2xl border px-4 py-3 transition {{ $isActive ? 'border-orange-300 bg-orange-50 text-orange-700' : 'border-slate-200 bg-white text-slate-700 hover:border-orange-300 hover:bg-slate-50' }}">
                        <div class="text-sm font-semibold">{{ $page['title'] }}</div>
                        <div class="mt-1 text-xs leading-5 text-slate-500">{{ $page['description'] }}</div>
                    </a>
                @endforeach
            </nav>
        @endif

        {{ $slot }}

        <section class="rounded-3xl border border-slate-200 bg-white p-6">
            @if (! is_null($resultLabel) || ! is_null($result))
                <h2 class="text-2xl font-bold text-slate-900">{{ $resultLabel ?? 'Latest result' }}</h2>
                <pre class="mt-4 overflow-auto rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs leading-6 text-slate-800">{{ json_encode($renderedResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <h2 class="text-2xl font-bold text-slate-900">No result yet</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Submit one of the forms above to inspect the typed response payload.</p>
            @endif
        </section>
    </main>
</body>
</html>
