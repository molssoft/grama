@extends('layouts.guest')

@section('title', __('auth.login.title'))

@section('content')
<div class="card">
    <h1>{{ __('auth.login.title') }}</h1>
    <p class="muted">{{ __('auth.login.subtitle') }}</p>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <label for="email">{{ __('auth.login.email') }}</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}"
               autocomplete="username" autofocus required
               @class(['invalid' => $errors->has('email')])>
        @error('email')<p class="error">{{ $message }}</p>@enderror

        <label for="password">{{ __('auth.login.password') }}</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required
               @class(['invalid' => $errors->has('password')])>
        @error('password')<p class="error">{{ $message }}</p>@enderror

        <label class="check">
            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
            {{ __('auth.login.remember') }}
        </label>

        <button type="submit">{{ __('auth.login.submit') }}</button>
    </form>
</div>
@endsection
