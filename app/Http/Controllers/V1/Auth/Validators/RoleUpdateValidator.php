<?php

namespace  App\Http\Controllers\V1\Auth\Validators;


use App\Http\Validators\ValidatorBase;
use App\Role;
use App\Supports\Message;
use Illuminate\Http\Request;

class RoleUpdateValidator extends ValidatorBase
{
    protected function rules()
    {
        return [
            'id'         => 'required|exists:roles,id,deleted_at,NULL',
            'code'       => [
                'nullable',
                'max:10',
                function ($attribute, $value, $fail) {
                    $input = Request::capture();
                    $item = Role::where('code', $value)->whereNull('deleted_at')->get()->toArray();
                    if (!empty($item) && count($item) > 0) {
                        if (count($item) > 1 || ($input['id'] > 0 && $item[0]['id'] != $input['id'])) {
                            return $fail(Message::get("unique", "$attribute: #$value"));
                        }
                    }
                }
            ],
            'role_level' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $input = Request::capture();
                    $item = Role::where('role_level', $value)->whereNull('deleted_at')->get()->toArray();
                    if (!empty($item) && count($item) > 0) {
                        if (count($item) > 1 || ($input['id'] > 0 && $item[0]['id'] != $input['id'])) {
                            return $fail(Message::get("unique", "$attribute: #$value"));
                        }
                    }
                }
            ],
            'name'       => 'nullable|max:50',
        ];
    }

    protected function attributes()
    {
        return [
            'id'         => Message::get("id"),
            'code'       => Message::get("code"),
            'role_level' => Message::get("role_level"),
            'name'       => Message::get("name"),
        ];
    }
}