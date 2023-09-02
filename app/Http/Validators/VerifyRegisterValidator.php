<?php

namespace App\Http\Validators;


use App\Supports\Message;

class VerifyRegisterValidator extends ValidatorBase
{
    protected function rules()
    {
        return [
            'verify_code' => 'required',
            'email'       => 'required|exists:users,email,deleted_at,NULL',
        ];
    }

    protected function attributes()
    {
        return [
            'verify_code' => Message::get("verify_code"),
            'email'       => Message::get("email"),
        ];
    }
}