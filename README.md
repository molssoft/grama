# Grama

Open source accounting software for Latvia, built with Laravel and PostgreSQL.

Interface languages: Latvian (default), Russian, English.

## Requirements

- PHP 8.3+ with `pdo_pgsql`
- PostgreSQL 16+
- Composer

## Setup

```sh
composer install
cp .env.example .env
php artisan key:generate
# set DB_* values in .env, then:
php artisan migrate --seed
```

Set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env` before seeding; the seeder uses them to create the first admin user.

## Adding a language

Add a folder under `lang/` and register the code in `supported_locales` in `config/app.php`.

## License

Copyright (C) 2026 SIA Molssoft Latvija.


Grama is free software: you can redistribute it and/or modify it under the terms of the
GNU General Public License as published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version. See [LICENSE](LICENSE).

Grama is built on [Laravel](https://laravel.com), which is MIT licensed.
