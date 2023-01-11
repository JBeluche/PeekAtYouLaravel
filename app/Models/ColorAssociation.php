<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColorAssociation extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'color_id',
        'association_text',
    ];

    public function color()
    {
        return $this->belongsTo(Color::class);
    }
    
    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }
}
