<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserDateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_date' => 'required|date_format:"M/d/Y"',
            'finish_date' => 'required|date_format:"M/d/Y"|before:tomorrow|after:start_date'
        ];
    }
}
