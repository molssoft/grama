<?php

namespace App\Models;

use App\Enums\AccountType;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

#[Fillable(['company_id', 'parent_id', 'code', 'name', 'type', 'is_postable', 'is_active'])]
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    /**
     * The default Latvian chart of accounts given to every new company: [code, name, type].
     *
     * Classes (1 digit) and groups (2 digits) are the parents of the longer accounts that start with
     * their code and are headings that cannot be posted to. Names are Latvian regardless of the interface language, like the administrator role.
     * Indicative only: an accountant should adapt it to the company's activity.
     *
     * @var list<array{0: string, 1: string, 2: string}>
     */
    public const DEFAULT_CHART = [
        ['1', 'Ilgtermiņa ieguldījumi', 'asset'],
        ['11', 'Nemateriālie ieguldījumi', 'asset'],
        ['1110', 'Nemateriālie ieguldījumi', 'asset'],
        ['1190', 'Nemateriālo ieguldījumu amortizācija', 'asset'],
        ['12', 'Pamatlīdzekļi', 'asset'],
        ['1210', 'Zemes gabali, ēkas un būves', 'asset'],
        ['1220', 'Iekārtas un mašīnas', 'asset'],
        ['1230', 'Pārējie pamatlīdzekļi', 'asset'],
        ['1290', 'Pamatlīdzekļu nolietojums', 'asset'],
        ['2', 'Apgrozāmie līdzekļi', 'asset'],
        ['21', 'Krājumi', 'asset'],
        ['2110', 'Izejvielas un materiāli', 'asset'],
        ['2130', 'Gatavie ražojumi', 'asset'],
        ['2140', 'Preces pārdošanai', 'asset'],
        ['23', 'Debitori', 'asset'],
        ['2310', 'Pircēju un pasūtītāju parādi', 'asset'],
        ['2350', 'Citi debitori', 'asset'],
        ['26', 'Nauda', 'asset'],
        ['2610', 'Kase', 'asset'],
        ['2620', 'Norēķinu konti bankā', 'asset'],
        ['3', 'Pašu kapitāls', 'equity'],
        ['31', 'Pamatkapitāls', 'equity'],
        ['3110', 'Apmaksātais pamatkapitāls', 'equity'],
        ['34', 'Nesadalītā peļņa', 'equity'],
        ['3410', 'Iepriekšējo gadu nesadalītā peļņa', 'equity'],
        ['3420', 'Pārskata gada peļņa vai zaudējumi', 'equity'],
        ['4', 'Uzkrājumi', 'liability'],
        ['41', 'Uzkrājumi', 'liability'],
        ['4110', 'Uzkrājumi neizmantotajiem atvaļinājumiem', 'liability'],
        ['4190', 'Citi uzkrājumi', 'liability'],
        ['5', 'Kreditori', 'liability'],
        ['52', 'Piegādātāji', 'liability'],
        ['5210', 'Piegādātāju un darbuzņēmēju parādi', 'liability'],
        ['53', 'Darba samaksa', 'liability'],
        ['5310', 'Norēķini par darba samaksu', 'liability'],
        ['55', 'Aizņēmumi', 'liability'],
        ['5510', 'Īstermiņa aizņēmumi', 'liability'],
        ['57', 'Nodokļi', 'liability'],
        ['5710', 'Pievienotās vērtības nodoklis', 'liability'],
        ['5720', 'Uzņēmumu ienākuma nodoklis', 'liability'],
        ['5730', 'Iedzīvotāju ienākuma nodoklis', 'liability'],
        ['5740', 'Valsts sociālās apdrošināšanas obligātās iemaksas', 'liability'],
        ['6', 'Ieņēmumi', 'revenue'],
        ['61', 'Neto apgrozījums', 'revenue'],
        ['6110', 'Ieņēmumi no preču pārdošanas', 'revenue'],
        ['6120', 'Ieņēmumi no pakalpojumu sniegšanas', 'revenue'],
        ['64', 'Citi ieņēmumi', 'revenue'],
        ['6410', 'Citi saimnieciskās darbības ieņēmumi', 'revenue'],
        ['65', 'Finanšu ieņēmumi', 'revenue'],
        ['6510', 'Procentu ieņēmumi', 'revenue'],
        ['7', 'Izdevumi', 'expense'],
        ['71', 'Pārdošanas izmaksas', 'expense'],
        ['7110', 'Pārdoto preču iegādes izmaksas', 'expense'],
        ['72', 'Administrācijas izmaksas', 'expense'],
        ['7210', 'Darba samaksa', 'expense'],
        ['7220', 'Valsts sociālās apdrošināšanas obligātās iemaksas', 'expense'],
        ['7230', 'Telpu noma', 'expense'],
        ['7240', 'Biroja izdevumi', 'expense'],
        ['7250', 'Pamatlīdzekļu nolietojums un nemateriālo ieguldījumu amortizācija', 'expense'],
        ['7290', 'Citas administrācijas izmaksas', 'expense'],
        ['75', 'Finanšu izmaksas', 'expense'],
        ['7510', 'Procentu maksājumi', 'expense'],
        ['77', 'Uzņēmumu ienākuma nodoklis', 'expense'],
        ['7710', 'Uzņēmumu ienākuma nodoklis', 'expense'],
    ];

    protected static function booted(): void
    {
        static::saving(function (Account $account): void {
            if ($account->parent_id === null) {
                return;
            }

            if ($account->exists && $account->parent_id === $account->getKey()) {
                throw new InvalidArgumentException('An account cannot be its own parent.');
            }

            $parent = static::withoutGlobalScopes()->find($account->parent_id);

            if ($parent === null || $parent->type !== $account->type) {
                throw new InvalidArgumentException('An account must have the same type as its parent account.');
            }

            if ($parent->is_postable) {
                throw new InvalidArgumentException('A parent account must not allow posting.');
            }
        });

        static::saving(function (Account $account): void {
            if ($account->is_postable && $account->exists && $account->children()->exists()) {
                throw new InvalidArgumentException('An account that has child accounts cannot allow posting.');
            }
        });
    }

    /**
     * Creates the default chart of accounts for a company.
     */
    public static function createDefaultChart(Company $company): void
    {
        /** @var array<string, int> $ids */
        $ids = [];

        // Sorted by code, so a parent (shorter prefix) always comes before its children.
        $chart = self::DEFAULT_CHART;
        usort($chart, fn (array $a, array $b): int => strcmp($a[0], $b[0]));

        foreach ($chart as [$code, $name, $type]) {
            $parentCode = null;

            for ($length = strlen($code) - 1; $length > 0; $length--) {
                if (isset($ids[substr($code, 0, $length)])) {
                    $parentCode = substr($code, 0, $length);

                    break;
                }
            }

            $isHeading = collect(self::DEFAULT_CHART)->contains(fn (array $other): bool => $other[0] !== $code && str_starts_with($other[0], $code));

            // A plain insert: it must not pick up the active panel tenant, which may be another company.
            $ids[$code] = static::withoutGlobalScopes()->insertGetId([
                'company_id' => $company->getKey(),
                'parent_id' => $parentCode === null ? null : $ids[$parentCode],
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'is_postable' => ! $isHeading,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'is_postable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The code followed by the name, e.g. "2310 Settlements with suppliers".
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->code.' '.$this->name;
    }

    /**
     * Suggests an unused code for a new child account, following the numbering of the existing children.
     *
     * Only a suggestion: codes stay free-form, so the user can change it.
     */
    public function suggestChildCode(): string
    {
        $siblingCodes = static::withoutGlobalScopes()
            ->where('company_id', $this->company_id)
            ->where('parent_id', $this->getKey())
            ->pluck('code')
            ->all();

        if ($siblingCodes !== []) {
            usort($siblingCodes, 'strnatcmp');
            $candidate = self::incrementCode(end($siblingCodes));
        } else {
            $candidate = match (true) {
                strlen($this->code) < 2 => $this->code.'1',
                strlen($this->code) < 4 => str_pad($this->code.'1', 4, '0'),
                default => $this->code.'-001',
            };
        }

        while (static::withoutGlobalScopes()->where('company_id', $this->company_id)->where('code', $candidate)->exists()) {
            $candidate = self::incrementCode($candidate);
        }

        return $candidate;
    }

    /**
     * Raises the trailing number of a code, by 10 when it ends in 0 (2610 -> 2620), otherwise by 1 (2310-001 -> 2310-002).
     */
    private static function incrementCode(string $code): string
    {
        if (! preg_match('/^(.*?)(\d+)$/', $code, $matches)) {
            return $code.'1';
        }

        [, $prefix, $digits] = $matches;
        $step = str_ends_with($digits, '0') ? 10 : 1;

        return $prefix.str_pad((string) ((int) $digits + $step), strlen($digits), '0', STR_PAD_LEFT);
    }

    /**
     * Ids of every account below this one in the hierarchy, at any depth.
     *
     * @return list<int>
     */
    public function descendantIds(): array
    {
        $ids = [];
        $parentIds = [$this->getKey()];

        while ($parentIds !== []) {
            $parentIds = static::withoutGlobalScopes()->whereIn('parent_id', $parentIds)->pluck('id')->all();
            $ids = [...$ids, ...$parentIds];
        }

        return $ids;
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * @return HasMany<Account, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }
}
