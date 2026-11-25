<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/grama.css') }}">
</head>
<body class="guest">
    <main class="guest-wrap">
        <a class="brand" href="/"><span class="brand-mark">G</span> Grama</a>
        @yield('content')
        <x-language-switcher />
        <p class="foot">Grama — {{ __('app.tagline') }}</p>
    </main>
</body>
</html>
