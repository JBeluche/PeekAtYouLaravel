<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'displayed_note', 'long_note', 'date', 'calendar_id', 'color_association_id'
     ];

    public function colorAssociation()
    {
        return $this->belongsTo(ColorAssociation::class);
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

}
