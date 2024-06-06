<?php
$api->get('/check_admin', 'AdminController@checkAdmin');

$api->group(['middleware' => ['cors', 'trimInput', 'verifySecret', 'authorize','admin']], function ($api) {
    $api->get('/admin', 'AdminController@statistical');
    // Product
    $api->post('/create_product', 'ProductController@createProduct');
    $api->put('/update_product/{id}', 'ProductController@updateProduct');
});
