<?php

/**
 * Grama - open source accounting software.
 * Copyright (C) 2026 SIA Molssoft Latvija
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

namespace App\Filament\Resources\CurrencyRates\Pages;

use App\Filament\Resources\CurrencyRates\CurrencyRateResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ManageRecords;

class ManageCurrencyRates extends ManageRecords
{
    protected static string $resource = CurrencyRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(fn (array $data): array => [
                    ...$data,
                    'company_id' => Filament::getTenant()?->getKey(),
                    'source' => 'manual',
                ]),
        ];
    }

    public function toggleTodayRates(): void
    {
        $this->tableFilters['today']['isActive'] = ! ($this->tableFilters['today']['isActive'] ?? false);
        $this->resetPage();
    }
}
