<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\ShippingCompany;
use App\Http\Controllers\V1\Auth\Resources\ShippingCompany\ShippingCompanyCollection;
use App\Http\Controllers\V1\Auth\Resources\ShippingCompany\ShippingCompanyResource;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

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

    public function createShippingOrder(){
        $client = new Client();
        // $url = 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/preview';
        $url = 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create';

        $headers = [
            'Content-Type' => 'application/json',
            'ShopId' => '189653',
            'Token' => '67b98e6a-5d08-11ee-a6e6-e60958111f48',
        ];

        $data = [
            "payment_type_id" => 2,
            "note" => "Tintest 123",
            "required_note" => "KHONGCHOXEMHANG",
            "return_phone" => "0332190158",
            "return_address" => "39 NTT",
            "return_district_id" => null,
            "return_ward_code" => "",
            "client_order_code" => "",
            "from_name" => "TinTest124",
            "from_phone" => "0987654321",
            "from_address" => "72 Thành Thái, Phường 14, Quận 10, Hồ Chí Minh, Vietnam",
            "from_ward_name" => "Phường 14",
            "from_district_name" => "Quận 10",
            "from_province_name" => "HCM",
            "to_name" => "TinTest124",
            "to_phone" => "0987654321",
            "to_address" => "30/4, Khóm 1, Phường 3, TP Sóc Trăng, Sóc Trăng, Vietnam",
            "to_ward_name" => "Phường 3",
            "to_district_name" => "Thành phố Sóc Trăng",
            "to_province_name" => "Sóc Trăng",
            "cod_amount" => 200000,
            "content" => "Theo New York Times",
            "weight" => 200,
            "length" => 1,
            "width" => 19,
            "height" => 10,
            "cod_failed_amount" => 2000,                  
            "pick_station_id" => 1444,
            "deliver_station_id" => null,
            "insurance_value" => 900000,
            "service_id" => 0,
            "service_type_id" => 2,
            "coupon" => null,
            "pickup_time" => 1692840132,
            "pick_shift" => [2],
            "items" => [
                [
                    "name" => "Áo Polo",
                    "code" => "Polo123",
                    "quantity" => 1,
                    "price" => 200000,
                    "length" => 12,
                    "width" => 12,
                    "weight" => 1200,
                    "height" => 12,
                    "category" => [
                        "level1" => "Áo"
                    ]
                ]
            ]
        ];

        $response = $client->request('POST', $url, [
            'headers' => $headers,
            'json' => $data,
        ]);

        $body = $response->getBody();
        $result = json_decode($body, true);
        // Dữ liệu trả về
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
            
            return $this->responseSuccess("Tạo đơn thành công");
        }
        

        // ////////////////////////

        $response = $client->post('https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Token' => '67b98e6a-5d08-11ee-a6e6-e60958111f48',
                'ShopId' => '189653',
            ],
            'json' => [
                "payment_type_id" => 2,
                "note" => "Tintest 123",
                "required_note" => "KHONGCHOXEMHANG",
                "return_phone" => "0332190158",
                "return_address" => "39 NTT",
                "return_district_id" => null,
                "return_ward_code" => "",
                "client_order_code" => "",
                "from_name" => "TinTest124",
                "from_phone" => "0987654321",
                "from_address" => "72 Thành Thái, Phường 14, Quận 10, Hồ Chí Minh, Vietnam",
                "from_ward_name" => "Phường 14",
                "from_district_name" => "Quận 10",
                "from_province_name" => "HCM",
                "to_name" => "TinTest124",
                "to_phone" => "0987654321",
                "to_address" => "72 Thành Thái, Phường 14, Quận 10, Hồ Chí Minh, Vietnam",
                "to_ward_name" => "Phường 14",
                "to_district_name" => "Quận 10",
                "to_province_name" => "HCM",
                "cod_amount" => 200000,
                "content" => "Theo New York Times",
                "weight" => 200,
                "length" => 1,
                "width" => 19,
                "height" => 10,
                "cod_failed_amount" => 2000,
                "pick_station_id" => 1444,
                "deliver_station_id" => null,
                "insurance_value" => 10000000,
                "service_id" => 0,
                "service_type_id" => 2,
                "coupon" => null,
                "pickup_time" => 1692840132,
                "pick_shift" => [2],
                "items" => [
                    [
                        "name" => "Áo Polo",
                        "code" => "Polo123",
                        "quantity" => 1,
                        "price" => 200000,
                        "length" => 12,
                        "width" => 12,
                        "weight" => 1200,
                        "height" => 12,
                        "category" => [
                            "level1" => "Áo"
                        ]
                    ]
                ]]
        ]);

        // Đọc dữ liệu từ phản hồi
        $data = $response->getBody()->getContents();
        return $data;
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
