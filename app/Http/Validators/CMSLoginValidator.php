<?php
/**
 * Created by PhpStorm.
 * User: kpistech2
 * Date: 2019-01-20
 * Time: 22:37
 */

namespace App\Http\Validators;


use App\Supports\Message;

class CMSLoginValidator extends ValidatorBase
{
    protected function rules()
    {
        return [
            'phone'       => 'required|exists:users,phone,deleted_at,NULL',
            'password'    => 'required',
            'device_type' => 'in:DESKTOP,TABLET,PHONE,ANDROID,IOS,UNKNOWN',
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
