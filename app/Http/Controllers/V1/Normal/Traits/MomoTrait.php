<?php

namespace App\Http\Controllers\V1\Normal\Traits;

use App\Device;
use App\LoginMomo;
use App\Supports\Message;
use Crypt_RSA;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

trait MomoTrait
{
    /**
     * @var false|string
     */
    protected $imei;
    protected $time;
    protected $apiAction;
    protected $arr_Prefix;
    protected $AUTH_TOKEN;
    protected $appInfo;
    protected $deviceInfo;
    private   $URLAction;
    private   $rsa;

    /**
     *
     */
    public function __construct()
    {
        set_time_limit(0);
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $this->imei = $this->get_imei();
        $this->time = $this->getTimeNow();
        $this->apiAction = [
            'TRANS_HIS'                 => 'https://api.momo.vn/sync/transhis/list',
            'QUERY_TRAN_HIS_MSG'        => 'https://owa.momo.vn/api',
            'M2MU_CONFIRM'              => 'https://owa.momo.vn/api',
            'M2MU_INIT'                 => 'https://owa.momo.vn/api',
            'USER_LOGIN_MSG'            => 'https://owa.momo.vn/public/login',
            'CHECK_USER_BE_MSG'         => 'https://api.momo.vn/backend/auth-app/public',
            'SEND_OTP_MSG'              => 'https://api.momo.vn/backend/otp-app/public',
            'REG_DEVICE_MSG'            => 'https://api.momo.vn/backend/otp-app/public',
            'NEW_DEVICE_REQUEST_LOGIN'  => 'https://api.momo.vn/backend/device-login-app/public',
            'NEW_DEVICE_CONFIRM_LOGIN'  => 'https://api.momo.vn/backend/device-login-app/public',
            'NEW_DEVICE_RE_LOGIN'       => 'https://api.momo.vn/backend/device-login-app/public',
        ];

        $this->URLAction = array(
            "CHECK_USER_BE_MSG"     => "https://api.momo.vn/backend/auth-app/public/CHECK_USER_BE_MSG", //Check người dùng
            "SEND_OTP_MSG"          => "https://api.momo.vn/backend/otp-app/public/", //Gửi OTP
            "REG_DEVICE_MSG"        => "https://api.momo.vn/backend/otp-app/public/", // Xác minh OTP
            "QUERY_TRAN_HIS_MSG"    => "https://owa.momo.vn/api/QUERY_TRAN_HIS_MSG",
            "GET_OTP"               => "https://owa.momo.vn/public", // Check ls giao dịch
            "USER_LOGIN_MSG"        => "https://owa.momo.vn/public/login", // Đăng Nhập
            "QUERY_TRAN_HIS_MSG_NEW" => "https://m.mservice.io/hydra/v2/user/noti", // check ls giao dịch
            "M2MU_INIT"             => "https://owa.momo.vn/api/M2MU_INIT", // Chuyển tiền
            "M2MU_CONFIRM"          => "https://owa.momo.vn/api/M2MU_CONFIRM", // Chuyển tiền
            "LOAN_MSG"              => "https://owa.momo.vn/api/LOAN_MSG", // yêu cầu chuyển tiền
            'CHECK_USER_PRIVATE'    => 'https://owa.momo.vn/api/CHECK_USER_PRIVATE', // Check người dùng ẩn
            "QUERY_TRAN_HIS_NEW"    => "https://api.momo.vn/sync/transhis/browse",
            'GET_TRANS_BY_TID02'    => 'https://api.momo.vn/sync/transhis/details',
            "GET_TRANS_BY_TID_OLD"  => "https://owa.momo.vn/api/GET_TRANS_BY_TID",
            "GENERATE_TOKEN_AUTH_MSG" => "https://api.momo.vn/backend/auth-app/public/GENERATE_TOKEN_AUTH_MSG",
        );

        $this->appInfo = [
            'appCode'     => '4.0.14',
            'appId'       => "vn.momo.platform",
            'appVer'      => 40143,
            'buildNumber' => 0,
            'lang'        => 'vi',
            'channel'     => 'APP',
            'deviceOS'    => 'android',
        ];

        $this->deviceInfo = [
            'ccode'       => '084',
            'cname'       => 'Vietnam',
            'firmware'    => '25',
            'hardware'    => 'exynos8895',
            'csp'         => 'Viettel Mobile',
            'icc'         => '',
            'mcc'         => '452',
            'mnc'         => '',
            'device_os'   => 'Android',
        ];

        $this->arr_Prefix = [
            'CELL' => [
                '016966' => '03966',
                '0169'   => '039',
                '0168'   => '038',
                '0167'   => '037',
                '0166'   => '036',
                '0165'   => '035',
                '0164'   => '034',
                '0163'   => '033',
                '0162'   => '032',
                '0120'   => '070',
                '0121'   => '079',
                '0122'   => '077',
                '0126'   => '076',
                '0128'   => '078',
                '0123'   => '083',
                '0124'   => '084',
                '0125'   => '085',
                '0127'   => '081',
                '0129'   => '082',
                '01992'  => '059',
                '01993'  => '059',
                '01998'  => '059',
                '01999'  => '059',
                '0186'   => '056',
                '0188'   => '058'
            ],
            'HOME' => [
                '076'  => '0296',
                '064'  => '0254',
                '0281' => '0209',
                '0240' => '0204',
                '0781' => '0291',
                '0241' => '0222',
                '075'  => '0275',
                '056'  => '0256',
                '0650' => '0274',
                '0651' => '0271',
                '062'  => '0252',
                '0780' => '0290',
                '0710' => '0292',
                '026'  => '0206',
                '0511' => '0236',
                '0500' => '0262',
                '0501' => '0261',
                '0230' => '0215',
                '061'  => '0251',
                '067'  => '0277',
                '059'  => '0269',
                '0351' => '0226',
                '04'   => '024',
                '039'  => '0239',
                '0320' => '0220',
                '031'  => '0225',
                '0711' => '0293',
                '08'   => '028',
                '0321' => '0221',
                '058'  => '0258',
                '077'  => '0297',
                '060'  => '0260',
                '0231' => '0213',
                '063'  => '0263',
                '025'  => '0205',
                '020'  => '0214',
                '072'  => '0272',
                '0350' => '0228',
                '038'  => '0238',
                '030'  => '0229',
                '068'  => '0259',
                '057'  => '0257',
                '052'  => '0232',
                '0510' => '0235',
                '055'  => '0255',
                '033'  => '0203',
                '053'  => '0233',
                '079'  => '0299',
                '022'  => '0212',
                '066'  => '0276',
                '036'  => '0227',
                '0280' => '0208',
                '037'  => '0237',
                '054'  => '0234',
                '073'  => '0273',
                '074'  => '0294',
                '027'  => '0207',
                '070'  => '0270',
                '029'  => '0216'
            ]
        ];
    }

