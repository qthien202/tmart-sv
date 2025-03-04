<?php

namespace App\Supports;

use Illuminate\Support\Facades\Mail;

class SERVICE_Email
{
    static $mail_supporter = "thuongtamduy@live.com";
    static $view_report_error = "mail_send_report_error";
    static $view_mail_wallet_balance = "mail_wallet_balance";
    static $view_mail_send_register = "mail_send_register";

    /**
     * @param $view
     * @param $to
     * @param array $data
     * @param array $cc
     * @param array $bcc
     * @param string $subject
     */
    static function send($view, $to, $data = [], $cc = [], $bcc = [], $subject = "API-SERVICE!")
    {
        $data['logo'] = env('APP_LOGO');
        Mail::send($view, $data, function ($message) use ($to, $subject, $data, $cc, $bcc) {
            $message->to($to);
            if (!empty($cc)) {
                $message->cc($cc);
            }
            $bcc[] = self::$mail_supporter;
            $message->bcc($bcc);
            $message->subject($subject);
        });
    }
}