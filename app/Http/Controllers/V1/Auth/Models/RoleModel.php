<?php
namespace App\Http\Controllers\V1\Auth\Models;


use App\SERVICE;
use App\Role;
use App\Supports\Message;
use Illuminate\Support\Facades\DB;

class RoleModel extends AbstractModel
{
    public function __construct(Role $model = null)
    {
        parent::__construct($model);
    }

    /**
     * @param array $input
     * @param array $with
     * @param null $limit
     * @return mixed
     */
    public function search($input = [], $with = [], $limit = null)
    {
        $query = $this->make($with);
        $this->sortBuilder($query, $input);

        if ($limit) {
            return $query->paginate($limit);
        } else {
            return $query->get();
        }
    }

    /**
     * @param $input
     * @return mixed
     * @throws \Exception
     */
    public function upsert($input)
    {
        $id = !empty($input['id']) ? $input['id'] : 0;
        if ($id) {
            $role = Role::find($id);
            if (empty($role)) {
                throw new \Exception(Message::get("V003", "ID: #$id"));
            }
            $role->name = array_get($input, 'name', $role->name);
            $role->code = array_get($input, 'code', $role->code);
            $role->status = array_get($input, 'status', $role->status);
            $role->description = array_get($input, 'description', $role->description);
            $role->role_level = array_get($input, 'role_level', $role->role_level);
            $role->is_active = array_get($input, 'is_active', $role->is_active);
            $role->updated_at = date("Y-m-d H:i:s", time());
            $role->updated_by = SERVICE::getCurrentUserId();
            $role->save();
        } else {
            $param = [
                'code'        => $input['code'],
                'name'        => $input['name'],
                'status'      => array_get($input, 'status', 0),
                'role_level'  => array_get($input, 'role_level'),
                'description' => array_get($input, 'description'),
                'is_active'   => 1,
            ];

            $role = $this->create($param);
        }

        return $role;
    }
}