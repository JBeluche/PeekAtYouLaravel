<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarRequest extends FormRequest
{

    public function authorize()
    {
        if (Auth::user()->id !== $this->route('calendar')->user_id) {
            return false;
        }

        return true;
    }

    public function rules()
    {
        return [
            'name' => [ 'string', 'between:1,75', 'required'],
        ];
    }
}