    private function sendOtpTrait($attributes)
    {
        $phone = $attributes['phone'];
        $password = $attributes['password'];

        $device = Device::inRandomOrder()->first();

        $data = array(
            'phone'    => $phone,
            'device'   => $device->device,
            'hardware' => $device->hardware,
            'facture'  => $device->facture,
            'SECUREID' => $this->SECUREID(),
            'MODELID'  => $device->MODELID,
            'imei'     => $this->get_imei(),
            'rkey'     => $this->get_rkey(20),
            'AAID'     => $this->get_imei(),
            'TOKEN'    => $this->get_TOKEN(),
            'password' => $password
        );

        $this->checkUserBeMsg($data);
        $result = $this->sendOtpMsg($data);

        if (!empty($result) && $result['resultType'] == "SUCCESS") {
            $loginMomo = LoginMomo::where('phone', $phone)->first();
            if (!$loginMomo) {
                LoginMomo::where('phone', $phone)->delete();
                LoginMomo::create([
                    'token'     => Str::random(64),
                    'password'  => $password,
                    'phone'     => $phone,
                    'time'      => time(),
                    'info'      => json_encode($data),
                    'status'    => 3
                ]);
                return response()->json(array('status' => 'success', 'message' => 'Gửi mã OTP về ' . $phone . ' thành công'));
            } else {
                $loginMomo->status = 3;
                $loginMomo->info = json_encode($data);
                $loginMomo->save();
                return response()->json(array('status' => 'success', 'message' => 'Gửi mã OTP về ' . $phone . ' thành công'));
            }
        } else {
            return response()->json(array('status' => 'success', 'message' => 'Lỗi vui lòng thực hiện lại sau.'));
        }
    }
    /**
     * @throws ValidationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     * @throws \Throwable
     */
    private function checkUserBeMsg($data)
    {
        $action = 'CHECK_USER_BE_MSG';
        $microtime = $this->getTimeNow();
        $header = [
            "agent_id: undefined",
            "sessionkey:",
            "user_phone: undefined",
            "authorization: Bearer undefined",
            "msgtype: CHECK_USER_BE_MSG",
            "Host: api.momo.vn",
            "User-Agent: okhttp/3.14.17",
            "app_version: " . $this->appInfo['appVer'],
            "app_code: ",
            "device_os: Android"
        ];
        $data = [
            'user'        => $data['phone'],
            'msgType'     => $action,
            'cmdId'       => (string)$microtime . '000000',
            'lang'        => $this->appInfo['lang'],
            'channel'     => $this->appInfo['channel'],
            'time'        => $microtime,
            'appVer'      => $this->appInfo['appVer'],
            'appCode'     => $this->appInfo['appCode'],
            'deviceOS'    => $this->appInfo['deviceOS'],
            'buildNumber' => $this->appInfo['buildNumber'],
            'appId'       => $this->appInfo['appId'],
            'momoMsg'     => [
                '_class'      => 'mservice.backend.entity.msg.RegDeviceMsg',
                'number'      => $data['phone'],
                'imei'        => $data['imei'],
                'cname'       => $this->deviceInfo['cname'],
                'ccode'       => $this->deviceInfo['ccode'],
                'device'      => $data['device'],
                'firmware'    => $this->deviceInfo['firmware'],
                'hardware'    => $this->deviceInfo['hardware'],
                'manufacture' => $data['facture'],
                'csp'         => $this->deviceInfo['csp'],
                'icc'         => $this->deviceInfo['icc'],
                'mcc'         => $this->deviceInfo['mcc'],
                'mnc'         => $this->deviceInfo['mnc'],
                'device_os'   => $this->deviceInfo['device_os'],
                'secure_id'   => $data["SECUREID"],
            ],
            'extra' => [
                'checkSum' => '',
            ],
        ];
        return $this->CURL("CHECK_USER_BE_MSG", $header, $data);
    }
    /**
     * @throws ValidationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     * @throws \Throwable
     */
    public function sendOtpMsg($data)
    {
        $action = 'SEND_OTP_MSG';
        $microtime = $this->get_microtime();
        $header = array(
            "agent_id: undefined",
            "sessionkey:",
            "user_phone: undefined",
            "authorization: Bearer undefined",
            "msgtype: SEND_OTP_MSG",
            "Host: api.momo.vn",
            "User-Agent: okhttp/3.14.17",
            "app_version: " . $this->appInfo['appVer'],
            "app_code: " . $this->appInfo['appCode'],
            "device_os: Android"
        );
        $data = [
            'user'        => $data['phone'],
            'msgType'     => $action,
            'cmdId'       => (string)$microtime . '000000',
            'lang'        => $this->appInfo['lang'],
            'channel'     => $this->appInfo['channel'],
            'time'        => $microtime,
            'appVer'      => $this->appInfo['appVer'],
            'appCode'     => $this->appInfo['appCode'],
            'deviceOS'    => $this->appInfo['deviceOS'],
            'buildNumber' => $this->appInfo['buildNumber'],
            'appId'       => $this->appInfo['appId'],
            'result'      => true,
            'errorCode'   => 0,
            'errorDesc'   => '',
            'extra'       => [
                'action'    => 'SEND',
                'rkey'      => $data["rkey"],
                'isVoice'   => true,
                'AAID'      => $data["AAID"],
                'IDFA'      => '',
                'TOKEN'     => $data["TOKEN"],
                'SIMULATOR' => '',
                'MODELID'   => $data["MODELID"],
                'REQUIRE_HASH_STRING_OTP'   => true,
                'SECUREID'  => $data["SECUREID"],
            ],
            'momoMsg'     => [
                '_class'      => 'mservice.backend.entity.msg.RegDeviceMsg',
                'number'      => $data['phone'],
                'imei'        => $data["imei"],
                'cname'       => $this->deviceInfo['cname'],
                'ccode'       => $this->deviceInfo['ccode'],
                'device'      => $data["device"],
                'firmware'    => $this->deviceInfo['firmware'],
                'hardware'    => $data["hardware"],
                'manufacture' => $data["facture"],
                'csp'         => $this->deviceInfo['csp'],
                'icc'         => $this->deviceInfo['icc'],
                'mcc'         => $this->deviceInfo['mcc'],
                'mnc'         => $this->deviceInfo['mnc'],
                'device_os'   => $this->deviceInfo['device_os'],
                'secure_id'   => $data['SECUREID'],
            ],
        ];
        return $this->CURL("SEND_OTP_MSG", $header, $data);
    }

