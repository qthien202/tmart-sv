<?php
$api->get('/get_address_all_books', 'AddressBookController@getAddressAllBooks');
$api->get('/get_address_books', 'AddressBookController@getAddressBooks');
$api->post('/create_address_books', 'AddressBookController@createAddressBook');
$api->put('/update_address_books/{id}', 'AddressBookController@updateAddressBook');
$api->post('/confirm_address_books', 'AddressBookController@confirmAddress');

$api->post('/set_default_address', 'AddressBookController@setDefault');