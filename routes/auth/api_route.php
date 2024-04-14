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

// Authorized Group
$api->group(['prefix' => 'api/auth', 'namespace' => 'V1\Auth\Controllers', 'middleware' => ['cors', 'trimInput', 'verifySecret', 'authorize',/*'tokenStore'*/]], function ($api) {

    $api->options('/{any:.*}', function () {
        return response(['status' => 'success'])
            ->header('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, DELETE')
            ->header('Access-Control-Allow-Headers', 'Authorization, Content-Type, Origin');
    });

    $api->get('/', function () {
        return ['status' => 'AUTH API OK!'];
    });

    // Users
    require __DIR__ . '/user.php';

    // Role
    require __DIR__ . '/role.php';

    // Permissions
    require __DIR__ . '/permission.php';

    // Permission Group
    require __DIR__ . '/permission_group.php';

    // User Log
    require __DIR__ . '/user_log.php';

    // Banner
    require __DIR__ . '/banner.php';
    
    // Category
    require __DIR__ . '/category.php';

    // Product
    require __DIR__ . '/product.php';

    // Unit
    require __DIR__ . '/unit.php';

    // Manufacturer
    require __DIR__ . '/manufacturer.php';
    
    // Packaging Unit
    require __DIR__ . '/packaging_unit.php';

    // Price
    require __DIR__. '/price.php';

    // Price Detail
    require __DIR__. '/price_detail.php';

    // Order
    require __DIR__. '/order.php';

    // Shipping company
    require __DIR__ . '/shipping_company.php';

    // Shipping company
    require __DIR__ . '/addressbook.php';

    // VNPAY
    require __DIR__ . '/vnpay.php';

    // Comment
    require __DIR__ . '/comment.php';
});
