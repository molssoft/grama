<form method="POST" class="lang-switcher" aria-label="{{ __('app.language') }}">
    @csrf
    @foreach (config('app.supported_locales') as $code => $label)
        <button type="submit" formaction="{{ route('locale.update', $code) }}"
                @class(['active' => app()->getLocale() === $code])
                @if (app()->getLocale() === $code) aria-current="true" @endif
                lang="{{ $code }}">{{ $label }}</button>
    @endforeach
</form>
