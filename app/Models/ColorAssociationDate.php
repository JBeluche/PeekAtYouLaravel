<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColorAssociationDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_id', 'color_association_id', 'extra_value'
    ];

    public function colorAssociation()
    {
        return $this->belongsTo(ColorAssociation::class);
    }

    public function date()
    {
        return $this->belongsTo(Date::class);
    }

}
