<?php

namespace App\Rules;

use App\Models\ColorAssociationDate;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class DateColorAssociationExist implements Rule, DataAwareRule
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
        $colorAssociationDate = ColorAssociationDate::where('color_association_id', $value)->where('date_id', $this->data['date_id'])->first();

        //For the store method
        if(is_null($colorAssociationDate)){
            return true;

        //For the update method
        } else if ($value === $colorAssociationDate->id){
            return true;
        }

        return false;
    }
    
    public function message()
    {
        return 'The ColorAssociationDate already exists';
    }
}
