<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Supports\Message;
use Laravel\Lumen\Routing\Controller;

class BaseController extends Controller
{
    
    /**
     * @param null $msg
     * @param int $code
     *
     * @return \Illuminate\Http\JsonResponse
     */

    protected function responseError($msg = null, $code = 400)
    {
        $msg = $msg ? $msg : Message::get("V1001");
        return response()->json(['status' => 'error', 'error' => ['errors' => ["msg" => $msg]]], $code);
    }
    protected function responseSuccess($msg = null, $data = null, $statusCode = 200) {
        if ($data) {
            $response["data"] = $data;
        }
        if ($msg) {
            $response["message"] = $msg;
        }
        return response()->json($response, $statusCode);
    }

}