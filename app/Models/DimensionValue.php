<?php

namespace App\Models;

use Database\Factories\DimensionValueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['dimension_id', 'code', 'name'])]
class DimensionValue extends Model
{
    /** @use HasFactory<DimensionValueFactory> */
    use HasFactory;

    /**
     * The code followed by the name, e.g. "SALES Sales department".
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->code.' '.$this->name;
    }

    /**
     * Whether any ledger line is tagged with this value.
     */
    public function isUsed(): bool
    {
        return GeneralLedgerLine::query()->whereHas('dimensionValues', fn ($query) => $query->whereKey($this->getKey()))->exists();
    }

    /**
     * @return BelongsTo<Dimension, $this>
     */
    public function dimension(): BelongsTo
    {
        return $this->belongsTo(Dimension::class);
    }
}
