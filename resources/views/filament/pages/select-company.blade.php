<x-filament-panels::page.simple>
    <div class="flex flex-col gap-2">
        @foreach ($this->getCompanies() as $company)
            <x-filament::button
                tag="a"
                :href="$this->getCompanyUrl($company)"
                color="gray"
                outlined
                class="w-full"
            >
                {{ $company->name }}
            </x-filament::button>
        @endforeach
    </div>
</x-filament-panels::page.simple>
