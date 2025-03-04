<?php

use App\Supports\SERVICE_Excel;
use Illuminate\Http\Request;

$api->post('/excel/read-file', function (Request $request) {
    return SERVICE_Excel::readExcel($request);
});