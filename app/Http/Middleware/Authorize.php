<?php

namespace App\Http\Middleware;

use App\RolePermission;
use App\Supports\Message;
use App\User;
use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\SERVICE;
use Illuminate\Support\Arr;
use Laravel\Lumen\Routing\Router;

class Authorize
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var
     */
    protected $permissions;

    protected $roleId;

    /**
     * Authorize constructor.
     */
    public function __construct(Router $router)
    {
        if (!SERVICE::allowRemote()) {
            throw new AccessDeniedHttpException(Message::get('remote_denied'));
        }
        $this->router = $router;
        // $this->roleId = SERVICE::getCurrentRoleId();
    }

    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // User is Admin, then bypass checking permission
        if (SERVICE::isAdminUser()) {
            return $next($request);
        }
        ///////////////////////////// BỎ PERMISSTION
        return $next($request);
        /////////////////////////////

        // $permissions = [];
        // if (!empty($this->roleId)) {
        //     $currentPermissions = RolePermission::with(['permission'])
        //         ->where('role_id', $this->roleId)->get()->toArray();
        //     $permissions        = array_pluck($currentPermissions, null, 'permission.code');
        // }
        // $action = Arr::get($this->router->getRoutes()[$request->method() . $request->getPathInfo()]['action'], 'action', null);

        // if (!$action) {
        //     return $next($request);
        // }
        // if (empty($permissions[$action])) {
        //     throw new AccessDeniedHttpException(Message::get("no_permission"));
        // }
        // return $next($request);
    }
}
