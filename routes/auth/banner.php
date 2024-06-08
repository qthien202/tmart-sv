<?php
$api->get('/banners', 'BannerController@search');
$api->get('/banners/{id:[0-9]+}', 'BannerController@detailById');
$api->get('/banners/{code}', 'BannerController@detailByCode');
// $api->post('/banners', 'BannerController@create');
// $api->put('/banners/{id:[0-9]+}', 'BannerController@update');
// $api->delete('/banners/{id:[0-9]+}', 'BannerController@delete');