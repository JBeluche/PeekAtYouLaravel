<?php

namespace App\Models;

use App\Traits\Syncable;
use Database\Factories\CalendarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'owner_id', 'client_uuid'])]
class Calendar extends Model
{
    /** @use HasFactory<CalendarFactory> */
    use HasFactory, Syncable;

    protected static function booted(): void
    {
        // A soft delete skips the database cascade, so carry it to the children
        // here. Their updated_at moves too, so the deletes reach ?since= syncs.
        static::softDeleted(function (Calendar $calendar): void {
            $calendar->labels()->delete();
            $calendar->entries()->delete();
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The colour legend for this calendar.
     *
     * @return HasMany<Label, $this>
     */
    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    /**
     * @return HasMany<Entry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }
}
