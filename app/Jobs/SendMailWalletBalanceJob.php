<?php

namespace App\Jobs;

use App\Supports\SERVICE_Email;

class SendMailWalletBalanceJob extends Job
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        echo "Sending!";
        $data = $this->data;
        SERVICE_Email::send(SERVICE_Email::$view_mail_wallet_balance, SERVICE_Email::$mail_supporter, $data, [], null, '[Notification]SERVICE');
        echo "Sent!";
        return;
    }
}
