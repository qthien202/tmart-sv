<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\ShippingCompany;
use App\Http\Controllers\V1\Auth\Resources\ShippingCompany\ShippingCompanyCollection;
use App\Http\Controllers\V1\Auth\Resources\ShippingCompany\ShippingCompanyResource;
use App\Http\Controllers\V1\Normal\Models\Cart;
use App\Http\Controllers\V1\Normal\Resources\Cart\CartResource;
use App\SERVICE;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ShippingCompanyController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new ShippingCompany();
    }
    
    public function getShippingCompanies(Request $request)
    {
        $shippingCompany = $this->model->search($request->all());
        return new ShippingCompanyCollection($shippingCompany);
    }

    public function createShippingOrder(Request $request){
        $input = $request->all();
        $action = Arr::get($input,'action','preview');
        $urlGHTKTest = "https://services-staging.ghtklab.com";
        //$urlGHTKProduction = "https://services.giaohangtietkiem.vn";
        
        $userID = SERVICE::getCurrentUserId();
        $userInfo = User::find($userID);

        $cart = Cart::where('user_id',$userID)->first();

        // Thông tin người gửi
        $fromName = "Công ty Tmart";
        $fromPhone = "0379086875";
        $fromAddress = "233, Nguyễn Văn Cừ, An Hòa, Ninh Kiều, TP. Cần Thơ";
        $fromWardName = "Phường An Hòa";
        $fromDistrictName = "Quận Ninh Kiều";
        $fromProvinceName = "Cần Thơ";

        // Thông tin người nhận
        $toName = $cart->name;
        $toPhone = $cart->phone;
        $toAddress = $cart->address;
        $toWardName = $cart->ward_name;
        $toDistrictName = $cart->district_name;
        $toProvinceName = $cart->city_name;

        $total = array_values(array_filter($cart->info,function($i) {
            if ($i["code"] == "total") {
                return $i;
            }
        }));
        if ($total[0]["value"] >= 20000000) {
            return $this->responseError("Số tiền không được vượt quá 20.000.000 đ");
        };
        $totalCOD = (int)($total[0]["value"]);
        // return $totalCOD;
        if ($action == "preview" || $action == "p") {
            //Tính phí vận chuyển
            $data = array(
                "pick_province" => $fromProvinceName,
                "pick_district" => $fromDistrictName,
                "province" => $toProvinceName,
                "district" => $toDistrictName,
                "address" => $toAddress,
                "weight" => 1000,
                "value" => $totalCOD,
                "transport" => "road",
                "deliver_option" => "xteam",
                "tags"  => [1]
            );
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $urlGHTKTest."/services/shipment/fee?" . http_build_query($data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_HTTPHEADER => array(
                    "Token: ".env("TOKEN_GHTK"),
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response,true);
            $response['total_fee'] = $response['fee']['fee'];

            return $this->responseSuccess(null,$response);
        }
        else if ($action == "create" || $action == "c"){
            // 
            $cartDetails = $cart->cartDetails;
            $product = [];
            foreach ($cartDetails as $item) {
                $product[] = [
                    "name" => $item->product->product_name,
                    "weight" => 0.1,
                    "quantity" => $item->quantity,
                    "product_code" => (string) $item->product->id,
                ];
            }
            // Tạo đơn 
            $product=json_encode($product);
            $order = <<<HTTP_BODY
            {
                "products": $product,
                "order": {
                    "id": "$cart->id",
                    "pick_name": "$fromName",
                    "pick_address": "$fromAddress",
                    "pick_province": "$fromProvinceName",
                    "pick_district": "$fromDistrictName",
                    "pick_ward": "$fromWardName",
                    "pick_tel": "$fromPhone",
                    "tel": "$toPhone",
                    "name": "$toName",
                    "address": "$toAddress",
                    "province": "$toProvinceName",
                    "district": "$toDistrictName",
                    "ward": "$toWardName",
                    "hamlet": "Khác",
                    "is_freeship": "1",
                    "pick_date": "2016-09-30",
                    "pick_money": $totalCOD,
                    "note": "Khối lượng tính cước tối đa: 1.00 kg",
                    "value": $totalCOD,
                    "transport": "fly",
                    "pick_option":"cod",      
                    "pick_session" : 2,
                    "tags": [ 1]
                }
            }
            HTTP_BODY;
            // return ($order);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $urlGHTKTest."/services/shipment/order",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $order,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Token: ".env("TOKEN_GHTK"),
                    "Content-Length: " . strlen($order),
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response,true);
            if ($response['success']==false) {
                return $this->responseError($response['message']);
            }else{
                $data = $response["order"];
                $expectedDeliveryTime = $data["estimated_deliver_time"];
                $orderCode = $data["tracking_id"];
                $totalFee = $data["fee"];
                // $mainFee = $data["fee"]["main_service"];
    
                $shippingCompany = $this->model;
                $shippingCompany->code = $orderCode;
                $shippingCompany->shipping_company = "Giao hàng tiết kiệm";
                $shippingCompany->cost = $totalFee;
                $shippingCompany->estimated_shipping_time = date('Y-m-d H:i:s', strtotime($expectedDeliveryTime));
                $shippingCompany->save();
                $response["id"] = $shippingCompany->id;
                $response['total_fee'] = $totalFee;
                return $this->responseSuccess(null,$response);
            }
        }
        else{
            return $this->responseError("Action không hợp lệ! ");
        }

    }
    public function createShippingOrderGHN(Request $request){
        $input = $request->all();
        $action = Arr::get($input,'action','preview');

        // 
        if ($action == "preview" || $action == "p") {
            $url = 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/preview';
        }
        else if ($action == "create" || $action == "c"){
            $url = 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create';
        }
        else{
            return $this->responseError("Action không hợp lệ! ");
        }

        $userID = SERVICE::getCurrentUserId();
        $userInfo = User::find($userID);

        $cart = Cart::where('user_id',$userID)->first();
        $cartDetails = $cart->cartDetails;


        $product = [];
        foreach ($cartDetails as $item) {
            $product[] = [
                "name" => $item->product->product_name,
                "code" => (string) $item->product->id,
                "quantity" => $item->quantity,
                "length" => 12,
                "width" => 12,
                "weight" => 1200,
                "height" => 12,
            ];
        }
        $total = array_values(array_filter($cart->info,function($i) {
            if ($i["code"] == "total") {
                return $i;
            }
        }));
        if ($total[0]["value"] >= 5000000) {
            return $this->responseError("Số tiền không được vượt quá 5.000.000 đ");
        };
        // $cart = new CartResource($cart);
        // return $cart;

        $client = new Client();
        
        $headers = [
            'Content-Type' => 'application/json',
            'ShopId' => '189653',
            'Token' => '67b98e6a-5d08-11ee-a6e6-e60958111f48',
        ];

        // Thông tin người gửi
        $fromName = "Công ty Tmart";
        $fromPhone = "0379086875";
        $fromAddress = "233 - Nguyễn Văn Cừ - An Hòa - Ninh Kiều - TP. Cần Thơ";
        $fromWardName = "Phường An Hòa";
        $fromDistrictName = "Quận Ninh Kiều";
        $fromProvinceName = "Cần Thơ";

        // Thông tin người nhận
        $toName = $cart->name;
        $toPhone = $cart->phone;
        $toAddress = $cart->address;
        $toWardName = $cart->ward_name;
        $toDistrictName = $cart->district_name;
        $toProvinceName = $cart->city_name;

        $data = [
            "payment_type_id" => 2,
            "note" => "Tintest 123",
            "required_note" => "KHONGCHOXEMHANG", //CHOTHUHANG, CHOXEMHANGKHONGTHU, KHONGCHOXEMHANG
            // "return_phone" => "0332190158",
            // "return_address" => "39 NTT",
            // "return_district_id" => null,
            // "return_ward_code" => "",
            "client_order_code" => "",
            "from_name" => $fromName,
            "from_phone" => $fromPhone,
            "from_address" => $fromAddress,
            "from_ward_name" => $fromWardName,
            "from_district_name" => $fromDistrictName,
            "from_province_name" => $fromProvinceName,
            "to_name" => $toName,
            "to_phone" => $toPhone,
            "to_address" => $toAddress,
            "to_ward_name" => $toWardName,
            "to_district_name" => $toDistrictName,
            "to_province_name" => $toProvinceName,
            "cod_amount" => $total[0]["value"],
            "content" => Arr::get($input,'content',null),
            "weight" => 200,
            "length" => 1,
            "width" => 19,
            "height" => 10,
            // "cod_failed_amount" => 2000, //Thu thêm tiền khi giao thất bại          
            // "pick_station_id" => 1444,
            // "deliver_station_id" => null,
            "insurance_value" => $total[0]["value"],
            "service_id" => 0,
            "service_type_id" => 2,
            "coupon" => null,
            // "pickup_time" => 1692840132,
            // "pick_shift" => [2],
            "items" => $product
        ];

        $response = $client->request('POST', $url, [
            'headers' => $headers,
            'json' => $data,
        ]);

        $body = $response->getBody();
        $result = json_decode($body, true);

        // return $result;

        // 29/10/2023 Làm tới đây (Hoàn thành tạo đơn với GHN - Chưa lưu vào CSDL)

        if ($action == "create" || $action == "c"){
            if ($result["code"] == 200) {
                $data = $result["data"];
                $expectedDeliveryTime = $data["expected_delivery_time"];
                $orderCode = $data["order_code"];
                $totalFee = $data["total_fee"];
                // $mainFee = $data["fee"]["main_service"];
    
                $shippingCompany = $this->model;
                $shippingCompany->code = $orderCode;
                $shippingCompany->shipping_company = "Giao hàng nhanh";
                $shippingCompany->cost = $totalFee;
                $shippingCompany->estimated_shipping_time = date('Y-m-d H:i:s', strtotime($expectedDeliveryTime));
                $shippingCompany->save();
                $result["data"]["id"] = $shippingCompany->id;
                return $result;
            }
        }
        
        return $result;
        // ////////////////////////

        // $response = $client->post('https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee', [
        //     'headers' => [
        //         'Content-Type' => 'application/json',
        //         'Token' => '67b98e6a-5d08-11ee-a6e6-e60958111f48',
        //         'ShopId' => '189653',
        //     ],
        //     'json' => [
        //         "payment_type_id" => 2,
        //         "note" => "Tintest 123",
        //         "required_note" => "KHONGCHOXEMHANG",
        //         "return_phone" => "0332190158",
        //         "return_address" => "39 NTT",
        //         "return_district_id" => null,
        //         "return_ward_code" => "",
        //         "client_order_code" => "",
        //         "from_name" => "TinTest124",
        //         "from_phone" => "0987654321",
        //         "from_address" => "72 Thành Thái, Phường 14, Quận 10, Hồ Chí Minh, Vietnam",
        //         "from_ward_name" => "Phường 14",
        //         "from_district_name" => "Quận 10",
        //         "from_province_name" => "HCM",
        //         "to_name" => "TinTest124",
        //         "to_phone" => "0987654321",
        //         "to_address" => "72 Thành Thái, Phường 14, Quận 10, Hồ Chí Minh, Vietnam",
        //         "to_ward_name" => "Phường 14",
        //         "to_district_name" => "Quận 10",
        //         "to_province_name" => "HCM",
        //         "cod_amount" => 200000,
        //         "content" => "Theo New York Times",
        //         "weight" => 200,
        //         "length" => 1,
        //         "width" => 19,
        //         "height" => 10,
        //         "cod_failed_amount" => 2000,
        //         "pick_station_id" => 1444,
        //         "deliver_station_id" => null,
        //         "insurance_value" => 10000000,
        //         "service_id" => 0,
        //         "service_type_id" => 2,
        //         "coupon" => null,
        //         "pickup_time" => 1692840132,
        //         "pick_shift" => [2],
        //         "items" => [
        //             [
        //                 "name" => "Áo Polo",
        //                 "code" => "Polo123",
        //                 "quantity" => 1,
        //                 "price" => 200000,
        //                 "length" => 12,
        //                 "width" => 12,
        //                 "weight" => 1200,
        //                 "height" => 12,
        //                 "category" => [
        //                     "level1" => "Áo"
        //                 ]
        //             ]
        //         ]]
        // ]);

        // // Đọc dữ liệu từ phản hồi
        // $data = $response->getBody()->getContents();
        // return $data;
    }

    public function createShippingCompany(Request $request)
    {
        $this->validate($request,[
            "shipping_company" => "required|string",
            "contact" => "required|string",
            "estimated_shipping_time" => "required",
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là kiểu chuỗi",
        ]);
        $this->model->create($request->all());
        return $this->responseSuccess("Thêm shipping company thành công");
    }

    public function getShippingCompanyById($id)
    {
        $shippingCompany = ShippingCompany::find($id);
        if (empty($shippingCompany)) {
            return $this->responseError("Không tìm thấy shipping company với ID: $id");
        }
        return new ShippingCompanyResource($shippingCompany);
    }

    public function updateShippingCompany(Request $request, $id)
    {
        $this->validate($request,[
            "shipping_company" => "sometimes|required|string",
            "contact" => "sometimes|required|string",
            "estimated_shipping_time" => "sometimes|required",
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là kiểu chuỗi",
        ]);
        $shippingCompany = ShippingCompany::find($id);
        if (empty($shippingCompany)) {
            return $this->responseError("Không tìm thấy shipping company với ID: $id");
        }
        try {
            $result = $shippingCompany->update($request->all());
            if ($result) {
                return $this->responseSuccess("Không xảy ra lỗi trong quá trình cập nhật");
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }

    public function removeShippingCompany($id)
    {
        $shippingCompany = ShippingCompany::find($id);
        if (empty($shippingCompany)) {
            return $this->responseError("Không tìm thấy shipping company với ID: $id");
        }
        $shippingCompany->delete();
        return $this->responseSuccess("Xóa shipping company thành công");
    }
}
