<?php

namespace App\Rules;

use App\Models\ColorAssociation;
use App\Models\ColorAssociationDate;
use App\Models\Date;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class CalendarIdCheck implements Rule, DataAwareRule
{
    protected $data = [];

    protected $errorMessage;

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
   
    public function __construct()
    {
        //
    }
   
    public function passes($attribute, $value)
    {
        $date = Date::find($this->data['date_id']);
   
        $colorAssociation = ColorAssociation::find($value);

        if(is_null($date)){
            $this->errorMessage = 'Could not find date';
            return false;
        }
        else if(is_null($colorAssociation)){
            $this->errorMessage = 'Could not find color association';
            return false;
        }
        else if($date->calendar_id != $colorAssociation->calendar_id){
            $this->errorMessage = 'Own no, something went wrong. | (Date->calendar id) and (Color association->calendar id) are not the same. | :(';
            return false;
        }
        return true;
       
    }
   
    public function message()
    {
        return $this->errorMessage;
    }
}
