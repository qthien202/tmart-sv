<?php
$api->get('/vietnam-list-district/json', [
    'action' => '',
    'uses' => 'VietNamListDistrictController@getJson',
]);

$api->get('/vietnam-list-district/xls', [
    'action' => '',
    'uses' => 'VietNamListDistrictController@getXls',
]);

$api->get('/vietnam-list-district/provinces', [
    'action' => '',
    'uses' => 'VietNamListDistrictController@getProvince',
]);

$api->get('/vietnam-list-district/{provinceCode}/districts', [
    'action' => '',
    'uses' => 'VietNamListDistrictController@getDistrict',
]);

$api->get('/vietnam-list-district/{districtCode}/wards', [
    'action' => '',
    'uses' => 'VietNamListDistrictController@getWard',
]);
