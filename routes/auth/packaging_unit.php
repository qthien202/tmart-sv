<?php
$api->get('/get_packaging_units', 'PackagingUnitController@getPackagingUnits');
$api->post('/create_packaging_unit', 'PackagingUnitController@createPackagingUnit');
$api->get('/get_packaging_unit_by_id/{id}', 'PackagingUnitController@getPackagingUnitById');
$api->put('/update_packaging_unit/{id}', 'PackagingUnitController@updatePackagingUnit');
$api->delete('/remove_packaging_unit/{id}', 'PackagingUnitController@removePackagingUnit');