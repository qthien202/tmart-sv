<?php
/**
 * Created by PhpStorm.
 * User: SangNguyen
 * Date: 5/13/2019
 * Time: 9:27 PM
 */

$api->get('/user-logs', [
    'action' => 'VIEW-LOG-USER',
    'uses'   => 'LogController@logUser',
]);