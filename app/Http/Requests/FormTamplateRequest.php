<?php

namespace App\Http\Requests;

use App\Helper\validationHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class FormTamplateRequest extends FormRequest
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
            'name' => 'required|string',
        ];
    }
    
    protected function failedValidation(Validator $validator)
    {
        validationHelper::failedValidation($validator, 'FormTamplate');
    }
}
