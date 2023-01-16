<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateDateRequest extends FormRequest
{
    public function authorize()
    {
        if (Auth::user()->id !== $this->route('date')->calendar->user_id) {
            return false;
        }

        return true;
    }

    public function rules()
    {
        return [
            'long_note' => ['nullable', 'string', 'max:255', 'required'],
            'displayed_note' => ['numeric', 'required'],
        ];
    }
}
