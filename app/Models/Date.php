<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    use HasFactory;

    protected $fillable = [
        'info', 'date', 'calendar_id', 'color_id'
    ];

    public function color()
    {
        return $this->belongsTo(Color::class);
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
