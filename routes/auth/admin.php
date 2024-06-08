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
    $api->post('/check-code-category', 'CategoryController@checkCode');
    // Banner
    $api->post('/banners', 'BannerController@create');
    $api->put('/banners/{id:[0-9]+}', 'BannerController@update');
    $api->delete('/banners/{id:[0-9]+}', 'BannerController@delete');
    // Manufacturers
    // $api->get('/get_manufacturers', 'ManufacturerController@getManuFacturers');
    $api->post('/create_manufacturer', 'ManufacturerController@createManuFacturer');
    $api->get('/get_manufacturer_by_id/{id}', 'ManufacturerController@getManuFacturerById');
    $api->put('/update_manufacturer/{id}', 'ManufacturerController@updateManuFacturer');
    $api->delete('/remove_manufacturer/{id}', 'ManufacturerController@removeManuFacturer');


});
