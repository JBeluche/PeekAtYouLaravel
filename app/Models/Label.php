<?php

namespace App\Models;

use Database\Factories\LabelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One entry in a calendar's "label your colours" legend.
 */
#[Fillable(['calendar_id', 'color', 'name'])]
class Label extends Model
{
    /** @use HasFactory<LabelFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Calendar, $this>
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }
}
