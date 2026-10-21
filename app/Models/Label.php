<?php

namespace App\Models;

use App\Traits\Syncable;
use Database\Factories\LabelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row of a calendar's "label your colours" legend. It names a colour on
 * that calendar and is not linked to any day.
 */
#[Fillable(['calendar_id', 'client_uuid', 'color', 'name'])]
class Label extends Model
{
    /** @use HasFactory<LabelFactory> */
    use HasFactory, Syncable;

    /**
     * @return BelongsTo<Calendar, $this>
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }
}
