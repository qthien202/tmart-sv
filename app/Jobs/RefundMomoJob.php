<?php

namespace App\Jobs;

use App\LoginMomo;
use App\Momo;
use App\MomoHistory;
use App\Supports\SERVICE_Error;
use GuzzleHttp\Client;

class RefundMomoJob extends Job
{
    protected $model;
    protected $data;

    public function __construct($model, $data)
    {
        $this->model = $model;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response    = $this->momoRefund($this->data);
        if ($response == 200) {
            $this->model->completed = 1;
            $this->model->save();
        }
    }

    private function momoRefund($param)
    {
        if (env('API_MOMO_MODE') != 'live') {
            return 200;
        }
        if (empty($param)) {
            return null;
        }
        try {
            $phone = [
                env('API_MOMO_PHONE')   => env('API_MOMO_PHONE'),
                env('API_MOMO_PHONE_1') => env('API_MOMO_PHONE'),
            ];

            $config   = LoginMomo::where('phone', $phone[$param['receiverNumber']])->select('phash', 'phone')->first();
            $client   = new Client([
                'headers' => [
                    'Content-type' => 'application/json'
                ],
                'verify'  => false
            ]);

            $response = $client->post(
                env('API_MOMO_TRANSFER'),
                [
                    'body'            => json_encode(
                        [
                            'token'           => $config->phash,
                            'phone'           => $config->phone,
                            'receiver_number' => $param['phone'],
                            'amount'          => $param['cash'],
                            'comment'         => $param['comment']
                        ]
                    ),
                    'timeout'         => 10,
                    'connect_timeout' => 10
                ]
            );
            $result   = json_decode($response->getBody()->getContents(), true);
            $r        = $result['error'] != 0 ? 400 : $response->getStatusCode();
            MomoHistory::insert([
                'id'         => $param['id'],
                'phone'      => $param['phone'],
                'cash'       => $param['cash'],
                'status'     => $r,
                'message'    => $result['msg'],
                'content'    => json_encode($result),
                'created_at' => date("Y-m-d H:i:s", time()),
                'updated_at' => date("Y-m-d H:i:s", time())
            ]);
        } catch (\Exception $exception) {
            $response = SERVICE_Error::handle($exception);
            $r        = $response['code'];
            MomoHistory::insert([
                'id'         => $param['id'],
                'phone'      => $param['phone'],
                'cash'       => $param['cash'],
                'status'     => $response['code'],
                'message'    => $response['message'],
                'content'    => json_encode($exception),
                'created_at' => date("Y-m-d H:i:s", time()),
                'updated_at' => date("Y-m-d H:i:s", time())
            ]);
        }
        return $r;
    }
}
