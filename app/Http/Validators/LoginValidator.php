<?php

namespace App\Http\Validators;

use App\Supports\Message;

/**
 * Class LoginValidator
 * @package App\Http\Validators\Admin
 */
class LoginValidator extends ValidatorBase
{
    protected function rules()
    {
        return [
            'username'    => 'required|exists:users,username',
            'password'    => 'required',
            'device_type' => 'required|in:ANDROID,IOS,UNKNOWN',
            'device_id'   => 'nullable|max:50'
        ];
    }

    protected function attributes()
    {
        return [
            'username'    => Message::get("user_name"),
            'password'    => Message::get("password"),
            'device_type' => Message::get("device_type"),
            'device_id'   => Message::get("device_id"),
        ];
    }
}