<?php
namespace App\Http\Controllers\V1\Auth\Models;

use App\Supports\Message;
use App\User;
use App\SERVICE;
use Carbon\Carbon;

class UserModel extends AbstractModel
{
    public function __construct(User $model = null)
    {
        parent::__construct($model);
    }

    public function upsert($input)
    {
        $phone = "";
        if (!empty($input['phone'])) {
            $phone = str_replace(" ", "", $input['phone']);
            $phone = preg_replace('/\D/', '', $phone);
        }
        $id          = !empty($input['id']) ? $input['id'] : 0;
        if ($id) {
            $user = User::find($id);
            if (empty($user)) {
                throw new \Exception(Message::get("V003", "ID: #$id"));
            }
            if (!empty($input['password'])) {
                $password = password_hash($input['password'], PASSWORD_BCRYPT);
            }
            $user->phone         = array_get($input, 'phone', $user->phone);
            $user->code          = array_get($input, 'code', $user->code);
            $user->username      = array_get($input, 'code', $user->code);
            $user->first_name    = array_get($input, 'first_name', $user->first_name);
            $user->last_name     = array_get($input, 'last_name', $user->last_name);
            $user->full_name     = array_get($input, 'full_name', $user->full_name);
            $user->address       = array_get($input, 'address', $user->address);
            $user->birthday      = Carbon::parse(array_get($input, 'birthday', $user->birthday));
            $user->genre         = array_get($input, 'genre', $user->genre);
            $user->avatar        = array_get($input, 'avatar', $user->avatar);
            $user->id_number     = array_get($input, 'id_number', $user->id_number);
            $user->email         = array_get($input, 'email', $user->email);
            $user->role_id       = array_get($input, 'role_id', $user->role_id);
            $user->password      = empty($password) ? array_get($input, 'password', $user->password) : $password;
            $user->verify_code   = array_get($input, 'verify_code', $user->verify_code);
            $user->expired_code  = array_get($input, 'expired_code', $user->expired_code);
            $user->is_active     = array_get($input, 'is_active', $user->is_active);
            $user->updated_at    = date("Y-m-d H:i:s", time());
            $user->updated_by    = SERVICE::getCurrentUserId();
            $user->save();
            // dd($user->birthday);
        } else {

            $now       = date("Y-m-d H:i:s", time());
            $y         = date('Y', time());
            $m         = date("m", time());
            $d         = date("d", time());
            $dir       = !empty($input['avatar']) ? "$y/$m/$d" : null;
            $file_name = empty($dir) ? null : "avatar_{$input['phone']}";
            if ($file_name) {
                $avatars         = explode("base64,", $input['avatar']);
                $input['avatar'] = $avatars[1];
                if (!empty($file_name) && !is_image($avatars[1])) {
                    throw new \Exception(Message::get("V002", "Avatar"));
                }
            }
            $full = explode(" ", trim($input['full_name']));

            $full_name = $input['full_name'];
            $first     = trim($full[count($full) - 1]);
            unset($full[count($full) - 1]);
            $last = trim(implode(" ", $full));

            $param = [
                'phone'         => $phone,
                'code'          => $input['code'],
                'username'      => SERVICE::array_get($input, 'username', $input['code']),
                'first_name'    => $first,
                'last_name'     => $last,
                'full_name'     => $full_name,
                'address'       => array_get($input, 'address', null),
                'birthday'      => empty($input['birthday']) ? null : $input['birthday'],
                'genre'         => array_get($input, 'genre', "O"),
                'avatar'        => $file_name ? $dir . "/" . $file_name . ".jpg" : null,
                'id_number'     => array_get($input, 'id_number', 0),
                'email'         => array_get($input, 'email'),
                'role_id'       => array_get($input, 'role_id'),
                'verify_code'   => mt_rand(100000, 999999),
                'expired_code'  => date('Y-m-d H:i:s', strtotime("+5 minutes")),
                'is_active'     => array_get($input, 'is_active', 1),
                'created_at'    => $now,
                'created_by'    => SERVICE::getCurrentUserId(),
            ];
            if (!empty($input['password'])) {
                $param['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
            }
            $user = $this->create($param);

        }
        return $user;
    }

    public function search($input = [], $with = [], $limit = null)
    {
        $query = $this->make($with);
        $this->sortBuilder($query, $input);
        if (!empty($input['code'])) {
            $query = $query->where('code', 'like', "%{$input['code']}%");
        }
        if (!empty($input['phone'])) {
            $query = $query->where('phone', 'like', "%{$input['phone']}%");
        }
        if (isset($input['is_active'])) {
            $query = $query->where('is_active', 'like', "%{$input['is_active']}%");
        }
        if ($limit) {
            if ($limit === 1) {
                return $query->first();
            } else {
                return $query->paginate($limit);
            }
        } else {
            return $query->get();
        }
    }
}
