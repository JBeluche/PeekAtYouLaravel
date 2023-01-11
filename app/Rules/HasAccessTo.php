<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class HasAccessTo implements Rule
{
    
    protected $accessToCheck;
    protected $errorMessage;



    public function __construct($param)
    {
        $this->accessToCheck = $param;
    }

    public function passes($attribute, $value)
    {
       
        
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }
}
