<?php

namespace App\Models;

use App\Enums\EntrySymbol;
use Database\Factories\EntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single day's note on a calendar.
 */
#[Fillable(['calendar_id', 'date', 'text', 'pen_color', 'symbol', 'mark_color'])]
class Entry extends Model
{
    /** @use HasFactory<EntryFactory> */
    use HasFactory;

    /**
     * Mirror the schema defaults so a new model reads correctly before it is saved.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'symbol' => 'none',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'symbol' => EntrySymbol::class,
        ];
    }

    /**
     * @return BelongsTo<Calendar, $this>
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * The legend entry this day's mark colour refers to, if any.
     *
     * @return BelongsTo<Label, $this>
     */
    public function label(): BelongsTo
    {
        return $this->belongsTo(Label::class, 'mark_color', 'color');
    }
}
