<?php

namespace App\Rules;

use App\Models\ColorAssociationDate;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class CalendarIdCheckOnUpdate implements Rule, DataAwareRule
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
        $colorAssociationDate = ColorAssociationDate::find($value);
        $newAssociation = [];

        if(is_null($colorAssociationDate)){
            $this->errorMessage = 'Wrong id: could not find colorAssociationDate';
            return false;
        }

        foreach($data->associations as $association){
            if($association['id'] === $colorAssociationDate){
                $newAssociation = $association;
            }
        }
      
        if($colorAssociationDate->date->calendar_id != $newAssociation->colorAssociation->calendar_id){
            $this->errorMessage = 'Own no, something went wrong. | (Date->calendar id) and (Color association->calendar id) are not the same. | :(';
            return false;
        }
        return true;
       
    }
   
    public function message()
    {
        return $this->errorMessage;
    }
