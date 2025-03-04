<?php

namespace  App\Http\Controllers\V1\Auth\Validators;


use App\Http\Validators\ValidatorBase;
use App\Role;
use App\Supports\Message;
use Illuminate\Http\Request;

/**
 * Class RoleValidator
 * @package App\V1\CMS\Validators
 */
class RoleValidator extends ValidatorBase
{
    protected function rules()
    {
        return [
            'id'         => 'integer|exists:roles,id,deleted_at,NULL',
            'code'       => [
                'required',
                'max:20',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $role = Role::Model()->where('code', $value)->first();
                        if (!empty($role)) {
                            return $fail(Message::get("unique", "$attribute: #$value"));
                        }
                    }
                    return true;
                }
            ],
            'role_level' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $role = Role::Model()->where('role_level', $value)->first();
                        if (!empty($role)) {
                            return $fail(Message::get("unique", "$attribute: #$value"));
                        }
                    }
                    return true;
                }
            ],
            'name'       => 'nullable',
        ];
    }

    protected function attributes()
    {
        return [
            'code'       => Message::get("code"),
            'name'       => Message::get("name"),
            'role_level' => Message::get("role_level"),
        ];
    }
}