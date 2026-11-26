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

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Role;
use App\Models\User;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('app.users.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('app.users.email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->helperText(fn (string $operation): ?string => $operation === 'create' ? __('app.users.email_help') : null)
                    ->rules(fn (string $operation, ?User $record): array => $operation === 'create'
                        ? [static::notYetInCurrentCompany(...)]
                        : [Rule::unique(User::class, 'email')->ignore($record)]),
                TextInput::make('password')
                    ->label(fn (string $operation): string => $operation === 'create' ? __('app.users.temporary_password') : __('app.users.password'))
                    ->helperText(fn (string $operation): ?string => $operation === 'create' ? __('app.users.temporary_password_help') : null)
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation, Get $get): bool => $operation === 'create' && ! User::where('email', $get('email'))->exists())
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->minLength(8),
                Select::make('role_id')
                    ->label(__('app.users.role'))
                    ->options(fn (): array => Role::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->required()
                    ->dehydrated(false),
                Select::make('locale')
                    ->label(__('app.users.locale'))
                    ->options(config('app.supported_locales')),
            ]);
    }

    /**
     * An email that belongs to an existing user is allowed: that user is added to the company instead.
     */
    public static function notYetInCurrentCompany(string $attribute, mixed $value, Closure $fail): void
    {
        $isMember = User::where('email', $value)
            ->whereHas('companies', fn (Builder $query): Builder => $query->whereKey(Filament::getTenant()?->getKey()))
            ->exists();

        if ($isMember) {
            $fail(__('app.users.already_member'));
        }
    }
}
