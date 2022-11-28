<?php

namespace App\Http\Requests;

use App\Helper\validationHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class AssignCampsRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // 'receiver_company_id' => 'required',
            'square_id' => 'required|integer',
            'camp_id' => 'required|array',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        validationHelper::failedValidation($validator, 'AssignCamps');
    }
}
