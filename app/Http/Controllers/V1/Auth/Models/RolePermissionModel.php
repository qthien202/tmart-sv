<?php
namespace App\Http\Controllers\V1\Auth\Models;


use App\RolePermission;

class RolePermissionModel extends AbstractModel
{
    public function __construct(RolePermission $model = null)
    {
        parent::__construct($model);
    }
}