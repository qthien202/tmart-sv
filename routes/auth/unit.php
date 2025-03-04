<?php
$api->get('/get_units', 'UnitController@getUnits');
$api->post('/create_unit', 'UnitController@createUnit');
$api->get('/get_unit_by_id/{id}', 'UnitController@getUnitById');
$api->put('/update_unit/{id}', 'UnitController@updateUnit');
$api->delete('/remove_unit/{id}', 'UnitController@removeUnit');