<?php
// Order
$api->get('/get_order', 'OrderController@getOrders');
$api->get('/get_order_by_user_id', 'OrderController@getOrderByUserID');
$api->post('/confirm_order', 'OrderController@confirmOrder');
$api->get('/get_order_by_id/{id}', 'OrderController@getOrderById');
$api->put('/update_order_status/{id}', 'OrderController@updateOrderStatus');
$api->put('/update_order/{id}', 'OrderController@updateOrder');
$api->put('/cancel_order/{id}', 'OrderController@cancelOrder');
$api->put('/update_order_detail/{id}', 'OrderController@updateOrderDetail');
$api->delete('/remove_order/{id}', 'OrderController@removeOrder');

// Order shippings
$api->get('/get_order_shippings', 'OrderShippingController@getOrderShippings');
$api->post('/create_order_shipping', 'OrderShippingController@createOrderShipping');
$api->get('/get_order_shipping_by_id/{id}', 'OrderShippingController@getOrderShippingById');
$api->put('/update_order_shipping/{id}', 'OrderShippingController@updateOrderShipping');
$api->delete('/remove_order_shipping/{id}', 'OrderShippingController@removeOrderShipping');

// Order history
$api->get('/get_order_history', 'OrderHistoryController@getOrderHistory');
$api->post('/create_order_history', 'OrderHistoryController@createOrderHistory');
$api->get('/get_order_history_by_id/{id}', 'OrderHistoryController@getOrderHistoryById');
$api->put('/update_order_history/{id}', 'OrderHistoryController@updateOrderHistory');
$api->delete('/remove_order_history/{id}', 'OrderHistoryController@removeOrderHistory');

// Order payment
$api->get('/get_order_payments', 'OrderPaymentController@getOrderPayments');
$api->post('/create_order_payment', 'OrderPaymentController@createOrderPayment');
$api->get('/get_order_payment_by_id/{id}', 'OrderPaymentController@getOrderPaymentById');
$api->put('/update_order_payment/{id}', 'OrderPaymentController@updateOrderPayment');
$api->delete('/remove_order_payment/{id}', 'OrderPaymentController@removeOrderPayment');

// Order promotion
$api->get('/get_order_promotions', 'OrderPromotionController@getOrderPromotions');
$api->post('/create_order_promotion', 'OrderPromotionController@createOrderPromotion');
$api->get('/get_order_promotion_by_id/{id}', 'OrderPromotionController@getOrderPromotionById');
$api->put('/update_order_promotion/{id}', 'OrderPromotionController@updateOrderPromotion');
$api->delete('/remove_order_promotion/{id}', 'OrderPromotionController@removeOrderPromotion');

// Order status
$api->get('/get_order_status', 'OrderStatusController@getOrderStatus');
$api->post('/create_order_status', 'OrderStatusController@createOrderStatus');
$api->get('/get_order_status_by_id/{id}', 'OrderStatusController@getOrderStatusById');
$api->put('/update_order_status/{id}', 'OrderStatusController@updateOrderStatus');
$api->delete('/remove_order_status/{id}', 'OrderStatusController@removeOrderStatus');

// Shipping companies
$api->get('/get_shipping_companies', 'ShippingCompanyController@getShippingCompanies');
$api->post('/create_shipping_company', 'ShippingCompanyController@createShippingCompany');
$api->get('/get_shipping_company_by_id/{id}', 'ShippingCompanyController@getShippingCompanyById');
$api->put('/update_shipping_company/{id}', 'ShippingCompanyController@updateShippingCompany');
$api->delete('/remove_shipping_company/{id}', 'ShippingCompanyController@removeShippingCompany');