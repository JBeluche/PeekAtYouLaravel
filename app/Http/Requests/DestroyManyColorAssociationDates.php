<?php

namespace App\Http\Requests;

use App\Rules\HasAccessTo;
use Illuminate\Foundation\Http\FormRequest;

class DestroyManyColorAssociationDates extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id.*' => ['required', 'numeric', 'exists:color_association_dates,id', new HasAccessTo('COLOR_ASSOCIATION_DATE')],
        ];
    }
}