    /**
     * @throws ValidationException|\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     * @throws \Exception
     */
    public function verifyDeviceAndLoginTrait($attributes)
    {
        $phone = $attributes['phone'];
        $password = $attributes['password'];
        $otp = $attributes['otp'];

        $loginMomo = LoginMomo::where('phone', $phone)->first();
        if (!$loginMomo) {
            throw new \Exception(Message::get('V003', 'Dữ liệu cho số điện thoại ' . $phone . ' không tồn tại.'));
        }
        $oldInfo = json_decode($loginMomo->info, true);
        $data = Arr::add($oldInfo, 'ohash', hash('sha256', $oldInfo["phone"] . $oldInfo["rkey"] . $otp));
        $result = $this->regDeviceMsg($data);
        if ($result['errorCode'] != 0) {
            return response()->json(['status' => 'error', 'message' => $result]);
        }
        if ($result['errorCode'] == 0 || empty($result['errorCode'])) {
            $newData = Arr::add($data, 'setupKey', $result["extra"]["setupKey"]);
            $newData = Arr::add($newData, 'setupKeyDecrypt', $this->get_setupKey($result["extra"]["setupKey"], $data));
            $loginMomo->info = $newData;
            $loginMomo->save();

            $result = $this->loginMomo($loginMomo->phone);

            if ($result['status'] == 'success') {
                $loginMomo->status = 1;
                $loginMomo->info = $newData;
                $loginMomo->save();
                return response()->json(['status' => 'success', 'message' => 'Đăng nhập thành công', 'token' => $loginMomo->token]);
            } else {
                return response()->json(['status' => 'error', 'message' => $result]);
            }
        }
    }

    private function regDeviceMsg($data)
    {
        $action = 'REG_DEVICE_MSG';
        $microtime = $this->get_microtime();

        $header = array(
            "agent_id: undefined",
            "sessionkey:",
            "user_phone: undefined",
            "authorization: Bearer undefined",
            "msgtype: REG_DEVICE_MSG",
            "Host: api.momo.vn",
            "User-Agent: okhttp/3.14.17",
            "app_version: " . $this->appInfo["appVer"],
            "app_code: " . $this->appInfo["appCode"],
            "device_os: ANDROID"
        );

        $data = [
            'user'        => $data['phone'],
            'msgType'     => $action,
            'cmdId'       => (string)$microtime . '000000',
            'lang'        => $this->appInfo['lang'],
            'channel'     => $this->appInfo['channel'],
            'time'        => $microtime,
            'appVer'      => $this->appInfo['appVer'],
            'appCode'     => $this->appInfo['appCode'],
            'deviceOS'    => $this->appInfo['deviceOS'],
            'buildNumber' => $this->appInfo['buildNumber'],
            'appId'       => $this->appInfo['appId'],
            'result'      => true,
            'errorCode'   => 0,
            'errorDesc'   => '',
            'extra'       => [
                'ohash'     => $data['ohash'],
                'AAID'      => $data["AAID"],
                'IDFA'      => '',
                'TOKEN'     => $data["TOKEN"],
                'SIMULATOR' => true,
                'MODELID'   => $data["MODELID"],
                'SECUREID'  => $data["SECUREID"],
            ],
            'momoMsg'     => [
                '_class'      => 'mservice.backend.entity.msg.RegDeviceMsg',
                'number'      => $data['phone'],
                'imei'        => $data['imei'],
                'cname'       => $this->deviceInfo['cname'],
                'ccode'       => $this->deviceInfo['ccode'],
                'device'      =>  $data["device"],
                'firmware'    => $this->deviceInfo['firmware'],
                'hardware'    => $data["hardware"],
                'manufacture' => $data["facture"],
                'csp'         => $this->deviceInfo['csp'],
                'icc'         => $this->deviceInfo['icc'],
                'mcc'         => $this->deviceInfo['mcc'],
                'mnc'         => $this->deviceInfo['mnc'],
                'device_os'   => $this->deviceInfo['device_os'],
                'secure_id'   => $data["SECUREID"],
            ],
        ];
        return $this->CURL("REG_DEVICE_MSG", $header, $data);
    }


