<?php

namespace  App\Http\Controllers\V1\Auth\Validators;


use App\Http\Validators\ValidatorBase;
use App\Supports\Message;

class UserCreateValidator extends ValidatorBase
{
    protected function rules()
    {
        return [
            'id'            => 'exists:users,id,deleted_at,NULL',
            'email'         => 'nullable|unique_create:users,email',
            'code'          => 'required|max:50|unique_create:users,code',
            'department_id' => 'exists:departments,id,deleted_at,NULL',
            'company_id'    => 'exists:companies,id,deleted_at,NULL',
            'phone'         => 'max:12',
            'password'      => 'required',
            'full_name'     => 'required',
        ];
    }

    protected function attributes()
    {
        return [
            'phone'         => Message::get("phone"),
            'department_id' => Message::get("department_id"),
            'company_id'    => Message::get("company_id"),
            'email'         => Message::get("email"),
            'code'          => Message::get("code"),
            'full_name'     => Message::get("full_name"),
            'password'      => Message::get("password"),
        ];
    }
}
