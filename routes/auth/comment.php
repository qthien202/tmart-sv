<?php
// Order
$api->get('/get_comments', 'CommentController@getComments');
$api->post('/add_comment', 'CommentController@addComment');
$api->get('/get_image_product', 'CommentController@getImgsFromProductId');
// $api->get('/get_order_by_user_id', 'OrderController@getOrderByUserID');
// $api->get('/get_order_by_id/{id}', 'OrderController@getOrderById');
// $api->put('/update_order_status/{id}', 'OrderController@updateOrderStatus');
// $api->put('/update_order/{id}', 'OrderController@updateOrder');
// $api->put('/cancel_order/{id}', 'OrderController@cancelOrder');
// $api->put('/update_order_detail/{id}', 'OrderController@updateOrderDetail');
// $api->delete('/remove_order/{id}', 'OrderController@removeOrder');
