<?php

use Illuminate\Support\Facades\Config;

if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param string $path
     *
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }
}

if (!function_exists('public_path')) {
    /**
     * Return the path to public dir
     *
     * @param null $path
     *
     * @return string
     */
    function public_path($path = null)
    {
        return rtrim(app()->basePath('public/' . $path), '/');
    }
}

if (!function_exists('get_image')) {
    /**
     * @param $url
     *
     * @return string
     */
    function get_image($url)
    {
        if (empty($url)) {
            return null;
        }

        $path = storage_path(Config::get('constants.URL_IMG')) . "/$url";

        $type = pathinfo($path, PATHINFO_EXTENSION);

        if (!file_exists($path)) {
            return null;
        }

        $data = file_get_contents($path);

        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}

if (!function_exists('upload_image')) {
    /**
     * @param        $data
     * @param        $dir
     * @param        $file_name
     * @param string $type
     *
     * @return bool|null
     */
    function upload_image($data, $dir, $file_name, $type = "jpg")
    {
        if (empty($data)) {
            return null;
        }

        if (!file_exists(storage_path(Config::get('constants.URL_IMG')) . "/" . $dir)) {
            mkdir(storage_path(Config::get('constants.URL_IMG')) . "/" . $dir, 0777, true);
        }

        file_put_contents(storage_path(Config::get('constants.URL_IMG')) . "/$dir/$file_name.$type",
            base64_decode(preg_replace('#^data:image/\w+;base64,#i',
                '', $data)));

        return true;
    }
}

if (!function_exists('is_image')) {
    /**
     * @param $base64
     *
     * @return bool
     */
    function is_image($base64)
    {
        if (empty($base64)) {
            return false;
        }

        $base = base64_decode($base64);

        if (empty($base)) {
            return false;
        }

        $file_size = strlen($base);

        if ($file_size / 1024 / 1024 > Config::get("constant.IMG_UPLOAD_MAXSIZE", 1)) {
            return false;
        }

        return true;
    }
}

if (!function_exists("getDatesBetween")) {
    function getDatesBetween($inputFrom, $inputTo)
    {
        $start    = new \DateTime($inputFrom);
        $interval = new \DateInterval('P1D');
        $end      = new \DateTime(date('Y-m-d', strtotime("+1 day", strtotime($inputTo))));

        $period = new \DatePeriod($start, $interval, $end);

        $dates = array_map(function ($d) {
            return $d->format("Y-m-d");
        }, iterator_to_array($period));

        return $dates;
    }
}

if (!function_exists('get_device')) {
    function get_device() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
        
        if (strpos($user_agent, 'dart') !== false) {
            return 'APP';
        }
        
        if (preg_match('/(mobile|iphone|ipod|android|windows phone)/', $user_agent)) {
            return 'PHONE';
        }
        
        if (preg_match('/(tablet|ipad|playbook|android)/', $user_agent)) {
            return 'TABLET';
        }
        
        return 'DESKTOP';
    }
}

if (!function_exists('convertPhone')) {
    function convertPhone($phoneNumber)
    {
        $arr_Prefix
            = array(
            'CELL' => array(
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
            ),
            'HOME' => array(
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
            )
        );

        if (!empty($phoneNumber)) {
            //1. Xóa k t trắng
            $phoneNumber = str_replace(' ', '', $phoneNumber);
            //2. Xóa các dấu chấm phân cách
            $phoneNumber = str_replace('.', '', $phoneNumber);
            //3. Xóa các dấu gch nối phân cách
            $phoneNumber = str_replace('-', '', $phoneNumber);
            //4. Xóa dấu mở ngoặc ơn
            $phoneNumber = str_replace('(', '', $phoneNumber);
            //5. Xóa dấu đóng ngoặc đơn
            $phoneNumber = str_replace(')', '', $phoneNumber);
            //6. Xóa dấu +
            $phoneNumber = str_replace('+', '', $phoneNumber);
            //7. Chuyển 84 đầu thành 0
            if (substr($phoneNumber, 0, 2) == '84') {
                $phoneNumber = '0' . substr($phoneNumber, 2, strlen($phoneNumber) - 2);
            }
            $isSuccess = false;
            foreach ($arr_Prefix['HOME'] as $key => $value) {
                //$prefixlen=strlen($key);
                if (strpos($phoneNumber, $key) === 0) {
                    $prefix      = $key;
                    $prefixlen   = strlen($key);
                    $phone       = substr($phoneNumber, $prefixlen, strlen($phoneNumber) - $prefixlen);
                    $prefix      = str_replace($key, $value, $prefix);
                    $phoneNumber = $prefix . $phone;
                    //$phoneNumber=str_replace($key,$value,$phoneNumber);
                    $isSuccess = true;
                    break;
                }
            }

            if ($isSuccess == false) {
                foreach ($arr_Prefix['CELL'] as $key => $value) {
                    //$prefixlen=strlen($key);
                    if (strpos($phoneNumber, $key) === 0) {
                        $prefix      = $key;
                        $prefixlen   = strlen($key);
                        $phone       = substr($phoneNumber, $prefixlen, strlen($phoneNumber) - $prefixlen);
                        $prefix      = str_replace($key, $value, $prefix);
                        $phoneNumber = $prefix . $phone;
                        //$phoneNumber=str_replace($key,$value,$phoneNumber);
                        $isSuccess = true;
                        break;
                    }
                }
            }

            return $phoneNumber;
        } else {
            return false;
        }
    }
}