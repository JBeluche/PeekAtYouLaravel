<?php

namespace App\Rules;

use App\Models\ColorAssociationDate;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class CheckDateId implements Rule, DataAwareRule
{
    /**
     * Check if the date is connected to all the color_association_date. 
     */
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

        if (is_null($colorAssociationDate)) {
            $this->errorMessage = 'Could not find color_association_date';
            return false;
        }
        else if(isset($this->data['date_id']) && $this->data['date_id'] != $colorAssociationDate->date_id){
            $this->errorMessage = 'The color_association_date belongs to another date. Change the date id, or the color_association_date id.';
            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->errorMessage;
    }
}
