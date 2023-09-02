<?php


require_once __DIR__ . '/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();


date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->instance('path.public', app()->basePath() . DIRECTORY_SEPARATOR . 'public');
$app->instance('path.config', app()->basePath() . DIRECTORY_SEPARATOR . 'config');
$app->instance('path.storage', app()->basePath() . DIRECTORY_SEPARATOR . 'storage');

$app->withFacades();
$app->withEloquent();
// $app->configure('cors');
$app->configure('jwt');
$app->configure('constants');
$app->configure('messages');
$app->configure('mail');
$app->configure('import');
$app->configure('validation');
$app->configure('queue');
$app->configure('filesystems');
$app->configure('excel');
/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Filesystem\Factory::class,
    function ($app) {
        return new Illuminate\Filesystem\FilesystemManager($app);
    }
);

$app->singleton('filesystem', function ($app) {
    return $app->loadComponent(
        'filesystems',
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        'filesystem'
    );
});

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

$app->configure('app');

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    App\Http\Middleware\CorsMiddleware::class
]);


$app->routeMiddleware([
    'auth'         => App\Http\Middleware\Authenticate::class,
    'cors'         => App\Http\Middleware\CorsMiddleware::class,
    'verifySecret' => App\Http\Middleware\VerifySecret::class,
    'trimInput'    => App\Http\Middleware\TrimInput::class,
    'authorize'    => App\Http\Middleware\Authorize::class
]);

// if (!class_exists('JWTAuth')) {
//    class_alias('Tymon\JWTAuth\Facades\JWTAuth', 'JWTAuth');
// }

// if (!class_exists('JWTFactory')) {
//    class_alias('Tymon\JWTAuth\Facades\JWTFactory', 'JWTFactory');
// }

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Illuminate\Database\Eloquent\LegacyFactoryServiceProvider::class);
//$app->register(\Illuminate\Mail\MailServiceProvider::class);
$app->register(Maatwebsite\Excel\ExcelServiceProvider::class);
$app->register(\Illuminate\Redis\RedisServiceProvider::class);
$app->register(Jenssegers\Agent\AgentServiceProvider::class);
class_alias(Jenssegers\Agent\Facades\Agent::class, 'Agent');
class_alias(Maatwebsite\Excel\Facades\Excel::class, "Excel");

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/web.php';
});

return $app;
