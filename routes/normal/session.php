<?php
$api->get('/set_session', 'SessionController@setSession');
$api->delete('/remove_session', 'SessionController@removeSession');