<?php
/**
 * User: Administrator
 * Date: 12/10/2018
 * Time: 06:27 PM
 */

 namespace App\Http\Controllers\V1\Auth\Controllers;


use App\Permission;
use App\Role;
use App\Supports\Log;
use App\Supports\Message;
use App\Supports\SERVICE_Error;
use App\V1\CMS\Models\RoleModel;
use App\V1\CMS\Models\RolePermissionModel;
use App\V1\CMS\Transformers\Role\RoleTransformer;
use App\V1\CMS\Validators\RoleUpdateValidator;
use App\V1\CMS\Validators\RoleValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class RoleController extends BaseController
{
    protected $model;

    /**
     * RoleController constructor.
     */
    public function __construct()
    {
        $this->model = new RoleModel();
    }

    /**
     * @param Request $request
     * @param RoleTransformer $roleTransformer
     * @return \Dingo\Api\Http\Response|void
     */
    public function checkForeignTable($id, $tables)
    {
        if (empty($tables)) {
            return true;
        }

        $result = "";

        foreach ($tables as $table_key => $table) {
            $temp = explode(".", $table_key);
            $table_name = $temp[0];
            $foreign_key = !empty($temp[1]) ? $temp[1] : 'id';
            $data = DB::table($table_name)->where($foreign_key, $id)->first();
            if (!empty($data)) {
                $result .= "$table; ";
            }
        }

        $result = trim($result, "; ");

        if (!empty($result)) {
            return $this->responseError(Message::get("R004", $result));
        }

        return true;
    }

    public function search(Request $request, RoleTransformer $roleTransformer)
    {
        $input = $request->all();

        try {
            $roles = $this->model->search($input, [], array_get($input, 'limit', 20));
            Log::view($this->model->getTable());
        } catch (\Exception $ex) {
            if (env('APP_ENV') == 'testing') {
                return $this->responseError($ex->getMessage());
            } else {
                return $this->responseError(Message::get("R011"));
            }
        }

        return $this->response->paginator($roles, $roleTransformer);
    }

    /**
     * @param $id
     * @param RoleTransformer $roleTransformer
     *
     * @return \Dingo\Api\Http\Response
     */
    public function detail($id, RoleTransformer $roleTransformer)
    {
        try {
            $role = $this->model->getFirstBy('id', $id);
            Log::view($this->model->getTable());
        } catch (\Exception $ex) {
            if (env('APP_ENV') == 'testing') {
                return $this->responseError($ex->getMessage());
            } else {
                return $this->responseError(Message::get("R011"));
            }
        }

        return $this->response->item($role, $roleTransformer);
    }

    public function store(Request $request, RoleValidator $roleValidator, RoleTransformer $roleTransformer)
    {
        $input = $request->all();
        $roleValidator->validate($input);

        try {
            $role = $this->model->upsert($input);
            Log::create($this->model->getTable(), $role->name);
        } catch (\Exception $ex) {
            DB::rollBack();
            $response = SERVICE_Error::handle($ex);
            return $this->responseError($response['message']);
        }

        return $this->response->item($role, $roleTransformer);
    }

    public function update($id, Request $request, RoleUpdateValidator $roleUpdateValidator, RoleTransformer $roleTransformer)
    {
        $input = $request->all();
        $input['id'] = $id;
        $roleUpdateValidator->validate($input);
        try {
            DB::beginTransaction();
            $role = $this->model->upsert($input);
            Log::update($this->model->getTable(), $role->name);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            $response = SERVICE_Error::handle($ex);
            return $this->responseError($response['message']);
        }
        return $this->response->item($role, $roleTransformer);
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $role = Role::find($id);
            if (empty($role)) {
                return $this->responseError(Message::get("V003", "ID #$id"));
            }
            // 1. Delete Role
            $role->delete();
            Log::delete($this->model->getTable(), $role->name);

            DB::commit();
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            return $this->responseError($response['message']);
        }

        return ['status' => 'OK', 'message' => "Delete Successful"];
    }

    public function addPermission($id, Request $request)
    {
        $input = $request->all();

        try {
            $rolePermissionModel = new RolePermissionModel();

            $allPermission = $rolePermissionModel->search(['role_id' => $id])->toArray();
            $allPermission = array_pluck($allPermission, 'role_id', 'permission_id');
            // 1. Add new Permission
            DB::beginTransaction();
            foreach ($input['permission_id'] as $permission_id) {
                if (empty($allPermission[$permission_id])) {
                    // Add role
                    $rolePermissionModel->refreshModel();
                    $role = $rolePermissionModel->create(['role_id' => $id, 'permission_id' => $permission_id]);

                    // Write Log
                    $listRolePermission = $role->toArray();
                    $namePermisson = $this->getNamePermission($listRolePermission['permission_id']);
                    Log::update($rolePermissionModel->getTable(), $namePermisson);

                } else {
                    unset($allPermission[$permission_id]);
                }
            }
            //2 . Delete role permission
            foreach ($allPermission as $permission_id => $role_id) {
                $rolePermissionModel->refreshModel();
                $rolePermissionModel->deleteBy(['role_id', 'permission_id'], [
                    'role_id'       => $role_id,
                    'permission_id' => $permission_id,
                ]);
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            $response = SERVICE_Error::handle($ex);
            return $this->responseError($response['message']);
        }
        return ['status' => 'Update Role Successful', 'message' => "Update Role Successful!"];
    }

    public function getNamePermission($id)
    {
        $profile = Permission::where(['id' => $id])->first();
        return $profile->code;
    }
}
