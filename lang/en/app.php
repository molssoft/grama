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

return [
    'tagline' => 'open source accounting software',
    'dashboard' => [
        'title' => 'Home',
        'welcome' => 'Hello, :name!',
        'coming_soon' => 'The Grama accounting system is coming soon.',
    ],
    'navigation' => [
        'currencies' => 'Currencies',
        'accounting' => 'Accounting',
        'settings' => 'Settings',
    ],
    'users' => [
        'email_help' => 'Enter the email of an existing user to add them to this company; their password stays unchanged.',
        'already_member' => 'This user is already a member of this company.',
        'temporary_password' => 'Temporary password',
        'temporary_password_help' => 'The user signs in with this email and password. Share it securely and ask them to change it.',
        'role' => 'Role',
        'remove_help' => 'Removes the user from this company. Their account is kept.',
        'singular' => 'User',
        'plural' => 'Users',
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'locale' => 'Language',
        'created_at' => 'Created',
        'email_verified_at' => 'Email verified',
    ],
    'companies' => [
        'date_format' => 'Date format',
        'datetime_format' => 'Date and time format',
        'singular' => 'Company',
        'plural' => 'Companies',
        'name' => 'Name',
        'slug' => 'Web address',
        'slug_help' => 'Used in the URL, e.g. /my-company. Letters, numbers, dashes.',
        'create' => 'Create company',
        'select' => 'Choose a company',
        'settings' => 'Company settings',
        'base_currency' => 'Base currency',
        'base_currency_help' => 'Currency used for the company\'s accounting records.',
    ],
    'roles' => [
        'singular' => 'Role',
        'plural' => 'Roles',
        'name' => 'Name',
        'permissions' => 'Permissions',
        'administrator_help' => 'The administrator role always has every permission and cannot be renamed or deleted.',
    ],
    'currencies' => [
        'singular' => 'Currency',
        'plural' => 'Currencies',
        'code' => 'Code',
        'name' => 'Name',
        'symbol' => 'Symbol',
        'decimal_places' => 'Decimal places',
        'latest_rate' => 'Rate against EUR',
        'latest_rate_help' => 'Latest global reference rate: units of the currency per 1 EUR.',
        'latest_rate_date' => 'Rate date',
        'is_active' => 'Active',
        'date_format' => 'd.m.Y',
    ],
    'exchange_rates' => [
        'singular' => 'Exchange rate',
        'plural' => 'Exchange rates',
        'currency' => 'Currency',
        'date' => 'Date',
        'rate' => 'Rate',
        'rate_help' => 'Units of the currency per 1 EUR.',
        'type' => 'Type',
        'global' => 'Global',
        'custom' => 'Custom',
        'source' => 'Source',
        'from' => 'From',
        'until' => 'Until',
        'today' => 'Today\'s rates',
        'set_custom' => 'Set custom rate',
        'custom_help' => 'Your company\'s own rate. It takes precedence over the global rate for the same date.',
    ],
    'accounts' => [
        'singular' => 'Account',
        'plural' => 'Chart of accounts',
        'code' => 'Code',
        'code_help' => 'Choosing a parent account suggests the next free code.',
        'name' => 'Name',
        'type' => 'Type',
        'parent' => 'Parent account',
        'type_follows_parent' => 'Follows the parent account.',
        'parent_help' => 'Groups this account under another one, e.g. a class or group of accounts.',
        'is_postable' => 'Allow posting',
        'is_postable_help' => 'Turn off for headings that only group other accounts. A parent account must not allow posting.',
        'is_postable_locked' => 'This account has child accounts, so it is a heading and cannot be posted to.',
        'is_active' => 'Active',
        'types' => [
            'asset' => 'Asset',
            'liability' => 'Liability',
            'equity' => 'Equity',
            'revenue' => 'Revenue',
            'expense' => 'Expense',
        ],
    ],
    'permissions' => [
        'edit_chart_of_accounts' => 'Edit chart of accounts',
        'edit_company_data' => 'Edit company data',
        'edit_exchange_rates' => 'Edit exchange rates',
        'edit_roles' => 'Edit roles',
        'edit_users' => 'Edit users',
    ],
    'language' => 'Language',
];
