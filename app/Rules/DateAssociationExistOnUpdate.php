<?php

namespace App\Rules;

use App\Models\ColorAssociationDate;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class DateAssociationExistOnUpdate implements Rule, DataAwareRule
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

        //Get our color association date
        $colorAssociationDate = ColorAssociationDate::where('id', $value)->first();

        $duplicationConflics = ColorAssociationDate::where('color_association_id', $colorAssociationDate->id)->where('date_id', $this->data['date_id'])->get();

        //Check if id is working
        if(is_null($colorAssociationDate)){
            $this->errorMessage = 'The color_association_date could not be found';
            return false;
        } 

        //Find the new color association date to
        foreach($this->data['associations'] as $association){
            if($association['id'] === $value){

            }
        }

        //Look at the conflicts.
        foreach($duplicationConflics as $conflict){

            //If conflict is the current object, ignore
            if($conflict->id != $colorAssociationDate->id){

                $passed = false;
                
                //Resolve conflict by looking at the request and seeing if the color_association_date conflict is going to be changed.
                foreach($this->data['associations'] as $association){

                    //Find color_association_date in request
                    if($association['id'] === $conflict->id){
                        if($association['color_association_id'] != $conflict->color_association_id){
                            $passed = true;
                            exit;
                        }
                    }
                }

                if(!$passed){
                    $this->errorMessage = 'The association for this date already exists.';
                    return false;
                }

            }
        }
             
        return true;
    }
    
    public function message()
    {
        return 'The ColorAssociationDate already exists';
    }
}
