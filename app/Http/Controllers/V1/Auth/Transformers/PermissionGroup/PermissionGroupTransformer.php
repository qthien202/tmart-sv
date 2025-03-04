<?php

namespace  App\Http\Controllers\V1\Auth\Transformers\PermissionGroup;


use App\PermissionGroup;
use App\Supports\SERVICE_Error;
use League\Fractal\TransformerAbstract;

/**
 * Class PermissionGroupTransformer
 * @package App\V1\CMS\Transformers\PermissionGroup
 */
class PermissionGroupTransformer extends TransformerAbstract
{
    /**
     * @param PermissionGroup $permissionGroup
     * @return array
     * @throws \Exception
     */
    public function transform(PermissionGroup $permissionGroup)
    {
        try {

            $permissions = object_get($permissionGroup, 'permissions', []);
            if (!empty($permissions)) {
                $permissions = $permissions->toArray();
                $permissions = array_map(function ($permission) {
                    return [
                        'id'   => $permission['id'],
                        'name' => $permission['name'],
                        'code' => $permission['code'],
                    ];
                }, $permissions);
            }

            return [
                'id' => $permissionGroup->id,
                'code' => $permissionGroup->code,
                'name' => $permissionGroup->name,
                'description' => $permissionGroup->description,
                'is_active' => $permissionGroup->is_active,
                'permissions' => $permissions,
                'created_at' => date('d/m/Y H:i', strtotime($permissionGroup->created_at)),
                'updated_at' =>  date('d/m/Y H:i', strtotime($permissionGroup->updated_at)),
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message'], $response['code']);
        }
    }

}