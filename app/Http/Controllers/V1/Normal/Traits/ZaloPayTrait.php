<?php

namespace App\Http\Controllers\V1\Normal\Traits;

use App\ZaloPayAccount;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait ZaloPayTrait
{
    /**
     * @param $data
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendOTPTrait($data)
    {
        $phone = $data['phone'];
        $this->phoneStatus($phone); // Save info
        $data = ZaloPayAccount::where('phone', $phone)->first();
        if (empty($data) || $data->status == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản không tồn tại hoặc đã bị khóa',
                'data' => null
            ], 200);
        }

        $this->sendOtpProcess($data);

        return $data;
    }
    /**
     *
     * @param $phone
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function phoneStatus($phone)
    {
        try {
            try {
                $url = "https://api.zalopay.vn/v2/account/phone/status?phone_number=" . $phone;
                // Send with GuzzleClient
                $client = new Client();
                $response = $client->request('GET', $url, [
                    'timeout' => 5, // Response timeout
                    'connect_timeout' => 5, // Connection timeout
                ]);
                $response = $response->getBody()->getContents();
                $responseData = json_decode($response, true);
            } catch (\GuzzleHttp\Exception $e) {
                return [
                    'status' => false,
                    'message' => 'Lỗi hệ thống',
                    'data' => null,
                    'error' => $e->getMessage()
                ];
            }

            if (!empty($responseData['error'])) {
                return $responseData['error'];
            }
            // Rankdom string input length
            $length = 10;
            $responseData['data']['deviceid'] = $this->generateImei();
            $responseData['data']['appversion'] = '8.5.0';

            // $data = ZaloPayAccount::where('phone', $phone)->first();
            // if (!empty($data)) {
            //     $data->update([
            //         'info' => json_encode($responseData['data'], true),
            //         'status' => ($responseData['data']['is_exist'] == true && $responseData['data']['is_locked'] == false) ? 1 : 0,
            //         'key'    => substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length)
            //     ]);
            //     return $responseData['data'];
            // }
            // if (empty($data)) {
            //     $data = new ZaloPayAccount();
            //     $data->phone = $phone;
            //     $data->info = json_encode($responseData['data'], true);
            //     $data->status = ($responseData['data']['is_exist'] == true && $responseData['data']['is_locked'] == false) ? 1 : 0;
            //     $data->key = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
            //     $data->save();
            //     return $responseData['data'];
            // }
            ZaloPayAccount::updateOrInsert(
                ['phone' => $phone],
                [
                    'info' => json_encode($responseData['data'], true),
                    'status' => ($responseData['data']['is_exist'] == true && $responseData['data']['is_locked'] == false) ? 1 : 0,
                    'key'    => substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length)
                ]
            );
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $responseData['data'];
    }

    private function sendOtpProcess($data)
    {
        $dataInfo = $data->info;
        $phone = $dataInfo['phone_number'];
        $send_otp_token =  $dataInfo['send_otp_token'];
        $deviceid = $dataInfo['deviceid'];
        $appversion = $dataInfo['appversion'];
        $url = "https://api.zalopay.vn/v2/account/otp";
        $header = [
            "x-platform: NATIVE",
            "x-device-os: ANDROID",
            "x-device-id: $deviceid",
            "x-device-model: OnePlus IN2025",
            "x-app-version: $appversion",
            "x-density: xhdpi",
            "Authorization: Bearer",
            "Content-Type: application/json; charset=UTF-8",
            "X-DRSite: off",
            "Host: api.zalopay.vn",
            "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . ' ZaloPay Android / 9464',
        ];
        $body = [
            'phone_number' => $phone,
            'send_otp_token' => $send_otp_token,
            'provider' => 0
        ];
        $client = new Client();
        $response = $client->request('POST', $url, [
            'headers' => $header,
            'body' => json_encode($body)
        ]);
        $response = $response->getBody()->getContents();
        $responseData = json_decode($response, true);
        if (!empty($responseData['error'])) {
            return $responseData['error'];
        }
        //Push verify_otp_token to info
        $newInfo = Arr::add($dataInfo, 'verify_otp_token', $responseData['data']['verify_otp_token']);
        $data->info = $newInfo;
        $data->save();
        return $responseData['data'];
    }

    public function verificationOTP($data)
    {
        $phone = $data['phone'];
        $otp = $data['otp'];
        $data = ZaloPayAccount::where('phone', $phone)->first();

        if (empty($data) || $data->status == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản không tồn tại hoặc đã bị khóa',
                'data' => null
            ], 200);
        }

        $dataInfo = $data->info;
        $verify_otp_token = $dataInfo['verify_otp_token'];
        $phone_number = $dataInfo['phone_number'];
        $deviceid = $dataInfo['deviceid'];
        $appversion = $dataInfo['appversion'];
        $url = "https://api.zalopay.vn/v2/account/otp-verification";
        $header = [
            "x-platform: NATIVE",
            "x-device-os: ANDROID",
            "x-device-id: $deviceid",
            "x-device-model: OnePlus IN2025",
            "x-app-version: $appversion",
            "x-density: xhdpi",
            "Authorization: Bearer",
            "Content-Type: application/json; charset=UTF-8",
            "X-DRSite: off",
            "Host: api.zalopay.vn",
            "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . ' ZaloPay Android / 9464',
        ];

        $body = [
            'phone_number' => $phone_number,
            'verify_otp_token' => $verify_otp_token,
            'otp' => $otp
        ];
        $client = new Client();
        $response = $client->request('POST', $url, [
            'headers' => $header,
            'body' => json_encode($body)
        ]);
        $response = $response->getBody()->getContents();
        $responseData = json_decode($response, true);
        if (!empty($responseData['error'])) {
            return $responseData['error'];
        }
        //Push phone_verified_token to info
        $newInfo = Arr::add($dataInfo, 'phone_verified_token', $responseData['data']['phone_verified_token']);
        $newInfo = Arr::add($newInfo, 'otp', $otp);
        $newInfo = Arr::add($newInfo, 'salt', json_decode($this->getSalt($deviceid), true)['data']['salt']);
        $newInfo = Arr::add($newInfo, 'public_key', json_decode($this->getPublicKey($deviceid), true)['data']['public_key']);
        $data->info = $newInfo;
        $data->save();
        return response()->json([
            'status' => true,
            'key' => $data->key
        ], 200);
    }

    public function getSalt($deviceid)
    {
        $headers = array(
            'x-device-id: ' . $deviceid . '',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.zalopay.vn/v2/user/salt");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);

        curl_close($ch);
        return $data;
    }

    public function getPublicKey($deviceid)
    {
        $headers = array(
            'x-device-id: ' . $deviceid . '',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.zalopay.vn/v2/user/public-key");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);

        curl_close($ch);
        return $data;
    }

    public function loginTrait($data)
    {
        $phone = $data['phone'];
        $key = $data['key'];
        $password = $data['password'];
        $data = ZaloPayAccount::where(['phone' => $phone, 'key' => $key])->first();
        if (empty($data) || $data->status == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản không tồn tại hoặc đã bị khóa',
                'data' => null
            ], 200);
        }
        if ($data->key != $key) {
            return response()->json([
                'status' => false,
                'message' => 'Key không đúng',
                'data' => null
            ], 200);
        }
        $dataInfo = $data->info;
        $phone_verified_token = $dataInfo['phone_verified_token'];
        $phone_number = $dataInfo['phone_number'];
        $encrypted_pin = hash('sha256', $password);

        $url = "https://api.zalopay.vn/v2/account/phone/session";

        $headers = [
            "x-platform: NATIVE",
            "x-device-os: ANDROID",
            "x-device-id: 445080c67f3e9254",
            "x-device-model: OnePlus IN2025",
            "x-app-version: 8.6.0",
            "x-density: xhdpi",
            "Authorization: Bearer",
            "Content-Type: application/json; charset=UTF-8",
            "X-DRSite: off",
            "Host: api.zalopay.vn",
            "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . ' ZaloPay Android / 9464',
        ];

        $body = [
            'phone_number' => $phone_number,
            'phone_verified_token' => $phone_verified_token,
            'encrypted_pin' => $encrypted_pin
        ];
        $client = new Client();
        $response = $client->request('POST', $url, [
            'headers' => $headers,
            'body' => json_encode($body)
        ]);
        $response = $response->getBody()->getContents();
        $responseData = json_decode($response, true);
        if (!empty($responseData['error'])) {
            return $responseData['error'];
        }

        //Push session_token to info
        $newInfo = Arr::add($dataInfo, 'encrypted_pin', $encrypted_pin);
        $newInfo = Arr::add($newInfo, 'password', $password);
        $newInfo = Arr::add($newInfo, 'session_id', $responseData['data']['session_id']);
        $newInfo = Arr::add($newInfo, 'display_name', $responseData['data']['display_name']);
        $newInfo = Arr::add($newInfo, 'phone_number', $responseData['data']['phone_number']);
        $newInfo = Arr::add($newInfo, 'access_token', $responseData['data']['access_token']);
        $newInfo = Arr::add($newInfo, 'user_id', $responseData['data']['user_id']);
        $data->info = $newInfo;
        $data->token = Str::random(64);
        $data->save();

        return response()->json([
            'status' => true,
            'message' => 'Đăng nhập thành công',
            'token' => $data->token,
        ], 200);
    }

    public function getBalanceTrait($data)
    {
        $token = $data['token'];
        $data = ZaloPayAccount::where(['token' => $token])->first();
        if (empty($data) || $data->status == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản không tồn tại hoặc đã bị khóa',
                'data' => null
            ], 200);
        }
        $dataInfo = $data->info;
        return $this->getCurrentBalance($dataInfo);
    }

    // public function getCurrentBalance($dataInfo)
    // {
    //     $headers = array(
    //         'Host: api.zalopay.vn',
    //         'x-platform: NATIVE',
    //         'x-device-os: ANDROID',
    //         'x-device-id: ' . $dataInfo['deviceid'],
    //         'x-device-model: Samsung SM_G532G',
    //         'x-access-token: ' . $dataInfo['access_token'],
    //         'x-zalo-id: ' . $dataInfo['zalo_id'],
    //         'x-zalopay-id:' . $dataInfo['user_id'],
    //         'x-user-id:' . $dataInfo['user_id'],
    //         'x-app-version: ' . $dataInfo['appversion'],
    //         'user-agent: ' . $_SERVER['HTTP_USER_AGENT'] . ' ZaloPay Android / 9464',
    //         'x-density: hdpi',
    //         'authorization: Bearer ' . $dataInfo['access_token']
    //     );
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, "https://api.zalopay.vn/v2/user/balance");
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_HEADER, 0);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     $data = curl_exec($ch);

    //     curl_close($ch);
    //     if (is_object(json_decode($data))) {
    //         return json_decode($data, true);
    //     }
    //     return $data;
    // }

    private function getCurrentBalance($dataInfo)
    {
        $access_token = $dataInfo['access_token'];
        $deviceid = $dataInfo['deviceid'];
        $appversion = $dataInfo['appversion'];
        $zalo_id = $dataInfo['zalo_id'];
        $user_id = $dataInfo['user_id'];

        $url = "https://sapi.zalopay.vn/v2/user/balance";

        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'x-device-id' => $deviceid,
            'x-device-os' => 'android',
            'x-device-model' => 'OnePlus IN2025',
            'x-platform' => 'NATIVE',
            'x-app-version' => $appversion,
            'x-access-token' => $access_token,
            'x-zalo-id' => $zalo_id,
            'x-zalopay-id' => $user_id,
            'x-user-id' => $user_id,
            'User-Agent' => $_SERVER['HTTP_USER_AGENT'] . ' ZaloPay Android / 9464',
            'X-DRSite' => 'off',
            'Host' => 'sapi.zalopay.vn',
            'Connection' => 'Keep-Alive',
            'Accept-Encoding' => 'gzip'
        ];
        $request = $client->request('GET', $url, [
            'headers' => $headers
        ]);

        $response = $request->getBody()->getContents();
        $responseData = json_decode($response, true);
        if (!empty($responseData['error'])) {
            return $responseData['error'];
        }
        return $responseData['data'];
    }

    public function sendMoneyTrait($input)
    {
        $token = $input['token'];
        $data = ZaloPayAccount::where(['token' => $token])->first();
        if (empty($data) || $data->status == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản không tồn tại hoặc đã bị khóa',
                'data' => null
            ], 200);
        }
        $dataInfo = $data->info;
        $balance = $this->getCurrentBalance($dataInfo);
        if ($balance['balance'] < $input['amount']) {
            return response()->json([
                'status' => false,
                'message' => 'Số dư không đủ',
                'data' => null
            ], 200);
        }
        $paymentProcess = $this->paymentProcess($data, $input, $dataInfo);
        if(($paymentProcess['data']['is_processing'] ?? null) == 1){
            return response()->json([
                'status' => true,
                'message' => 'Gửi tiền thành công',
            ], 200);
        }
        return response()->json([
            'status' => true,
            'message' => 'Gửi tiền thất bại',
        ], 200);
    }

    private function paymentProcess($data, $input, $dataInfo)
    {
        $result = $this->cashierAssets($data, $input, $dataInfo);
        $this->verifyPin($dataInfo);
        $resultPay = $this->cashierPay($result, $dataInfo);
        return $resultPay;
    }

    private function cashierAssets($model, $data, $dataInfo)
    {
        $access_token = $dataInfo['access_token'];
        $zalo_id = $dataInfo['zalo_id'];
        $user_id = $dataInfo['user_id'];
        $appversion = $dataInfo['appversion'];
        $deviceid = $dataInfo['deviceid'];
        $result = $this->createOrder($model, $data, $dataInfo);

        // $url = "https://api.zalopay.vn/v2/cashier/assets";

        // $headers = [
        //     'Host' => 'api.zalopay.vn',
        //     'x-platform' => 'NATIVE',
        //     'x-device-os' => 'ANDROID',
        //     'x-device-id' => $deviceid,
        //     'x-device-model' => 'OnePlus IN2025',
        //     'x-app-version' => $appversion,
        //     'User-Agent' => $_SERVER['HTTP_USER_AGENT'] . ' ZaloPay Android / 9464',
        //     'Authorization' => 'Bearer ' . $access_token,
        //     'x-access-token' => $access_token,
        //     'x-zalo-id' => $zalo_id,
        //     'x-zalopay-id' => $user_id,
        //     'x-user-id' => $user_id,
        //     'x-density: hdpi',
        //     'Cookie' => 'zalo_id=' . $zalo_id . '; zlp_token=' . $access_token . '; has_device_id=0; X-DRSITE=off',
        // ];

        // $body = [
        //     "order_type"=> "FULL_ORDER",
        //     "full_assets" => true,
        //     "order_data" => [
        //         "app_id" => 450,
        //         "app_trans_id" => $result['app_trans_id'],
        //         "app_time" => $result['app_time'],
        //         "app_user" => $result['app_user'],
        //         "amount" => $result['amount'],
        //         "item" => json_encode(json_decode($result['item'], true)),
        //         "description" => $result['description'],
        //         "embed_data" => json_encode(json_decode($result['embed_data'], true)),
        //         "mac" => $result['mac'],
        //         "trans_type" => 4,
        //         "product_code" => "TF020"
        //     ],
        //     "campaign_code"=> "",
        //     "display_mode" => 2
        // ];

        ######################################


        $header = array(
            'Host: api.zalopay.vn',
            'x-platform: NATIVE',
            'x-device-os: ANDROID',
            'x-device-id: ' . $dataInfo['deviceid'],
            'x-device-model: ios',
            'x-app-version: ' . $dataInfo['appversion'],
            'user-agent: ' . $_SERVER['HTTP_USER_AGENT'] . ' ZaloPay Android / 9464',
            'x-density: hdpi',
            'authorization: Bearer ' . $dataInfo['access_token'],
            'x-access-token: ' . $dataInfo['access_token'],
            'x-zalo-id: ' . $dataInfo['zalo_id'],
            'x-zalopay-id:' . $dataInfo['user_id'],
            'x-user-id:' . $dataInfo['user_id']
        );

        $Data = [
            "order_type" => "FULL_ORDER",
            "full_assets" => true,
            "order_data" => [
                "app_id" => 450,
                "app_trans_id" => $result['app_trans_id'],
                "app_time" => $result['app_time'],
                "app_user" => $result['app_user'],
                "amount" => $result['amount'],
                "item" => $result['item'],
                "description" => $result['description'],
                "embed_data" => $result['embed_data'],
                "mac" => $result['mac'],
                "trans_type" => 4,
                "product_code" => "TF020"
            ],
            "campaign_code" => "",
            "display_mode" => 2
        ];
        $Action = "https://api.zalopay.vn/v2/cashier/assets";
        $responseData =  $this->CURL($Action, $header, $Data);

        if (!empty($responseData['error'])) {
            return $responseData['error'];
        }
        $responseData['data']['zalo_token'] = $result['zalo_token'];
        return $responseData['data'];


        ######################################
        // $client = new Client();
        // $response = $client->request('POST', $url, [
        //     'headers' => $headers,
        //     'body' => json_encode($body)
        // ]);
        // $response = $response->getBody()->getContents();
        // $responseData = json_decode($response, true);
        // if (!empty($responseData['error'])) {
        //     return $responseData['error'];
        // }
        // $responseData['data']['zalo_token'] = $result['zalo_token'];
        // return $responseData['data'];
    }

    private function createOrder($model, $data, $dataInfo)
    {

        $zalo_access_token = $this->getAccessToken($dataInfo);

        $newInfo = Arr::add($dataInfo, 'zalo_access_token', $zalo_access_token['zalo_access_token']);
        $model->info = $newInfo;
        $model->save();

        $ottoken = $this->getOttoken($zalo_access_token['zalo_access_token']);

        if (!empty($ottoken['error'])) {
            return response()->json([
                'status' => false,
                'message' => $ottoken['error'],
                'data' => null
            ], 200);
        }
        $infoTransfer = $this->getInfoTransfer($data['phone'], $dataInfo);
        $receiver_zalo_id = $infoTransfer['userid'];
        $receiver_name = $infoTransfer['displayname'];
        $receiver_avatar = $infoTransfer['avatar'];
        $amount = $data['amount'];
        $message = $data['message'];

        $url = "https://sapi.zalopay.vn/mt/v5/order";
        ################################################

        $header = array(
            'Host: sapi.zalopay.vn',
            'Origin: https://social.zalopay.vn',
            'Cookie: zalo_id=' . $dataInfo['zalo_id'] . '; zlp_token=' . $dataInfo['access_token'] . '; X-DRSITE=off; has_device_id=0',
        );


        $Data = '
        {
            "receiver_user_id": "' . $receiver_zalo_id . '",
            "receiver_zalo_id": null,
            "receiver_name": "",
            "receiver_avatar": "",
            "amount": "' . $amount . '",
            "note": "' . $message . '",
            "zalo_token": null,
            "media": {
                "greeting_card": {
                    "theme_id": "1"
                }
            }
        }';

        $Action = 'https://sapi.zalopay.vn/mt/v5/order';
        $responseData =  $this->CURL($Action, $header, $Data);

        if (!empty($responseData['error'])) {
            return $responseData['error'];
        }
        $responseData['data']['zalo_token'] = $ottoken['token'];
        return $responseData['data'];



        ##############################

        // $headers = [
        //     'User-Agent' => $_SERVER['HTTP_USER_AGENT'] . 'ZaloPay Android / 700783',
        //     'Content-Type' => 'application/json',
        //     'Accept' => 'application/json',
        //     'Host'   => 'sapi.zalopay.vn',
        //     'Origin' => 'https://social.zalopay.vn',
        //     'Cookie' => 'zalo_id=' . $dataInfo['zalo_id'] . '; zlp_token=' . $dataInfo['access_token'] . '; X-DRSITE=off; has_device_id=0',
        // ];

        // $body = [
        //     'receiver_user_id' => $receiver_zalo_id,
        //     'amount' => $amount,
        //     'note' => $message,
        //     'receiver_zalo_id' => $receiver_zalo_id,
        //     'receiver_name' => $receiver_name,
        //     'receiver_avatar' => $receiver_avatar,
        //     'zalo_token' => $ottoken['token'],
        //     'media' => [
        //         'greeting_card' => [
        //             'theme_id' => "1",
        //         ]
        //     ],
        // ];
        // $client = new Client();
        // $response = $client->request('POST', $url, [
        //     'headers' => $headers,
        //     'body' => json_encode($body)
        // ]);
        // $response = $response->getBody()->getContents();
        // $responseData = json_decode($response, true);
        // if (!empty($responseData['error'])) {
        //     return $responseData['error'];
        // }
        // $responseData['data']['zalo_token'] = $ottoken['token'];
        // return $responseData['data'];
    }

    private function cashierPay($result, $dataInfo)
    {
        $access_token = $dataInfo['access_token'];
        $zalo_id = $dataInfo['zalo_id'];
        $user_id = $dataInfo['user_id'];


        #########################################
        $pass = hash('sha256', $dataInfo['password']);
        $header = array(
            'Host: api.zalopay.vn',
            'x-platform: NATIVE',
            'x-device-os: ANDROID',
            'x-device-id: ' . $dataInfo['deviceid'],
            'x-device-model: OnePlus IN2025',
            'x-app-version: ' . $dataInfo['appversion'],
            'user-agent: ' . $_SERVER['HTTP_USER_AGENT'] . ' ZaloPay Android / 9464',
            'x-density: hdpi',
            'authorization: Bearer ' . $dataInfo['access_token'],
            'x-access-token: ' . $dataInfo['access_token'],
            'x-zalo-id: ' . $dataInfo['zalo_id'],
            'x-zalopay-id:' . $dataInfo['user_id'],
            'x-user-id:' . $dataInfo['user_id']
        );
        $Data = '{
            "order_token":"' . $result['order_token'] . '",
            "sof_token":"' . $result['source_of_fund']['sof_token'] . '",
            "promotion_token":"",
            "transaction_fee":0,
            "service_fee":0,
            "authenticator":{
                "authen_type":1,
                "pin":"' . $pass . '",
                "bio_token":"",
                "pay_token":"",
                "bio_state":""
            },
            "zalo_token":"",
            "service_id":0,
            "order_source":0
        }';
        $Action = 'https://api.zalopay.vn/v2/cashier/pay';
        return $this->CURL($Action, $header, $Data);

        #########################################
        $body = [
            'authenticator' => [
                "authen_type" => 1,
                "pin" => $dataInfo['encrypted_pin']
            ],
            "order_fee" => [0],
            'order_token' => $result['order_token'],
            'promotion_token' => "",
            "service_id" => 1,
            "user_fee" => [0],
            'sof_token' => $result['source_of_fund']['sof_token'],
            'transaction_fee' => 0,
            'service_fee' => 0,
            "zalo_token" => $result['zalo_token']
        ];

        $headers = [
            'X-Requested-With' => 'X-Requested-With',
            'User-Agent' => $_SERVER['HTTP_USER_AGENT'] . 'ZaloPay Android / 700783',
            'Host' => 'sapi.zalopay.vn',
            'Connection' => 'Keep-Alive',
            'Accept-Encoding' => 'gzip',
            'Origin' => 'https://social.zalopay.vn',
            'Cookie' => 'zalo_id=' . $zalo_id . '; zlp_token=' . $access_token . '; has_device_id=0; X-DRSITE=off',
            'Content-Type' => 'application/json',
            'Content-Length' => strlen(json_encode($body)),
        ];
        $url = "https://sapi.zalopay.vn/v2/cashier/pay";

        $client = new Client();
        $response = $client->request('POST', $url, [
            'headers' => $headers,
            'body' => json_encode($body)
        ]);
        $response = $response->getBody()->getContents();
        $responseData = json_decode($response, true);
        if (!empty($responseData['error'])) {
            return $responseData['error'];
        }
        return $responseData['data'];
    }

    private function getInfoTransfer($phone, $dataInfo)
    {
        $phone = $this->convertPhone($phone);
        $accesstoken = $dataInfo['access_token'];
        $userid = $dataInfo['user_id'];
        $url = "https://zalopay.com.vn/um/getuserinfobyphonev2?accesstoken=$accesstoken&phonenumber=$phone&userid=$userid";
        $client = new Client();
        $response = $client->request('GET', $url);
        $response = $response->getBody()->getContents();
        $responseData = json_decode($response, true);
        if (!empty($responseData['error'])) {
            return $responseData['error'];
        }
        return $responseData;
    }

    private function getAccessToken($data)
    {
        $url = "https://sapi.zalopay.vn/v2/zalo/access-token";
        $headers = [
            'Cookie' => 'zalo_id=' . $data['zalo_id'] . '; zlp_token=' . $data['access_token'] . '; X-DRSITE=off; has_device_id=0'
        ];
        $client = new Client();
        $response = $client->request('GET', $url, [
            'headers' => $headers
        ]);
        $response = $response->getBody()->getContents();
        $responseData = json_decode($response, true);
        if (!empty($responseData['error'])) {
            return $responseData['error'];
        }
        return $responseData['data'];
    }

    private function verifyPin($data)
    {
        $url = "https://sapi.zalopay.vn/v2/user/pin:validate";
        $headers = [
            'Host' => 'sapi.zalopay.vn',
            'Cookie' => 'zalo_id=' . $data['zalo_id'] . '; zlp_token=' . $data['access_token'] . '; X-DRSITE=off; has_device_id=0',
            'Content-Type' => 'application/json'
        ];
        $body = [
            'pin' => $data['encrypted_pin'],
            'type' => 1
        ];
        $client = new Client();
        $response = $client->request('POST', $url, [
            'headers' => $headers,
            'body' => json_encode($body)
        ]);
        $response = $response->getBody()->getContents();
        $responseData = json_decode($response, true);
        if (!empty($responseData['error'])) {
            return $responseData['error'];
        }
        return $responseData['data'];
    }

    private function convertPhone($phone)
    {
        $phone = str_replace('+84', '0', $phone);
        $phone = str_replace(' ', '', $phone);
        $phone = str_replace('-', '', $phone);
        $phone = str_replace('.', '', $phone);
        $phone = str_replace('(', '', $phone);
        $phone = str_replace(')', '', $phone);
        $phone = str_replace('+', '', $phone);
        return $phone;
    }

    private function getOttoken($zaloAccessToken)
    {
        $url = "https://graph.zalo.me/v2.0/ottoken?access_token=$zaloAccessToken";
        $client = new Client();
        $response = $client->request('GET', $url);
        $response = $response->getBody()->getContents();
        $responseData = json_decode($response, true);
        return $responseData;
    }

    public function getHistoryTrait($data)
    {
        $token = $data['token'];
        $data = ZaloPayAccount::where(['token' => $token])->first();
        if (empty($data) || $data->status == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản không tồn tại hoặc đã bị khóa',
                'data' => null
            ], 200);
        }
        $dataInfo = $data->info;
        $access_token = $dataInfo['access_token'];

        $headers = [
            'Host: sapi.zalopay.vn',
            'x-device-os: ANDROID',
            'x-platform" ZPA',
            'authorization:Bearer ' . $access_token . ''
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://sapi.zalopay.vn/v2/history/transactions?category_id=2&page_size=50");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);

        curl_close($ch);
        return json_decode($data, true);
    }

    private function generateImei()
    {
        return $this->generateRandomString(8) . '-' . $this->generateRandomString(4) . '-' . $this->generateRandomString(4) . '-' . $this->generateRandomString(4) . '-' . $this->generateRandomString(12);
    }

    private function generateRandomString($length = 20)
    {
        $characters = '0123456789AQWERTYUIOPSDFGHJKLMNBVCXZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function CURL($Action, $header, $data)
    {
        $Data = is_array($data) ? json_encode($data) : $data;
        $curl = curl_init();
        // echo strlen($Data); die;
        $header[] = 'Content-Type: application/json';
        $header[] = 'accept: application/json';
        $header[] = 'Content-Length: ' . strlen($Data);
        $opt = array(
            CURLOPT_URL => $Action,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => empty($data) ? FALSE : TRUE,
            CURLOPT_POSTFIELDS => $Data,
            CURLOPT_CUSTOMREQUEST => empty($data) ? 'GET' : 'POST',
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_ENCODING => "",
            CURLOPT_HEADER => FALSE,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_TIMEOUT => 5,
        );
        curl_setopt_array($curl, $opt);
        $body = curl_exec($curl);
        // echo strlen($body); die;
        if (is_object(json_decode($body))) {
            return json_decode($body, true);
        }
        return $body;
    }
}
