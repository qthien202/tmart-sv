<?php


namespace App\Jobs;


use App\Supports\SERVICE_Email;

class SendMailRegister extends Job
{
    protected $data;
    protected $to;

    public function __construct($to, $data)
    {
        $this->data = $data;
        $this->to   = $to;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data    = $this->data;
        $to      = $this->to;
        $subject = "Thông báo đăng ký thành viên";
        try {
            SERVICE_Email::send(SERVICE_Email::$view_mail_send_register, $to, $data, null, null, $subject);
        }
        catch (\Exception $ex) {
            echo $ex->getMessage();
            die;
        }
    }
}