    private function loginMomo($phone)
    {
        $loginMomo = LoginMomo::where(['phone' => $phone])->first();
        if (!$loginMomo) {
            throw new \Exception(Message::get('V003', 'Dữ liệu cho số điện thoại ' . $phone . ' không tồn tại.'));
        }
        $oldData = json_decode($loginMomo->info, true);
        $newData = Arr::add($oldData, 'agent_id', '');
        $newData = Arr::add($newData, 'sessionkey', '');
        $newData = Arr::add($newData, 'authorization', '');

        $result = $this->userLoginMsg($newData);

        if (!empty($result['errorCode'])) {
            $newData = Arr::set($newData, 'Name', $result['errorDesc']);
            $newData = Arr::set($newData, 'balance', 0);
            $loginMomo->status = 4;
            $loginMomo->info = $newData;
            $loginMomo->save();
            return [
                'status' => 'error',
                'message' => 'Thất bại',
                'data' => [
                    'code' => $result['errorCode'],
                    'desc' => $result['errorDesc']
                ]
            ];
        }

        $newData = Arr::set($oldData, 'authorization', $result['extra']['AUTH_TOKEN']);
        $newData = Arr::set($newData, 'RSA_PUBLIC_KEY', $result['extra']['REQUEST_ENCRYPT_KEY']);
        $newData = Arr::set($newData, 'sessionkey', $result['extra']['SESSION_KEY']);
        $newData = Arr::set($newData, 'balance', $result['extra']['BALANCE']);
        $newData = Arr::set($newData, 'agent_id', $result['momoMsg']['agentId']);
        $newData = Arr::set($newData, 'BankVerify', $result['momoMsg']['bankVerifyPersonalid']);
        $newData = Arr::set($newData, 'Name', $result['momoMsg']['name']);
        $newData = Arr::set($newData, 'refreshToken', $result['extra']['REFRESH_TOKEN']);
        $loginMomo->info = $newData;
        $loginMomo->save();
        return [
            'status' => 'success',
            'message' => 'Đăng nhập thành công!',
            'balance' => $result['extra']['BALANCE'],
            'token'   => $loginMomo->token
        ];
    }

    private function userLoginMsg($data)
    {
        $microtime = $this->get_microtime();
        $header = [
            "agent_id: " . $data["agent_id"],
            "user_phone: " . $data["phone"],
            "sessionkey: " . (!empty($data["sessionkey"])) ? $data["sessionkey"] : "",
            "authorization: Bearer " . $data["authorization"],
            "msgtype: USER_LOGIN_MSG",
            "Host: owa.momo.vn",
            "user_id: " . $data["phone"],
            "User-Agent: okhttp/3.14.17",
            "app_version: " . $this->appInfo["appVer"],
            "app_code: " . $this->appInfo["appCode"],
            "device_os: Android"
        ];
        $data = [
            'user'        => $data['phone'],
            'msgType'     => 'USER_LOGIN_MSG',
            'pass'        => $data['password'],
            'cmdId'       => (string)$microtime . '000000',
            'lang'        => $this->appInfo['lang'],
            'time'        => $microtime,
            'channel'     => $this->appInfo['channel'],
            'appVer'      => $this->appInfo['appVer'],
            'appCode'     => $this->appInfo['appCode'],
            'deviceOS'    => $this->appInfo['deviceOS'],
            'buildNumber' => $this->appInfo['buildNumber'],
            'appId'       => $this->appInfo['appId'],
            'result'      => true,
            'errorCode'   => 0,
            'errorDesc'   => '',
            'momoMsg'     => [
                '_class'  => 'mservice.backend.entity.msg.LoginMsg',
                'isSetup' => false,
            ],
            'extra' => [
                'pHash' => $this->get_pHash($data),
                'AAID' => $data['AAID'],
                'IDFA' => '',
                'TOKEN' => $data['TOKEN'],
                'SIMULATOR' => '',
                'SECUREID' => $data['SECUREID'],
                'MODELID' => $data['MODELID'],
                'checkSum' => $this->generateCheckSum('USER_LOGIN_MSG', $microtime, $data),
            ],
        ];
        return $this->CURL("USER_LOGIN_MSG", $header, $data);
    }

    /**
     * @param $phone
     * @param $token
     * @param int $day
     * @return array
     * @throws \Exception
     */
    public function checkHistoryTrait($phone, $token, int $day = 1): array
    {
        $loginMomo = LoginMomo::where(['phone' => $phone, 'token' => $token])->first();
        if (!$loginMomo) {
            throw new \Exception(Message::get('V003', $phone));
        }
        $data = json_decode($loginMomo->info, true);
        $day         = $day . ' day ago';
        $action      = 'QUERY_TRAN_HIS_MSG';
        $begin       = strtotime($day) * 1000;
        $microtime        = $this->get_microtime();
        $header = [
            "authorization: Bearer " . $data["authorization"],
            "user_phone: " . $data["phone"],
            "sessionkey: " . $data["sessionkey"],
            "agent_id: " . $data["agent_id"],
            'app_version: ' . $this->appInfo["appVer"],
            'app_code: ' . $this->appInfo["appCode"],
            "Host: m.mservice.io"
        ];
        $data = [
            'user'        => (string)$data["phone"],
            'msgType'     => $action,
            'cmdId'       => (string)$microtime . '000000',
            'lang'        => $this->appInfo["lang"],
            'channel'     => $this->appInfo["channel"],
            'time'        => $microtime,
            'appVer'      => $this->appInfo["appVer"],
            'appCode'     => $this->appInfo["appCode"],
            'deviceOS'    => $this->appInfo["deviceOS"],
            'buildNumber' => $this->appInfo["buildNumber"],
            'appId'       => $this->appInfo["appId"],
            'extra'       => [
                'checkSum' => $this->generateCheckSum($action, $microtime, $data),
            ],
            'momoMsg'     => [
                '_class' => 'mservice.backend.entity.msg.M2MUConfirmMsg',
                'begin'  => $begin,
                'end'    => $microtime,
            ],
        ];
        return $this->CURL($action, $header, $data);
    }

    public function sendMoneyTrait($phone, $token, $receiverNumber, $amount, $comment, $name)
    {
        $loginMomo = LoginMomo::where(['phone' => $phone, 'token' => $token])->first();
        if (!$loginMomo) {
            throw new \Exception(Message::get('V003', $phone));
        }
        $result = $this->sendMoneyMomo($loginMomo->token, $amount, $comment, $receiverNumber, $name);
        if(empty($result)){
            return response()->json(['status' => 'error', 'message' => 'Token đã hết hạn. Vui lòng refresh token.']);
        }
        if ($result['status'] == 'error') {
            return response()->json(['status' => 'error', 'message' => $result['message']]);
        } else {
            return response()->json([
                'status'  => 'success', 
                'message' => 'Thành công',
                'tran_id' => $result['data']['tranId'],
                'balance' => $result['data']['balance'],
            ]);
                  
            // return response()->json(['status' => 'success', 'message' => 'Chuyển tiền thành công( 
            //         Số tiền: ' . $result['data']['amount'] . ' VNĐ , 
            //         Người nhận: ' . $result['data']['partnerName'] . ' , 
            //         Lời nhắn: ' . $result['data']['comment'] . ' ,
            //         MGD: ' . $result['data']['tranId'] . ' )']);
        }
    }

