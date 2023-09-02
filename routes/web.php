<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version() . " - API-SERVICE - ON DEVICE: " . get_device() . " - PHP VERSION: " . phpversion();
});

// Authorization
$router->group(['prefix' => 'auth', 'namespace' => 'Auth', 'middleware' => ['cors', 'trimInput']], function ($router) {
    // Auth
    $router->post('/login', "AuthController@authenticate");
    $router->post('/register', "AuthController@register");
    $router->post('/verify-register', "AuthController@verifyRegister");
    $router->get('/logout', "AuthController@logout");
    $router->get('/forget-password', "AuthController@forgetPassword");
    $router->post('/reset-password', "AuthController@resetPassword");
});

$api = app('Laravel\Lumen\Routing\Router');

// Normal API
require __DIR__ . '/normal/api_route.php';
// Authorize API
require __DIR__ . '/auth/api_route.php';
