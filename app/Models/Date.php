<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    use HasFactory;

    protected $fillable = [
        'displayed_note', 'long_note', 'date', 'calendar_id', 'bullet_value', 'color_association_id'
     ];

    public function colorAssociations()
    {
        return $this->belongsTo(ColorAssociationDate::class);
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

}
