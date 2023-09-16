<?php
$api->post('/add_to_cart', 'CartController@addToCart');
$api->put('/update_cart/{id}', 'CartController@updateCart');
$api->delete('/remove_cart/{id}', 'CartController@removeCart');
$api->delete('/remove_cart_detail/{id}', 'CartController@removeCartDetail');
$api->put('/update_cart_detail/{id}', 'CartController@updateCartDetail');
$api->get('/get_cart', 'CartController@getCart');
$api->post('/add_coupon', 'CartController@addCoupon');
$api->get('/get_coupons', 'CartController@getCoupons');
$api->get('/get_vouchers', 'CartController@getVouchers');
$api->delete('/remove_coupon/{id}', 'CartController@removeCoupon');
$api->post('/add_voucher', 'CartController@addVoucher');
$api->delete('/remove_voucher/{id}', 'CartController@removeVoucher');
$api->post('/add_cart_to_user', 'CartController@addCartToUser');