    public function sendMoneyMomo($token, $amount, $comment, $receiver, $name)
    {
        $dataMomo = LoginMomo::where('token', $token)->first();
        $phone = $dataMomo->phone;
        $partnerName = $name;
        if (!$dataMomo) {
            $json = [
                "status" => "error",
                "code" => 2005,
                "message" => "Số không có trong hệ thống"
            ];
            return $json;
        }
        $data = json_decode($dataMomo->info, true);

        $dataSend = array(
            'comment'    => $comment,
            'amount'     => $amount,
            'partnerName' => $partnerName,
            'receiver'   => $receiver,
        );
        $result = $this->M2MU_INIT($data, $dataSend);
        if (!empty($result["errorCode"]) && $result["errorDesc"] != "Lỗi cơ sở dữ liệu. Quý khách vui lòng thử lại sau") {
            $json = array(
                "status" => "error",
                "code" => $result["errorCode"],
                "message" => $result["errorDesc"]
            );
            return $json;
        }
        if (is_null($result)) {
            $json = array(
                "status" => "error",
                "code" => -5,
                "message" => "Đã xảy ra lỗi ở momo hoặc bạn đã hết hạn truy cập vui lòng đăng nhập lại"
            );
            return $result;
        }

        $id = $result["momoMsg"]["replyMsgs"]["0"]["ID"];
        $result = $this->M2MU_CONFIRM($id, $data, $dataSend);
        if (empty($result['errorCode'])) {
            $balance = $result["extra"]["BALANCE"];
            $tranHisMsg = $result["momoMsg"]["replyMsgs"]["0"]["tranHisMsg"];
            $data_new = Arr::set($data, 'balance', $balance);
            $dataMomo->info = $data_new;
            $dataMomo->save();
            $json = array(
                'status' => 'success',
                'message' => 'Thành công',
                'code' => 0,
                'data' => array(
                    "balance" => $balance,
                    "ID" => $tranHisMsg["ID"],
                    "tranId" => $tranHisMsg["tranId"],
                    "partnerId" => $tranHisMsg["partnerId"],
                    "partnerName" => $tranHisMsg["partnerName"],
                    "amount" => $tranHisMsg["amount"],
                    "comment" => (empty($tranHisMsg["comment"])) ? "" : $tranHisMsg["comment"],
                    "status" => $tranHisMsg["status"],
                    "desc" => $tranHisMsg["desc"],
                    "ownerNumber" => $tranHisMsg["ownerNumber"],
                    "ownerName" => $tranHisMsg["ownerName"],
                    "millisecond" => $tranHisMsg["finishTime"]
                )
            );
            return $json;
        } else {
            $json = array(
                'status' => 'error',
                "code" => $result["errorCode"],
                "message" => $result["errorDesc"]
            );
            return $json;
        }
    }

    public function M2MU_INIT($dataPhone, $dataSend)
    {
        $microtime = $this->get_microtime();
        $requestkeyRaw = $this->generateRandomString(32);
        $requestkey = $this->RSA_Encrypt($dataPhone["RSA_PUBLIC_KEY"], $requestkeyRaw);
        $header = array(
            "agent_id: " . $dataPhone["agent_id"],
            "user_phone: " . $dataPhone["phone"],
            "sessionkey: " . $dataPhone["sessionkey"],
            "authorization: Bearer " . $dataPhone["authorization"],
            "msgtype: M2MU_INIT",
            "userid: " . $dataPhone["phone"],
            "requestkey: " . $requestkey,
            "Host: owa.momo.vn"
        );
        $ipaddress = $this->get_ip_address();
        $data = array(
            'user' => $dataPhone['phone'],
            'msgType' => 'M2MU_INIT',
            'cmdId' => (string)$microtime . '000000',
            'lang' => 'vi',
            'time' => (int)$microtime,
            'channel' => 'APP',
            'appVer' => $this->appInfo["appVer"],
            'appCode' => $this->appInfo["appCode"],
            'deviceOS' => 'ANDROID',
            'buildNumber' => 0,
            'appId' => 'vn.momo.platform',
            'result' => true,
            'errorCode' => 0,
            'errorDesc' => '',
            'momoMsg' =>
            array(
                'clientTime' => (int)$microtime - 221,
                'tranType' => 2018,
                'comment' => $dataSend['comment'],
                'amount' => $dataSend['amount'],
                'partnerId' => $dataSend['receiver'],
                'partnerName' => $dataSend['partnerName'],
                'ref' => '',
                'serviceCode' => 'transfer_p2p',
                'serviceId' => 'transfer_p2p',
                '_class' => 'mservice.backend.entity.msg.M2MUInitMsg',
                'tranList' =>
                array(
                    0 =>
                    array(
                        'partnerName' => $dataSend['partnerName'],
                        'partnerId' => $dataSend['receiver'],
                        'originalAmount' => $dataSend['amount'],
                        'serviceCode' => 'transfer_p2p',
                        'stickers' => '',
                        'themeBackground' => '#f5fff6',
                        'themeUrl' => 'https://cdn.mservice.com.vn/app/img/transfer/theme/Corona_750x260.png',
                        'transferSource' => '',
                        'socialUserId' => '',
                        '_class' => 'mservice.backend.entity.msg.M2MUInitMsg',
                        'tranType' => 2018,
                        'comment' => $dataSend['comment'],
                        'moneySource' => 1,
                        'partnerCode' => 'momo',
                        'serviceMode' => 'transfer_p2p',
                        'serviceId' => 'transfer_p2p',
                        'extras' => '{"loanId":0,"appSendChat":false,"loanIds":[],"stickers":"","themeUrl":"https://cdn.mservice.com.vn/app/img/transfer/theme/Corona_750x260.png","hidePhone":false,"vpc_CardType":"SML","vpc_TicketNo":"' . $ipaddress . '","vpc_PaymentGateway":""}',
                    ),
                ),
                'extras' => '{"loanId":0,"appSendChat":false,"loanIds":[],"stickers":"","themeUrl":"https://cdn.mservice.com.vn/app/img/transfer/theme/Corona_750x260.png","hidePhone":false,"vpc_CardType":"SML","vpc_TicketNo":"' . $ipaddress . '","vpc_PaymentGateway":""}',
                'moneySource' => 1,
                'partnerCode' => 'momo',
                'rowCardId' => '',
                'giftId' => '',
                'useVoucher' => 0,
                'prepaidIds' => '',
                'usePrepaid' => 0,
            ),
            'extra' =>
            array(
                'checkSum' => $this->generateCheckSum('M2MU_INIT', $microtime, $dataPhone),
            ),
        );
        return $this->CURL("M2MU_INIT", $header, $this->Encrypt_data($data, $requestkeyRaw));
    }

