<?php

namespace App\Helper;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;


class validationHelper
{
    public function __construct()
    {
    }

    public static function failedValidation(Validator $validator, $model)
    {
        $errors = $validator->failed();
        $translated_error = [];
        foreach ($errors as $ekey => $error) {
            foreach ($error as $fkey => $field) {
                // array_push($translated_error, __('validation.'.$model.'.'.Str::lower("$ekey.$fkey")));
                $translated_error[$ekey] = __('validation.' . $model . '.' . Str::lower("$ekey.$fkey"));
            }
        }

        throw new HttpResponseException(
            response()->json(['errors' => $translated_error], 422)
        );
    }
}
