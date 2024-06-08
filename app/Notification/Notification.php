<?php

namespace App\Notification;

use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Message\Topics;

class Notification{
    public function send($title,$content){
        
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
}