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

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalDescription(__('app.users.remove_help'))
                ->using(fn (User $record): bool => (bool) $record->companies()->detach(Filament::getTenant()))
                ->hidden(fn (): bool => $this->getRecord()->is(auth()->user())),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role_id'] = $this->getRecord()->roleIn(Filament::getTenant())?->getKey();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->getRecord()->companies()->updateExistingPivot(Filament::getTenant(), [
            'role_id' => $this->form->getRawState()['role_id'],
        ]);
    }
}
