<?php
$api->get('/check_admin', 'AdminController@checkAdmin');

$api->group(['middleware' => ['cors', 'trimInput', 'verifySecret', 'authorize','admin']], function ($api) {
    $api->get('/admin', 'AdminController@statistical');
    // Product
    $api->post('/create_product', 'ProductController@createProduct');
    $api->put('/update_product/{id}', 'ProductController@updateProduct');
    $api->delete('/remove_product/{id}', 'ProductController@removeProduct');
    // Category
    $api->post('/categories', 'CategoryController@create');
    $api->put('/categories/{id:[0-9]+}', 'CategoryController@update');
    $api->delete('/categories/{id:[0-9]+}', 'CategoryController@delete');
    // Banner
    $api->post('/banners', 'BannerController@create');
    $api->put('/banners/{id:[0-9]+}', 'BannerController@update');
    $api->delete('/banners/{id:[0-9]+}', 'BannerController@delete');



});
