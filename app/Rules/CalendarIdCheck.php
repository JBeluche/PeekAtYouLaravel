<?php

namespace App\Rules;

use App\Models\ColorAssociation;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class CalendarIdCheck implements Rule, DataAwareRule
{
    protected $data = [];

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function __construct()
    {
        //
    }

    //date->calendar_id must be equal to color_association->calendar_id. 
    public function passes($attribute, $value)
    {
        $colorAssociation = ColorAssociation::find($value);

        if (!is_null($colorAssociation) && request()->route('date')->calendar_id != $colorAssociation->calendar_id) {
            return false;
        }
        return true;
    }

    public function message()
    {
        return 'Conflicting calendar id\'s';
    }
}
