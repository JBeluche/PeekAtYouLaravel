<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    use HasFactory;

    protected $fillable = [
        'displayed_note', 'long_note', 'date', 'calendar_id',
    ];

    public function colorAssociations()
    {
        return $this->hasMany(ColorAssociationDate::class);
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    public function dates()
    {
        return $this->hasMany(Date::class);
    }

}
