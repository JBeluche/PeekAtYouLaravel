<?php

namespace App\Rules;

use App\Models\ColorAssociation;
use App\Models\ColorAssociationDate;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class CombinationExists implements Rule, DataAwareRule
{

    protected $data = [];
    protected $requestType;
    protected $table;

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function __construct(String $table, String $requestType)
    {
        $this->requestType = $requestType;
        $this->table = $table;
    }

    public function passes($attribute, $value)
    {
        if ($this->table === 'color_associations' && $this->requestType === 'update') {
            return $this->colorAssociatoinsOnUpdateCheck($value);
        } else if ($this->table === 'color_associations' && $this->requestType === 'store') {
            return $this->colorAssociatoinsOnStoreCheck($value);
        } else if ($this->table === 'color_association_dates' && $this->requestType === 'update') {
            return $this->colorAssociatoinDatesOnUpdateCheck($value);
        } else if ($this->table === 'color_association_dates' && $this->requestType === 'store') {
            return $this->colorAssociatoinDatesOnStoreCheck($value);
        }
        return true;
    }


    public function message()
    {
        return 'Combination already exist.';
    }

    private function colorAssociatoinsOnUpdateCheck($value)
    {
        //Get the new color_association inside the request array.
        $foundId = array_search($value, array_column($this->data['color_associations'], 'id'));
        $modelToValidate = $this->data['color_associations'][$foundId];

        $existingCombinationModel = ColorAssociation::where(['color_id' => $modelToValidate['color_id']])
            ->where(['calendar_id' => request()->route('calendar')->id])
            ->whereNot('id', $modelToValidate['id'])
            ->first();

        //If no combination existing in database
        if (is_null($existingCombinationModel)) {
            return false;
        }

        //If the combination exists in the database, we still check for it in the request, to see if it's value  is going to change
        $foundId = array_search($existingCombinationModel->id, array_column($this->data['color_associations'], 'id'));
        $existingModelInRequest = $this->data['color_associations'][$foundId];

        if ($existingModelInRequest['color_id'] != $existingCombinationModel->color_id) {
            return true;
        }
    }

    private function colorAssociatoinsOnStoreCheck($value)
    {
        $existingCombinationModel = ColorAssociation::where(['color_id' => $value])
            ->where(['calendar_id' => request()->route('calendar')->id])
            ->first();

        if (is_null($existingCombinationModel)) {
            return true;
        }
    }

    private function colorAssociatoinDatesOnUpdateCheck($value)
    {
        //Get the new color_association inside the request array.
        $foundId = array_search($value, array_column($this->data['color_associations'], 'id'));
        $modelToValidate = $this->data['color_associations'][$foundId];

        //Find all duplicates in database
        $existingCombinationModel = ColorAssociationDate::where(['color_association_id' => $modelToValidate['color_association_id']])
            ->where(['date_id' => request()->route('date_id')->id])
            ->whereNot('id', $modelToValidate['id'])
            ->first();

        //If no combination existing in database
        if (is_null($existingCombinationModel)) {
            return true;
        }

          //If the combination exists in the database, we still check for it in the request, to see if it's value  is going to change
          $foundId = array_search($existingCombinationModel->id, array_column($this->data['color_association_dates'], 'id'));
          $existingModelInRequest = $this->data['color_association_dates'][$foundId];
  
          if ($existingModelInRequest['color_association_id'] != $existingCombinationModel->color_association_id) {
              return false;
          }
    }

    private function colorAssociatoinDatesOnStoreCheck($value)
    {
        $existingCombinationModel = ColorAssociationDate::where(['color_association_id' => $value])
            ->where(['date_id' => request()->route('date')->id])
            ->first();

        if (!is_null($existingCombinationModel)) {
            return false;
        }
        return true;
    }
}
