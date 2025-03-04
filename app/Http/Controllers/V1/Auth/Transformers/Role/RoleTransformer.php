<?php


namespace  App\Http\Controllers\V1\Auth\Transformers\Role;


use App\Role;
use App\RolePermission;
use App\Supports\SERVICE_Error;
use App\V1\CMS\Models\PermissionModel;
use App\V1\CMS\Models\RolePermissionModel;
use League\Fractal\TransformerAbstract;

class RoleTransformer extends TransformerAbstract
{
    public function transform(Role $role)
    {
        try {
            $permissionModel = new PermissionModel();
            $rolePermissionModel = new RolePermissionModel();
            $roles = RolePermission::model()
                ->select([
                    $permissionModel->getTable() . '.name as permission_name',
                    $permissionModel->getTable() . '.code as permission_code'
                ])
                ->where('role_id', $role->id)
                ->whereNull($permissionModel->getTable() . '.deleted_at')
                ->join($permissionModel->getTable(), $permissionModel->getTable() . '.id', '=',
                    $rolePermissionModel->getTable() . '.permission_id')
                ->get()->toArray();

            $permissions = array_pluck($roles, "permission_code");

            return [
                'id'          => $role->id,
                'code'        => $role->code,
                'name'        => $role->name,
                'status'      => $role->status,
                'description' => $role->description,
                'permissions' => $permissions,
                'is_active'   => $role->is_active,
                'role_level'  => $role->role_level,
                'created_at'  => date('d/m/Y H:i', strtotime($role->created_at)),
                'updated_at'  => date('d/m/Y H:i', strtotime($role->updated_at)),
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message'], $response['code']);
        }
    }
}
