<?php
namespace App\Http\Controllers\V1\Auth\Models;


use App\SERVICE;
use App\Permission;
use App\Supports\Message;

class PermissionModel extends AbstractModel
{
    public function __construct(Permission $model = null)
    {
        parent::__construct($model);
    }

    public function upsert($input)
    {
        $id = !empty($input['id']) ? $input['id'] : 0;
        if ($id) {
            $permission = Permission::find($id);
            if (empty($permission)) {
                throw new \Exception(Message::get("V003", "ID: #$id"));
            }
            $permission->name = array_get($input, 'name', $permission->name);
            $permission->code = array_get($input, 'code', $permission->code);
            $permission->group_id = array_get($input, 'group_id', $permission->group_id);
            $permission->description = array_get($input, 'description', NULL);
            $permission->updated_at = date("Y-m-d H:i:s", time());
            $permission->is_active = array_get($input, 'is_active', $permission->is_active);
            $permission->updated_by = SERVICE::getCurrentUserId();
            $permission->save();
        } else {
            $param = [
                'code'        => $input['code'],
                'name'        => $input['name'],
                'group_id'    => $input['group_id'],
                'description' => array_get($input, 'description'),
                'is_active'   => 1,

            ];

            $permission = $this->create($param);
        }

        return $permission;
    }
}