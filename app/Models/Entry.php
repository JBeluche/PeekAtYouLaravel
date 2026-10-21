<?php

namespace App\Models;

use App\Enums\EntrySymbol;
use App\Traits\Syncable;
use Carbon\CarbonImmutable;
use Database\Factories\EntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single day's note on a calendar. There is at most one per calendar and day.
 */
#[Fillable(['calendar_id', 'date', 'text', 'pen_color', 'symbol', 'mark_color'])]
class Entry extends Model
{
    /** @use HasFactory<EntryFactory> */
    use HasFactory, Syncable;

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
            'symbol' => EntrySymbol::class,
        ];
    }

    /**
     * Stored as a bare Y-m-d on every driver, so a lookup by day matches exactly.
     * The built-in date cast would store "Y-m-d 00:00:00" on SQLite.
     *
     * @return Attribute<CarbonImmutable|null, mixed>
     */
    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : CarbonImmutable::parse($value),
            set: fn (mixed $value) => CarbonImmutable::parse($value)->toDateString(),
        );
    }

    /**
     * Limit to entries dated within a year, or within one month of it.
     *
     * A half-open range rather than whereYear()/whereMonth() so the
     * (calendar_id, date) index stays usable.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function datedIn(Builder $query, int $year, ?int $month = null): void
    {
        $start = CarbonImmutable::create($year, $month ?? 1, 1);
        $end = $month === null ? $start->addYear() : $start->addMonth();

        $query->where('date', '>=', $start->toDateString())
            ->where('date', '<', $end->toDateString());
    }

    /**
     * @return BelongsTo<Calendar, $this>
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }
}
