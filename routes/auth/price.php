<?php
$api->get('/get_price', 'PriceController@getPrices');
$api->post('/create_price', 'PriceController@createPrice');
$api->get('/get_price_by_id/{id}', 'PriceController@getPriceById');
$api->put('/update_price/{id}', 'PriceController@updatePrice');
$api->delete('/remove_price/{id}', 'PriceController@removePrice');