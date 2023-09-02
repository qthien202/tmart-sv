<?php

namespace App;

use App\Http\Controllers\V1\Auth\Models\PermissionModel;
use App\Http\Controllers\V1\Auth\Models\RolePermissionModel;
use App\Supports\Message;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Cache;

class SERVICE
{
    private static $_data;

    public static final function info()
    {
        self::__getUserInfo();

        return self::$_data;
    }

    // private static function __getUserInfo()
    // {

    //     $token = JWTAuth::getToken();

    //     if (!$token) {
    //         return response()->json([
    //             'message'     => 'A token is required',
    //             'status_code' => HttpResponse::HTTP_UNAUTHORIZED,
    //         ], HttpResponse::HTTP_UNAUTHORIZED);
    //     }
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         if (empty($user)) {
    //             return response()->json([
    //                 'message'     => Message::get("V003", Message::get('customers')),
    //                 'status_code' => HttpResponse::HTTP_UNAUTHORIZED,
    //             ], HttpResponse::HTTP_UNAUTHORIZED);
    //         }
    //     } catch (TokenExpiredException $ex) {
    //         return response()->json([
    //             'message'     => $ex->getMessage(),
    //             'status_code' => HttpResponse::HTTP_UNAUTHORIZED,
    //         ], HttpResponse::HTTP_UNAUTHORIZED);
    //     } catch (TokenBlacklistedException $blacklistedException) {
    //         return response()->json([
    //             'message'     => $blacklistedException,
    //             'status_code' => HttpResponse::HTTP_UNAUTHORIZED,
    //         ], HttpResponse::HTTP_UNAUTHORIZED);
    //     }
    //     $permissionModel     = new PermissionModel();
    //     $rolePermissionModel = new RolePermissionModel();
    //     $roles               = RolePermission::model()
    //         ->select([
    //             $permissionModel->getTable() . '.name as permission_name',
    //             $permissionModel->getTable() . '.code as permission_code'
    //         ])
    //         ->where('role_id', $user->role_id)
    //         ->join($permissionModel->getTable(), $permissionModel->getTable() . '.id', '=',
    //             $rolePermissionModel->getTable() . '.permission_id')
    //         ->get()->toArray();
    //     $permissions         = array_pluck($roles, "permission_name", 'permission_code');

    //     self::$_data = [
    //         'id'          => $user->id,
    //         'email'       => $user->email,
    //         'user'        => $user->user,
    //         'company_id'  => object_get($user, 'company_id', null),
    //         'first_name'  => object_get($user, 'first_name', null),
    //         'last_name'   => object_get($user, 'last_name', null),
    //         'full_name'   => object_get($user, 'full_name', null),
    //         'is_super'    => $user->is_super,
    //         'role_id'     => object_get($user, 'role.id', null),
    //         'role_code'   => object_get($user, 'role.code', null),
    //         'role_name'   => object_get($user, 'role.name', null),
    //         'permissions' => $permissions,
    //     ];
    // }

    private static function __getUserInfo()
    {
    
        $token = JWTAuth::getToken();
    
        if (!$token) {
            return response()->json([
                'message'     => 'A token is required',
                'status_code' => HttpResponse::HTTP_UNAUTHORIZED,
            ], HttpResponse::HTTP_UNAUTHORIZED);
        }
    
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (empty($user)) {
                return response()->json([
                    'message'     => Message::get("V003", Message::get('customers')),
                    'status_code' => HttpResponse::HTTP_UNAUTHORIZED,
                ], HttpResponse::HTTP_UNAUTHORIZED);
            }
        } catch (TokenExpiredException $ex) {
            return response()->json([
                'message'     => $ex->getMessage(),
                'status_code' => HttpResponse::HTTP_UNAUTHORIZED,
            ], HttpResponse::HTTP_UNAUTHORIZED);
        } catch (TokenBlacklistedException $blacklistedException) {
            return response()->json([
                'message'     => $blacklistedException,
                'status_code' => HttpResponse::HTTP_UNAUTHORIZED,
            ], HttpResponse::HTTP_UNAUTHORIZED);
        }
        
        // Sử dụng Eloquent Relationships để lấy thông tin RolePermission của user
        $user->load('role.rolePermission.permission');
    
        $permissions = $user->role->rolePermission->pluck('permission.name', 'permission.code');
    
        self::$_data = [
            'id'          => $user->id,
            'email'       => $user->email,
            'user'        => $user->user,
            'company_id'  => $user->company_id,
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'full_name'   => $user->full_name,
            'is_super'    => $user->is_super,
            'role_id'     => $user->role->id,
            'role_code'   => $user->role->code,
            'role_name'   => $user->role->name, 
            'permissions' => $permissions,
        ];
    }
    //------------------static function-------------------------------

