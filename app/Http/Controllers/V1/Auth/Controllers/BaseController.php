<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\SERVICE;
use App\Supports\Message;
use App\UserSession;
use Laravel\Lumen\Routing\Controller;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Message\Topics;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use Tymon\JWTAuth\Facades\JWTAuth;

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
    // Bắn thông báo từ firebase ra thiết bị di động theo topic
    protected function notificationToTopic($title,$content){
        
        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($content)
                            ->setSound('default');

        $notification = $notificationBuilder->build();

        $topic = new Topics();
        $topic->topic('notifications');

        $topicResponse = FCM::sendToTopic($topic, null, $notification, null);

        $topicResponse->isSuccess();
        $topicResponse->shouldRetry();
        $topicResponse->error();

    }
    protected function notificationToDevice($title,$content){
        $tokenUser = JWTAuth::getToken(); //XEM LẠI
        $token = UserSession::where('token',$tokenUser)->value('device_id'); //XEM LẠI
        if (empty($token)) {
            return;
        }

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($content)
                            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data']);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();
        // dd($downstreamResponse);
        // return Array - you must remove all this tokens in your database
        $downstreamResponse->tokensToDelete();

        // return Array (key : oldToken, value : new token - you must change the token in your database)
        $downstreamResponse->tokensToModify();

        // return Array - you should try to resend the message to the tokens in the array
        $downstreamResponse->tokensToRetry();

        // return Array (key:token, value:error) - in production you should remove from your database the tokens
        $downstreamResponse->tokensWithError();

    }

}