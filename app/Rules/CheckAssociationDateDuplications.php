<?php

namespace App\Rules;

use App\Models\ColorAssociationDate;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class CheckAssociationDateDuplications implements Rule, DataAwareRule
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
        $foundId = array_search($value, array_column($this->data['color_association_dates'], 'id'));
        $colorAssociationDate = $this->data['color_association_dates'][$foundId];

        $colorAssociationDateRecord = ColorAssociationDate::where('id', $value)->first();

        //Confirm id
        if (is_null($colorAssociationDateRecord)) {
            $this->errorMessage = 'The color_association_date could not be found';
            return false;
        } elseif ($colorAssociationDateRecord->date_id != $this->data['date_id']) {
            $this->errorMessage = 'The color_association_date belongs to another date. Change the date id, or the color_association_date id.';
            return false;
        }

        $colorAssociationDatesInDB = ColorAssociationDate::where('date_id', $this->data['date_id'])->get();

        //Lets loop all color_associations_date from the database
        foreach ($colorAssociationDatesInDB as $databaseRecord) {

            //Lets ignore the $databaseRecord === to what we are currently validating. And look if the current item to validate is conflicting with a database record.
            if ($databaseRecord->id != $colorAssociationDate['id'] && $databaseRecord->color_association_id === $colorAssociationDate['color_association_id']) {

                //Let's see if the databaser record is being updated to something different first.
                $foundId = array_search($databaseRecord->id, array_column($this->data['color_association_dates'], 'id'));
                $newObject = $this->data['color_association_dates'][$foundId];

                //If the found id is not numeric, then we did not find the current database record in the request, therefor there is a conflict     
                if (is_numeric($foundId)) {

                    //However, if we did find the record. Check wheter or not the conflict is persistent inside the request.
                    if ($newObject['color_association_id'] === $databaseRecord->color_association_id) {
                        $this->errorMessage = 'Duplication error. There is already a color association linked with the current date.';
                        return false;
                    }
                } else {
                    $this->errorMessage = 'Duplication error. There is already a color association linked with the current date.';
                    return false;
                }
            }
        }
        return true;
    }

    public function message()
    {
        return $this->errorMessage;
    }
}
