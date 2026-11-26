<x-filament::button
    color="{{ ($this->tableFilters['today']['isActive'] ?? false) ? 'primary' : 'gray' }}"
    icon="heroicon-o-calendar-days"
    size="sm"
    outlined
    wire:click="toggleTodayRates"
>
    {{ __('app.exchange_rates.today') }}
</x-filament::button>
