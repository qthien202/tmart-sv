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

$api->get('/users', [
    'action' => 'VIEW-USER',
    'uses'   => 'UserController@search',
]);

$api->get('/user/{id:[0-9]+}', [
    'action' => 'VIEW-USER',
    'uses'   => 'UserController@view',
]);

$api->put('/users/change-password', [
    'action' => 'UPDATE-USER-PASSWORD',
    'uses'   => 'UserController@changePassword',
]);

$api->post('/user', [
    'action' => 'UPDATE-USER',
    'uses'   => 'UserController@create',
]);

$api->put('/user/{id:[0-9]+}', [
    'action' => 'UPDATE-USER',
    'uses'   => 'UserController@update',
]);

$api->delete('/user/{id:[0-9]+}', [
    'action' => 'DELETE-USER',
    'uses'   => 'UserController@delete',
]);
