<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'base_currency_id', 'date_format', 'datetime_format'])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    public const DEFAULT_DATE_FORMAT = 'd.m.Y';

    public const DEFAULT_DATETIME_FORMAT = 'd.m.Y H:i';

    /**
     * Selectable date formats (PHP date() syntax).
     *
     * @var list<string>
     */
    public const DATE_FORMATS = ['d.m.Y', 'd/m/Y', 'Y-m-d', 'm/d/Y'];

    /**
     * Selectable date and time formats (PHP date() syntax).
     *
     * @var list<string>
     */
    public const DATETIME_FORMATS = ['d.m.Y H:i', 'd/m/Y H:i', 'Y-m-d H:i', 'm/d/Y H:i', 'm/d/Y h:i A'];

    /**
     * @var array<string, string>
     */
    protected $attributes = [
        'date_format' => self::DEFAULT_DATE_FORMAT,
        'datetime_format' => self::DEFAULT_DATETIME_FORMAT,
    ];

    protected static function booted(): void
    {
        static::created(function (Company $company): void {
            // A plain insert: it must not pick up the active panel tenant, which may be another company.
            Role::query()->insert([
                'company_id' => $company->getKey(),
                'name' => Role::ADMINISTRATOR_NAME,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Account::createDefaultChart($company);
        });

        static::creating(function (Company $company): void {
            if (blank($company->slug)) {
                $company->slug = static::generateUniqueSlug($company->name);
            }
        });
    }

    /**
     * Builds a URL-safe slug from the name, suffixed with a number when taken.
     */
    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'company';
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    /**
     * @return HasMany<Role, $this>
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function administratorRole(): ?Role
    {
        return Role::withoutGlobalScopes()->whereBelongsTo($this)->where('name', Role::ADMINISTRATOR_NAME)->first();
    }

    /**
     * @return HasMany<Account, $this>
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role_id')->withTimestamps();
    }
}
