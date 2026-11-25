<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.dashboard.title') }} · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/grama.css') }}">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="/"><span class="brand-mark">G</span> Grama</a>
        <div class="topbar-actions">
            <x-language-switcher />
            <span class="muted">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="link">{{ __('auth.logout') }}</button>
            </form>
        </div>
    </header>
    <main class="page">
        <h1>{{ __('app.dashboard.welcome', ['name' => auth()->user()->name]) }}</h1>
        <p class="muted">{{ __('app.dashboard.coming_soon') }}</p>
    </main>
</body>
</html>
