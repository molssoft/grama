<form method="POST" style="display:flex;gap:.25rem;margin-inline-end:.75rem" aria-label="{{ __('app.language') }}">
    @csrf
    @foreach (config('app.supported_locales') as $code => $label)
        <button type="submit" style="padding:.25rem .5rem;font-size:.75rem;border-radius:.375rem;{{ app()->getLocale() === $code ? 'font-weight:700;background:rgba(128,128,128,.2)' : '' }}" formaction="{{ route('locale.update', $code) }}"
                @class(['active' => app()->getLocale() === $code])
                @if (app()->getLocale() === $code) aria-current="true" @endif
                lang="{{ $code }}">{{ $label }}</button>
    @endforeach
</form>
