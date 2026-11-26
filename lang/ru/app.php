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
    'tagline' => 'бухгалтерская программа с открытым исходным кодом',
    'dashboard' => [
        'title' => 'Главная',
        'welcome' => 'Здравствуйте, :name!',
        'coming_soon' => 'Бухгалтерская система Grama скоро будет доступна.',
    ],
    'navigation' => [
        'currencies' => 'Валюты',
        'accounting' => 'Бухгалтерия',
        'settings' => 'Настройки',
    ],
    'users' => [
        'email_help' => 'Укажите e-mail существующего пользователя, чтобы добавить его в эту компанию; его пароль не изменится.',
        'already_member' => 'Этот пользователь уже состоит в этой компании.',
        'temporary_password' => 'Временный пароль',
        'temporary_password_help' => 'Пользователь входит с этим адресом e-mail и паролем. Передайте его безопасно и попросите сменить.',
        'role' => 'Роль',
        'remove_help' => 'Пользователь будет удалён из этой компании. Его учётная запись сохранится.',
        'singular' => 'Пользователь',
        'plural' => 'Пользователи',
        'name' => 'Имя',
        'email' => 'Эл. почта',
        'password' => 'Пароль',
        'locale' => 'Язык',
        'created_at' => 'Создан',
        'email_verified_at' => 'Эл. почта подтверждена',
    ],
    'companies' => [
        'date_format' => 'Формат даты',
        'datetime_format' => 'Формат даты и времени',
        'singular' => 'Компания',
        'plural' => 'Компании',
        'name' => 'Название',
        'slug' => 'Веб-адрес',
        'slug_help' => 'Используется в URL, напр., /moya-kompaniya. Буквы, цифры, дефисы.',
        'create' => 'Создать компанию',
        'select' => 'Выберите компанию',
        'settings' => 'Настройки компании',
        'base_currency' => 'Базовая валюта',
        'base_currency_help' => 'Валюта, в которой ведётся учёт компании.',
    ],
    'roles' => [
        'singular' => 'Роль',
        'plural' => 'Роли',
        'name' => 'Название',
        'permissions' => 'Разрешения',
        'administrator_help' => 'Роль администратора всегда имеет все разрешения, её нельзя переименовать или удалить.',
    ],
    'currencies' => [
        'singular' => 'Валюта',
        'plural' => 'Валюты',
        'code' => 'Код',
        'name' => 'Название',
        'symbol' => 'Символ',
        'decimal_places' => 'Знаков после запятой',
        'latest_rate' => 'Курс к EUR',
        'latest_rate_help' => 'Последний общий справочный курс: единиц валюты за 1 EUR.',
        'latest_rate_date' => 'Дата курса',
        'is_active' => 'Активна',
        'date_format' => 'd.m.Y',
    ],
    'exchange_rates' => [
        'singular' => 'Курс валюты',
        'plural' => 'Курсы валют',
        'currency' => 'Валюта',
        'date' => 'Дата',
        'rate' => 'Курс',
        'rate_help' => 'Единиц валюты за 1 EUR.',
        'type' => 'Тип',
        'global' => 'Общий',
        'custom' => 'Собственный',
        'source' => 'Источник',
        'from' => 'С',
        'until' => 'По',
        'set_custom' => 'Задать собственный курс',
        'custom_help' => 'Собственный курс вашей компании. Имеет приоритет над общим курсом на ту же дату.',
    ],
    'accounts' => [
        'singular' => 'Счёт',
        'plural' => 'План счетов',
        'code' => 'Код',
        'code_help' => 'При выборе родительского счёта предлагается следующий свободный код.',
        'name' => 'Название',
        'type' => 'Тип',
        'parent' => 'Родительский счёт',
        'type_follows_parent' => 'Соответствует родительскому счёту.',
        'parent_help' => 'Объединяет этот счёт под другим, напр., классом или группой счетов.',
        'is_postable' => 'Разрешить проводки',
        'is_postable_help' => 'Отключите для счетов, которые только объединяют другие. У родительского счёта проводки не допускаются.',
        'is_postable_locked' => 'У этого счёта есть подчинённые счета, поэтому он является заголовком, и проводки по нему невозможны.',
        'is_active' => 'Активен',
        'types' => [
            'asset' => 'Активы',
            'liability' => 'Обязательства',
            'equity' => 'Капитал',
            'revenue' => 'Доходы',
            'expense' => 'Расходы',
        ],
    ],
    'permissions' => [
        'edit_chart_of_accounts' => 'Редактировать план счетов',
        'edit_company_data' => 'Редактировать данные компании',
        'edit_exchange_rates' => 'Редактировать курсы валют',
        'edit_roles' => 'Редактировать роли',
        'edit_users' => 'Редактировать пользователей',
    ],
    'language' => 'Язык',
];
