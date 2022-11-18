<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Palette extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'user_id'
    ];

    public function colors()
    {
        return $this->belongsToMany(Color::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    public function hasColor($color)
    {
        return $this->colors()
            ->where('color_id', $color->getKey())
            ->exists();
    }
}
