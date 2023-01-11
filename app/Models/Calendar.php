<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'user_id', 'is_bullet_calendar'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function colorAssocitations()
    {
        return $this->hasMany(ColorAssociation::class);
    }

    public function dates()
    {
        return $this->hasMany(Date::class);
    }

    public function hasColor($color)
    {
        return $this->colors()
            ->where('color_id', $color->getKey())
            ->exists();
    }
}
