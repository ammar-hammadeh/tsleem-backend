<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helper\validationHelper;
use Illuminate\Contracts\Validation\Validator;

class UpdateAssignCampsRequest extends FormRequest
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
            // 'receiver_company_id' => 'required',
            'square_id' => 'required|integer',
            'camp_id' => 'required|integer',
        ];

    }
    protected function failedValidation(Validator $validator)
    {
        validationHelper::failedValidation($validator, 'AssignCamps');
    }

}
