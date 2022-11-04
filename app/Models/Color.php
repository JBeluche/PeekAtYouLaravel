<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $fillable = [
        'hex_value'
    ];

    public function calendars()
    {
        return $this->belongsToMany(Calendar::class);
    }
    public function palettes()
    {
        return $this->belongsToMany(Palette::class);
    }
}
