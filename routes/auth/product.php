<?php
$api->get('/get_products', 'ProductController@getProducts');
$api->post('/create_product', 'ProductController@createProduct');
$api->get('/get_product_by_id/{id}', 'ProductController@getProductById');
$api->put('/update_product/{id}', 'ProductController@updateProduct');
$api->delete('/remove_product/{id}', 'ProductController@removeProduct');
$api->get('/get_product_by_category_id/{id}', 'ProductController@getProductByCategoryId');
$api->get('/get_products_cate', 'ProductController@getProductsCate');

// $api->post('/add_to_cart', 'CartController@addToCart');
// $api->put('/update_cart/{id}', 'CartController@updateCart');
// $api->delete('/remove_cart/{id}', 'CartController@removeCart');
// $api->put('/update_cart_detail/{id}', 'CartController@updateCartDetail');
// $api->get('/get_cart', 'CartController@getCart');
// $api->post('/add_coupon', 'CartController@addCoupon');
// $api->delete('/remove_coupon/{id}', 'CartController@removeCoupon');
// $api->post('/add_voucher', 'CartController@addVoucher');
// $api->delete('/remove_voucher/{id}', 'CartController@removeVoucher');
// $api->post('/add_cart_to_user', 'CartController@addCartToUser');