    public function M2MU_CONFIRM($ID, $data, $dataSend)
    {
        $microtime = $this->get_microtime();
        $requestkeyRaw = $this->generateRandomString(32);
        $requestkey = $this->RSA_Encrypt($data["RSA_PUBLIC_KEY"], $requestkeyRaw);
        $header = array(
            "agent_id: " . $data["agent_id"],
            "user_phone: " . $data["phone"],
            "sessionkey: " . $data["sessionkey"],
            "authorization: Bearer " . $data["authorization"],
            "msgtype: M2MU_INIT",
            "userid: " . $data["phone"],
            "requestkey: " . $requestkey,
            "Host: owa.momo.vn",
            "User-Agent: MoMoPlatform-Release/31152 CFNetwork/1331.0.7 Darwin/21.4.0"
        );
        $ipaddress = $this->get_ip_address();
        $Data = array(
            'user' => $data['phone'],
            'pass' => $data['password'],
            'msgType' => 'M2MU_CONFIRM',
            'cmdId' => (string)$microtime . '000000',
            'lang' => 'vi',
            'time' => (int)$microtime,
            'channel' => 'APP',
            'appVer' => $this->appInfo["appVer"],
            'appCode' => $this->appInfo["appCode"],
            'deviceOS' => 'ANDROID',
            'buildNumber' => 0,
            'appId' => 'vn.momo.platform',
            'result' => true,
            'errorCode' => 0,
            'errorDesc' => '',
            'momoMsg' =>
            array(
                'ids' =>
                array(
                    0 => $ID,
                ),
                'totalAmount' => $dataSend['amount'],
                'originalAmount' => $dataSend['amount'],
                'originalClass' => 'mservice.backend.entity.msg.M2MUConfirmMsg',
                'originalPhone' => $data['phone'],
                'totalFee' => '0.0',
                'id' => $ID,
                'GetUserInfoTaskRequest' => $dataSend['receiver'],
                'tranList' =>
                array(
                    0 =>
                    array(
                        '_class' => 'mservice.backend.entity.msg.TranHisMsg',
                        'user' => $data['phone'],
                        'clientTime' => (int)($microtime - 211),
                        'tranType' => 36,
                        'amount' => (int)$dataSend['amount'],
                        'receiverType' => 1,
                    ),
                    1 =>
                    array(
                        '_class' => 'mservice.backend.entity.msg.TranHisMsg',
                        'user' => $data['phone'],
                        'clientTime' => (int)($microtime - 211),
                        'tranType' => 36,
                        'partnerId' => $dataSend['receiver'],
                        'amount' => 100,
                        'comment' => '',
                        'ownerName' => $data['Name'],
                        'receiverType' => 0,
                        'partnerExtra1' => '{"totalAmount":' . $dataSend['amount'] . '}',
                        'partnerInvNo' => 'borrow',
                    ),
                ),
                'serviceId' => 'transfer_p2p',
                'serviceCode' => 'transfer_p2p',
                'clientTime' => (int)($microtime - 211),
                'tranType' => 2018,
                'comment' => '',
                'ref' => '',
                'amount' => $dataSend['amount'],
                'partnerId' => $dataSend['receiver'],
                'bankInId' => '',
                'otp' => '',
                'otpBanknet' => '',
                '_class' => 'mservice.backend.entity.msg.M2MUConfirmMsg',
                'extras' => '{"appSendChat":false,"vpc_CardType":"SML","vpc_TicketNo":"' . $ipaddress . '"","vpc_PaymentGateway":""}',
            ),
            'extra' =>
            array(
                'checkSum' => $this->generateCheckSum('M2MU_CONFIRM', $microtime, $data),
            ),
        );
        return $this->CURL("M2MU_CONFIRM", $header, $this->Encrypt_data($Data, $requestkeyRaw));
    }

    /**
     * @param $phone
     * @param $token
     * @return mixed|null
     * @throws \Exception
     */
    public function historyMomoByNotifyTrait($phone, $token)
    {
        $hours    = 1;
        $url    = "https://m.mservice.io/hydra/v2/user/noti";
        $loginMomo = LoginMomo::where(['phone' => $phone, 'token' => $token])->first();
        if (!$loginMomo) {
            throw new \Exception(Message::get('V003', 'Dữ liệu'));
        }
        $data = json_decode($loginMomo->info, true);
    
        $data_post = [
            'userId'   => $phone,
            'fromTime' => (time() - (3600 * $hours)) * 1000,
            // 'fromTime' => (time() - (86400 * $day)) * 1000,
            'toTime'   => $this->get_microtime(),
            'limit'    => 10
        ];

        $header = [
            'Authorization: Bearer ' . trim($data['authorization']),
            'Content-Type: application/json'
        ];

        $response = $this->curlMomo($url, $header, $data_post, "POST");
        if ($response == 'Unauthorized') {
            throw new \Exception(Message::get('token_expired'));
        }
        if (empty($response)) {
            throw new \Exception(Message::get('R011'));
        }
        $result = json_decode($response, true);

        return $result['message']['data']['notifications'] ?? null;
    }

    /**
     * @param $plaintext
     * @param $password
     * @return string
     */
    public function _encode($plaintext, $password): string
    {
        $method    = 'aes-256-cbc';
        $iv        = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
        $encrypted = base64_encode(openssl_encrypt($plaintext, $method, $password, OPENSSL_RAW_DATA, $iv));
        return $encrypted;
    }

