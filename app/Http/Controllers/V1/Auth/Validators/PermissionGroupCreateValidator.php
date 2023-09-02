<?php
/**
 * Created by PhpStorm.
 * User: SANG NGUYEN
 * Date: 1/16/2019
 * Time: 8:46 AM
 */

namespace  App\Http\Controllers\V1\Auth\Validators;


use App\Http\Validators\ValidatorBase;
use App\PermissionGroup;
use App\Supports\Message;

class PermissionGroupCreateValidator extends ValidatorBase
{
    protected function rules()
    {
        return [
            'code'                  => [
                'required',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $permissionGroup = PermissionGroup::model()->where('code', $value)->first();
                        if (!empty($permissionGroup)) {
                            return $fail(Message::get("unique", "$attribute: #$value"));
                        }
                    }
                    return true;
                }
            ],
            'name'                  => 'required|max:50',
        ];
    }

    protected function attributes()
    {
        return [
            'code'                  => Message::get("code"),
            'name'                  => Message::get("alternative_name"),
        ];
    }
}