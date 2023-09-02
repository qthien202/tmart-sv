<?php

namespace App\Supports;

use App\Jobs\SendMailErrorJob;
use App\SERVICE;
use Dingo\Api\Http\Response;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Predis\ClientException;

class SERVICE_Error
{
    public static function handle(\Exception $ex)
    {
        $errorCode = $ex->getCode();
        $errorCode = empty($errorCode) ? HttpResponse::HTTP_BAD_REQUEST : $errorCode;

        if (env('APP_DEBUG', false) == true) {
            $request = Request::capture();
            $param   = $request->all();
            $data    = [
                'server'  => SERVICE::urlBase(),
                'time'    => date("Y-m-d H:i:s", time()),
                'user_id' => SERVICE::getCurrentUserId(),
                'param'   => json_encode($param),
                'file'    => $ex->getFile(),
                'line'    => $ex->getLine(),
                'error'   => $ex->getMessage(),
            ];
            // Send Mail
            // dispatch(new SendMailErrorJob($data));
            // self::sendMessage(json_encode($data));
        }

        if (env('APP_ENV') == 'testing') {
            return ['message' => $ex->getMessage(), 'file' => $ex->getFile(), 'line' => $ex->getLine(), 'code' => $errorCode];
        } else {
            return ['message' => Message::get("R011"), 'code' => HttpResponse::HTTP_BAD_REQUEST];
        }
    }

    static function sendMessage($message)
    {
        try {
            $token = env('TELEGRAM_BOT_TOKEN');
            $chat_id = env('TELEGRAM_CHAT_ID');
            $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat_id;
            $client = new Client();
            $response = $client->get($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['text' => $message])
            ]);
            $response = $response->getBody();
            $response = !empty($response) ? json_decode($response, true) : [];
            return $response;
        } catch (ClientException $ex) {
        }
    }
}
