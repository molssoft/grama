<?php

namespace App\Models;

use Database\Factories\DimensionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'code', 'name'])]
class Dimension extends Model
{
    /** @use HasFactory<DimensionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return HasMany<DimensionValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(DimensionValue::class)->orderBy('code');
    }
}