    /**
     * @param $encrypted
     * @param $password
     * @return false|string
     */
    public function _decode($encrypted, $password)
    {
        $method    = 'aes-256-cbc';
        $iv        = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
        $decrypted = openssl_decrypt(base64_decode($encrypted), $method, $password, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    /**
     * @param $action
     * @param array $arrDataPost
     * @param $config
     * @return mixed
     */
    private function getDecodeRq($action, array $arrDataPost, $config)
    {
        $requestKeyRaw = $this->randomString(32);
        $requestKey    = $this->_encodeRSA($requestKeyRaw, $config->req_encrypt_key);
        $rqCheckInit   = $this->curlPost($this->apiAction[$action] . '/' . $action, $this->_encode(json_encode($arrDataPost), $requestKeyRaw), $action, $requestKey, $config->phone, $config->auth_token);
        return json_decode($this->_decode($rqCheckInit, $requestKeyRaw));
    }

    /**
     * @param int $length
     * @return string
     */
    public function randomString(int $length = 10): string
    {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param $content
     * @param $key
     * @return string
     */
    public function _encodeRSA($content, $key): string
    {
        $rsa = new \Crypt_RSA();
        $rsa->loadKey($key);
        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        return base64_encode($rsa->encrypt($content));
    }

    /**
     * @param $msgType
     * @param $time
     * @param $config
     * @return false|string
     */
    public function generateCheckSum($type, $microtime, $data)
    {
        $Encrypt = $data["phone"] . $microtime . '000000' . $type . ($microtime / 1000000000000.0) . 'E12';
        $iv = pack('C*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        return base64_encode(openssl_encrypt($Encrypt, 'AES-256-CBC', $data["setupKeyDecrypt"], OPENSSL_RAW_DATA, $iv));
    }
    public function generateCheckSumOld($msgType, $time, $config)
    {
        $l = $time . '000000';
        $f = $config->phone . $l . $msgType . ($time / 1e12) . "E12";
        return @openssl_encrypt($f, 'AES-256-CBC', substr($config->key, 0, 32), 0, '');
    }

    /**
     * @param int $length
     * @return string
     */
    private function SECUREID(int $length = 17): string
    {
        return $this->generateRandomString($length);
    }

    /**
     * @param $length
     * @return string
     */
    private function generateRandomString($length = 20): string
    {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param $length
     */
    public function get_TOKEN()
    {
        return $this->generateRandomString(22) . ':' . $this->generateRandomString(9) . '-' . $this->generateRandomString(20) . '-' . $this->generateRandomString(12) . '-' . $this->generateRandomString(7) . '-' . $this->generateRandomString(7) . '-' . $this->generateRandomString(53) . '-' . $this->generateRandomString(9) . '_' . $this->generateRandomString(11) . '-' . $this->generateRandomString(4);
    }

    /**
     * @return string
     */
    public function getTimeNow(): string
    {
        $pieces = explode(" ", microtime());
        return bcadd(($pieces[0] * 1000), bcmul($pieces[1], 1000));
    }

    /**
     * @return string
     */
    public function generateImei(): string
    {
        return $this->generateRandomString(8) . '-' . $this->generateRandomString(4) . '-' . $this->generateRandomString(4) . '-' . $this->generateRandomString(4) . '-' . $this->generateRandomString(12);
    }



    /**
     * @param int $length
     * @return string
     */
    private function get_rkey(int $length = 20): string
    {
        $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $size  = strlen($chars);
        $str   = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, $size - 1)];
        }
        return $str;
    }

    /**
     * @param $data
     * @param $key
     * @param $mode
     * @return false|string
     */
    private function encryptDecrypt($data, $key, $mode = 'ENCRYPT')
    {
        if (strlen($key) < 32) {
            $key = str_pad($key, 32, 'x');
        }
        $key = substr($key, 0, 32);
        $iv  = pack('C*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        if ($mode === 'ENCRYPT') {
            return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv));
        } else {
            return openssl_decrypt(base64_decode($data), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        }
    }

    /**
     * @return int
     */
    private function get_microtime(): int
    {
        return (int)trim(floor(microtime(true) * 1000));
    }

    /**
     * @param $data
     * @param $type
     * @return false|string
     */
    private function get_checksum($data, $type)
    {
        $checkSumSyntax = $data['phone'] . $this->get_microtime() . '000000' . $type . ($this->get_microtime() / 1000000000000.0) . 'E12';
        return $this->encryptDecrypt($checkSumSyntax, $this->encryptDecrypt($data['setupKey'], $data['ohash'], 'DECRYPT'));
    }

    /**
     * @param $data
     * @return false|string
     */
    private function get_pHash($data)
    {
        $pHashSyntax = $data['imei'] . '|' . $data['password'];
        return $this->encryptDecrypt($pHashSyntax, $this->encryptDecrypt($data['setupKey'], $data['ohash'], 'DECRYPT'));
    }

    /**
     * @return string
     */
    private function get_imei(): string
    {
        $time = md5($this->get_microtime());
        $text = $this->getText($time);
        return strtoupper($text);
    }

    /**
     * @return string
     */
    private function get_onesignal(): string
    {
        $time = md5($this->get_microtime() + time());
        return $this->getText($time);
    }

    /**
     * @param $url
     * @param $dataPost
     * @param $MsgType
     * @param $requestKey
     * @param $phone
     * @param $Auth
     * @return bool|string
     */
    private function curlPost($url, $dataPost, $msgType, $requestKey = null, $phone = null, $Auth = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataPost);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1');

        $headers   = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-Type: application/json';
        $header[] = 'Content-Length: ' . strlen($dataPost);
        if ($Auth != false) {
            $headers[] = 'Authorization: Bearer ' . $Auth;
        }
        $headers[] = 'Userhash: ' . md5($phone);
        $headers[] = 'Msgtype: ' . $msgType;
        $headers[] = 'device_os: ANDROID';
        $headers[] = 'app_version: ' . $this->appInfo['appVer'];
        $headers[] = 'app_code: ' . $this->appInfo['appCode'];
        $headers[] = 'channel: ' . $this->appInfo['channel'];;
        $headers[] = 'lang: ' . $this->appInfo['lang'];
        $headers[] = 'agent_id: 0';
        $headers[] = 'User-Agent: Ktor client';
        $headers[] = 'momo-session-key-tracking: ' . $this->imei;
        if ($requestKey != null) {
            $headers[] = 'requestkey: ' . $requestKey;
        }
        if ($phone != null) {
            $headers[] = 'user_id: ' . $phone;
            $headers[] = 'user_phone: ' . $phone;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result    = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @param $url
     * @param $header
     * @param $data_post
     * @param $type
     * @return bool|mixed|string
     */
    private function curlMomo($url, $header, $data_post, $type = "GET")
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $type,
            CURLOPT_POSTFIELDS     => json_encode($data_post),
            CURLOPT_HTTPHEADER     => $header,
        ));
        $response  = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (empty($response)) {
            return $http_code;
        }
        return $response;
    }

    public function curlGet($url)

    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function getInfoUserTrait($receiverNumber)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://nhantien.momo.vn/' . $receiverNumber);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
            $headers   = array();
            $headers[] = 'Connection: keep-alive';
            $headers[] = 'Cache-Control: max-age=0';
            $headers[] = 'Sec-Ch-Ua: \" Not;A Brand\";v=\"99\", \"Google Chrome\";v=\"91\", \"Chromium\";v=\"91\"';
            $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
            $headers[] = 'Upgrade-Insecure-Requests: 1';
            $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.77 Safari/537.36';
            $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
            $headers[] = 'Sec-Fetch-Site: none';
            $headers[] = 'Sec-Fetch-Mode: navigate';
            $headers[] = 'Sec-Fetch-User: ?1';
            $headers[] = 'Sec-Fetch-Dest: document';
            $headers[] = 'Accept-Language: vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7,fr-FR;q=0.6,fr;q=0.5';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            $name = explode("</div>", explode('<div class="d-flex justify-content-center" style="padding-top: 15px;padding-bottom: 15px">', $result)[1])[0]; //Lấy tên chủ khoản momo
        } catch (\Exception $exception) {
            $name = "UNKNOWN";
        }
        return $name;
    }

    /**
     * @param string $time
     * @return string
     */
    private function getText(string $time): string
    {
        $text = substr($time, 0, 8) . '-';
        $text .= substr($time, 8, 4) . '-';
        $text .= substr($time, 12, 4) . '-';
        $text .= substr($time, 16, 4) . '-';
        $text .= substr($time, 17, 12);
        return $text;
    }

    private function CURL($Action, $header, $data, $proxy = null)
    {
        if ($proxy == null) {
            $Data = is_array($data) ? json_encode($data) : $data;
            $curl = curl_init();
            // echo strlen($Data); die;
            $header[] = 'Content-Type: application/json';
            $header[] = 'accept: application/json';
            $header[] = 'Content-Length: ' . strlen($Data);
            $opt = array(
                CURLOPT_URL => $this->URLAction[$Action],
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POST => empty($data) ? FALSE : TRUE,
                CURLOPT_POSTFIELDS => $Data,
                CURLOPT_CUSTOMREQUEST => empty($data) ? 'GET' : 'POST',
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_ENCODING => "",
                CURLOPT_HEADER => FALSE,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_TIMEOUT => 20,
            );
            curl_setopt_array($curl, $opt);
            $body = curl_exec($curl);
            if (is_object(json_decode($body))) {
                return json_decode($body, true);
            }
        } else {

            $proxy = explode('|', $proxy);
            $ip = explode(':', $proxy[1])[0];
            $port = explode(':', $proxy[1])[1];

            $Data = is_array($data) ? json_encode($data) : $data;
            $curl = curl_init();
            // echo strlen($Data); die;
            $header[] = 'Content-Type: application/json';
            $header[] = 'accept: application/json';
            $header[] = 'Content-Length: ' . strlen($Data);
            $opt = array(
                CURLOPT_URL => $this->URLAction[$Action],
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POST => empty($data) ? FALSE : TRUE,
                CURLOPT_POSTFIELDS => $Data,
                CURLOPT_CUSTOMREQUEST => empty($data) ? 'GET' : 'POST',
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_ENCODING => "",
                CURLOPT_HEADER => FALSE,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_PROXY => $ip,
                CURLOPT_PROXYPORT => $port,
                CURLOPT_PROXYUSERPWD => $proxy[0]
            );
            curl_setopt_array($curl, $opt);
            $body = curl_exec($curl);
            // echo strlen($body); die;
            if (is_object(json_decode($body))) {
                return json_decode($body, true);
            }
        }
        return json_decode($this->Decrypt_data($body), true);
    }

    public function Decrypt_data($data)
    {

        $iv = pack('C*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        return openssl_decrypt(base64_decode($data), 'AES-256-CBC', $this->keys, OPENSSL_RAW_DATA, $iv);
    }

    public function get_setupKey($setUpKey, $data)
    {
        $iv = pack('C*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        return openssl_decrypt(base64_decode($setUpKey), 'AES-256-CBC', $data["ohash"], OPENSSL_RAW_DATA, $iv);
    }

    public function Encrypt_data($data, $key)
    {

        $iv = pack('C*', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $this->keys = $key;
        return base64_encode(openssl_encrypt(is_array($data) ? json_encode($data) : $data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv));
    }

    public function RSA_Encrypt($key, $content)
    {
        if (empty($this->rsa)) {
            $this->INCLUDE_RSA($key);
        }
        return base64_encode($this->rsa->encrypt($content));
    }

    private function INCLUDE_RSA($key)
    {
        $this->rsa = new \Crypt_RSA();
        $this->rsa->loadKey($key);
        $this->rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        return $this;
    }

    private function get_ip_address()
    {
        $isValid = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        if (!empty($isValid)) {
            return $_SERVER['REMOTE_ADDR'];
        }
        try {
            $isIpv4 = json_decode(file_get_contents('https://api.ipify.org?format=json'), true);
            return $isIpv4['ip'];
        } catch (\Throwable $e) {
            return '103.74.122.58';
        }
    }
}
