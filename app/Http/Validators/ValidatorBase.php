<?php

namespace App\Http\Validators;

use Illuminate\Http\JsonResponse;
use App\Supports\Message;
use Illuminate\Http\Exceptions\HttpResponseException;
use Validator;

abstract class ValidatorBase
{
    abstract protected function rules();

    protected $_input;

    public function validate($input)
    {
        $this->_input = $input;
        $validator = Validator::make($input, $this->rules(), $this->messages());
        $validator->setAttributeNames($this->attributes());

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $output = [];
            foreach ($errors as $key => $error) {
                $output[$key] = $error[0];
            }
            if ($validator->fails()) {
                // throw new HttpResponseException(
                //     new JsonResponse(['data' => $validator->errors()], 422)
                // );
                throw new HttpResponseException(
                    new JsonResponse([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors(),
                    ], 422)
                );
            }           
        }

        return $validator;
    }

    protected function messages()
    {
        $attributes = array_keys(config('validation'));
        $output     = [];
        foreach ($attributes as $attribute) {
            $output["*.$attribute"] = Message::get($attribute, ':attribute');
        }
        return $output;
    }
}
