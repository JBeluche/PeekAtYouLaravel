<?php

namespace App\Models;

use App\Enums\DayMarkStyle;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Display preferences, held per user rather than per calendar.
 */
#[Fillable(['user_id', 'note_font_size', 'note_font', 'day_mark_style'])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    /**
     * Mirror the schema defaults so a new model reads correctly before it is saved.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'note_font_size' => 14,
        'note_font' => 'sans',
        'day_mark_style' => 'stroke',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'note_font_size' => 'integer',
            'day_mark_style' => DayMarkStyle::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
