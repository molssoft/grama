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
    'tagline' => 'atvērtā koda grāmatvedības programmatūra',
    'dashboard' => [
        'title' => 'Sākums',
        'welcome' => 'Sveiki, :name!',
        'coming_soon' => 'Grama grāmatvedības sistēma drīzumā.',
    ],
    'navigation' => [
        'currencies' => 'Valūtas',
        'accounting' => 'Grāmatvedība',
        'settings' => 'Iestatījumi',
    ],
    'users' => [
        'email_help' => 'Ievadiet esoša lietotāja e-pastu, lai pievienotu viņu šim uzņēmumam; viņa parole netiks mainīta.',
        'already_member' => 'Šis lietotājs jau ir šī uzņēmuma dalībnieks.',
        'temporary_password' => 'Pagaidu parole',
        'temporary_password_help' => 'Lietotājs piesakās ar šo e-pastu un paroli. Nosūtiet to droši un lūdziet to nomainīt.',
        'role' => 'Loma',
        'remove_help' => 'Lietotājs tiks noņemts no šī uzņēmuma. Viņa konts tiks saglabāts.',
        'singular' => 'Lietotājs',
        'plural' => 'Lietotāji',
        'name' => 'Vārds',
        'email' => 'E-pasts',
        'password' => 'Parole',
        'locale' => 'Valoda',
        'created_at' => 'Izveidots',
        'email_verified_at' => 'E-pasts apstiprināts',
    ],
    'companies' => [
        'date_format' => 'Datuma formāts',
        'datetime_format' => 'Datuma un laika formāts',
        'singular' => 'Uzņēmums',
        'plural' => 'Uzņēmumi',
        'name' => 'Nosaukums',
        'slug' => 'Tīmekļa adrese',
        'slug_help' => 'Tiek izmantota URL, piem., /mans-uznemums. Burti, cipari, domuzīmes.',
        'create' => 'Izveidot uzņēmumu',
        'select' => 'Izvēlieties uzņēmumu',
        'settings' => 'Uzņēmuma iestatījumi',
        'base_currency' => 'Bāzes valūta',
        'base_currency_help' => 'Valūta, kurā tiek veikta uzņēmuma grāmatvedība.',
    ],
    'roles' => [
        'singular' => 'Loma',
        'plural' => 'Lomas',
        'name' => 'Nosaukums',
        'permissions' => 'Atļaujas',
        'administrator_help' => 'Administratoru loma vienmēr ir ar visām atļaujām, un to nevar pārdēvēt vai dzēst.',
    ],
    'currencies' => [
        'singular' => 'Valūta',
        'plural' => 'Valūtas',
        'code' => 'Kods',
        'name' => 'Nosaukums',
        'symbol' => 'Simbols',
        'decimal_places' => 'Decimālzīmes',
        'latest_rate' => 'Kurss pret EUR',
        'latest_rate_help' => 'Jaunākais vispārējais atsauces kurss: valūtas vienības par 1 EUR.',
        'latest_rate_date' => 'Kursa datums',
        'is_active' => 'Aktīva',
        'date_format' => 'd.m.Y',
    ],
    'exchange_rates' => [
        'singular' => 'Valūtas kurss',
        'plural' => 'Valūtu kursi',
        'currency' => 'Valūta',
        'date' => 'Datums',
        'rate' => 'Kurss',
        'rate_help' => 'Valūtas vienības par 1 EUR.',
        'type' => 'Veids',
        'global' => 'Vispārējais',
        'custom' => 'Pielāgotais',
        'source' => 'Avots',
        'from' => 'No',
        'until' => 'Līdz',
        'set_custom' => 'Iestatīt pielāgoto kursu',
        'custom_help' => 'Jūsu uzņēmuma kurss. Tam ir prioritāte pār vispārējo kursu tajā pašā datumā.',
    ],
    'accounts' => [
        'singular' => 'Konts',
        'plural' => 'Kontu plāns',
        'code' => 'Kods',
        'code_help' => 'Izvēloties augstāko kontu, tiek ieteikts nākamais brīvais kods.',
        'name' => 'Nosaukums',
        'type' => 'Veids',
        'parent' => 'Augstākais konts',
        'type_follows_parent' => 'Atbilst augstākajam kontam.',
        'parent_help' => 'Apvieno šo kontu zem cita konta, piem., kontu klases vai grupas.',
        'is_postable' => 'Atļaut grāmatojumus',
        'is_postable_help' => 'Izslēdziet kontiem, kas tikai apvieno citus kontus. Augstākajam kontam grāmatojumi nav atļauti.',
        'is_postable_locked' => 'Šim kontam ir apakškonti, tāpēc tas ir virsraksts, un tajā nevar grāmatot.',
        'is_active' => 'Aktīvs',
        'types' => [
            'asset' => 'Aktīvi',
            'liability' => 'Saistības',
            'equity' => 'Pašu kapitāls',
            'revenue' => 'Ieņēmumi',
            'expense' => 'Izdevumi',
        ],
    ],
    'permissions' => [
        'edit_chart_of_accounts' => 'Rediģēt kontu plānu',
        'edit_company_data' => 'Rediģēt uzņēmuma datus',
        'edit_exchange_rates' => 'Rediģēt valūtu kursus',
        'edit_roles' => 'Rediģēt lomas',
        'edit_users' => 'Rediģēt lietotājus',
    ],
    'language' => 'Valoda',
];
