<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function colorAssociations()
    {
        return $this->hasMany(ColorAssociation::class);
    }

    public function calendarDates()
    {
        return $this->hasMany(CalendarDate::class);
    }

}
