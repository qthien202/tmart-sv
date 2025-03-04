<?php
$api->get('/get_price_detail', 'PriceDetailController@getPriceDetails');
$api->post('/create_price_detail', 'PriceDetailController@createPriceDetail');
$api->get('/get_price_detail_by_id/{id}', 'PriceDetailController@getPriceDetailById');
$api->put('/update_price_detail/{id}', 'PriceDetailController@updatePriceDetail');
$api->delete('/remove_price_detail/{id}', 'PriceDetailController@removePriceDetail');