    public static final function isSuper()
    {
        $userInfo = self::info();

        return $userInfo['is_super'] == 1 ? true : false;
    }

    public static final function isAdminUser()
    {
        $userInfo = self::info();

        return !empty($userInfo['role_code']) && $userInfo['role_code'] == "ADMIN" ? true : false;
    }

    public static final function getCurrentUserId()
    {
        $userInfo = self::info();

        return $userInfo['id'] ?? null;
    }

    public static final function getCurrentUserName()
    {
        $userInfo = self::info();

        return $userInfo['full_name'] ?? null;
    }

    public static final function getCurrentCompanyId()
    {
        $companyInfo = self::info();

        return $companyInfo['company_id'] ?? null;
    }


    public static function getUpdatedBy()
    {
        $userInfo = self::info();

        return "USER: #" . $userInfo['id'];
    }

    public static final function isPriceShow()
    {
        $userInfo = self::info();

        return $userInfo['price_show'] === 1 ? true : false;
    }

    public static final function getCurrentPermission()
    {
        $userInfo = self::info();

        return $userInfo['permissions'];
    }

    public static final function getCurrentRoleId()
    {
        $userInfo = self::info();

        return $userInfo['role_id'];
    }

    public static final function getCurrentRoleCode()
    {
        $userInfo = self::info();

        return $userInfo['role_code'];
    }

    public static final function getCurrentRoleName()
    {
        $userInfo = self::info();

        return $userInfo['role_name'];
    }

    public static final function urlBase($url = null)
    {
        $base = env("APP_URL");
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $base = $_SERVER['HTTP_REFERER'];
        } elseif (!empty($_SERVER['HTTP_ORIGIN'])) {
            $base = $_SERVER['HTTP_ORIGIN'];
        }
        $base = $base . $url;
        $base = str_replace(" ", "", $base);
        $base = str_replace("\\", "/", $base);
        $base = str_replace("//", "/", $base);
        $base = str_replace(":/", "://", $base);

        return $base;
    }

    public static function allowRemote()
    {
        $allowRemote = env("APP_ALLOW_REMOTE", false);
        if ($allowRemote == true) {
            return true;
        }

        $remoteAddr = $_SERVER["REMOTE_ADDR"] ?? "";
        $httpOrigin = $_SERVER["HTTP_ORIGIN"] ?? "";
        if (!$remoteAddr || !$httpOrigin) {
            return false;
        }

        $clientDomain = strtolower(trim(str_replace(" ", "", $httpOrigin)));
        $clientIp     = $_SERVER["SERVER_ADDR"];

        $remote = DB::table('settings')->select(['id', 'name'])->where('code', 'REMOTE')->first();
        if (!$remote) {
            return false;
        }

        if (empty($remote->name)) {
            return false;
        }

        $remote = json_decode($remote->name, true);
        if (!$remote || !is_array($remote)) {
            return false;
        }

        foreach ($remote as $item) {
            if ($item["ip"] == $clientIp && $item["domain"] == $clientDomain) {
                return true;
            }
        }

        return false;
    }

    public static final function array_get($array, $key, $default = null)
    {
        if (!Arr::accessible($array)) {
            return value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (Arr::exists($array, $key)) {
            if ($array[$key] === "" || $array[$key] === null) {
                return $default;
            }
            return $array[$key];
        }

        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $segment) {
            if (Arr::accessible($current) && Arr::exists($current, $segment)) {
                $current = $current[$segment];
            } else {
                return value($default);
            }
        }

        return $current;
    }
}
