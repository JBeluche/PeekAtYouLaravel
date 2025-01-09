<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColorAssociation extends Model
{
    /** @use HasFactory<\Database\Factories\ColorAssociationFactory> */
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'hex_value',
        'association_text',
    ];

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }
}
