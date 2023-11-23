<?php

namespace App\Http\Controllers\V1\Normal\Controllers;

use App\Http\Controllers\V1\Auth\Models\Order;
use Exception;
use Illuminate\Http\Request;

class VNPAYController extends BaseController
{
    // protected $model;
    // public function __construct()
    // {
    //     $this->model = new Category();
    // }
    protected $domain;
    public function __construct()
    {
        $this->domain = "http://tmart-sv.test";
    }
    // public function vnpayCreatePayment(Request $request)
    // {
    //     $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    //     $vnp_Returnurl = $this->domain."/api/auth/vnpay_return";
    //     $vnp_TmnCode = "IC8XEJH8";//Mã website tại VNPAY 
    //     $vnp_HashSecret = "MWDHLTWKCLXINXMAHXLLYZXYWCDAFQDE"; //Chuỗi bí mật

    //     $vnp_TxnRef = 10009; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
    //     $vnp_OrderInfo = "Thanh toán đơn hàng";
    //     $vnp_OrderType = "TMart";
    //     $vnp_Amount = 10000 * 100;
    //     $vnp_Locale = 'vn';
    //     $vnp_BankCode = "NCB";
    //     $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
       
    //     $inputData = array(
    //         "vnp_Version" => "2.1.0",
    //         "vnp_TmnCode" => $vnp_TmnCode,
    //         "vnp_Amount" => $vnp_Amount,
    //         "vnp_Command" => "pay",
    //         "vnp_CreateDate" => date('YmdHis'),
    //         "vnp_CurrCode" => "VND",
    //         "vnp_IpAddr" => $vnp_IpAddr,
    //         "vnp_Locale" => $vnp_Locale,
    //         "vnp_OrderInfo" => $vnp_OrderInfo,
    //         "vnp_OrderType" => $vnp_OrderType,
    //         "vnp_ReturnUrl" => $vnp_Returnurl,
    //         "vnp_TxnRef" => $vnp_TxnRef
    //     );

    //     if (isset($vnp_BankCode) && $vnp_BankCode != "") {
    //         $inputData['vnp_BankCode'] = $vnp_BankCode;
    //     }
    //     if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
    //         $inputData['vnp_Bill_State'] = $vnp_Bill_State;
    //     }

    //     //var_dump($inputData);
    //     ksort($inputData);
    //     $query = "";
    //     $i = 0;
    //     $hashdata = "";
    //     foreach ($inputData as $key => $value) {
    //         if ($i == 1) {
    //             $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    //         } else {
    //             $hashdata .= urlencode($key) . "=" . urlencode($value);
    //             $i = 1;
    //         }
    //         $query .= urlencode($key) . "=" . urlencode($value) . '&';
    //     }

    //     $vnp_Url = $vnp_Url . "?" . $query;
    //     if (isset($vnp_HashSecret)) {
    //         $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//  
    //         $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
    //     }
    //     $returnData = array('code' => '00'
    //         , 'message' => 'success'
    //         , 'data' => $vnp_Url);
    //         // if (isset($_POST['redirect'])) {
    //         if (false) {
    //             header('Location: ' . $vnp_Url);
    //             die();
    //         } else {
    //             echo json_encode($returnData);
    //         }
    //         // vui lòng tham khảo thêm tại code demo
    // }

    public function vnpayIPNReturn(Request $rq){
        $inputData = $rq->all();
        $orderId = $inputData['vnp_TxnRef'];

        


        $order = Order::find($orderId);
        $orderPayment = $order->orderPayments;
        $orderPayment["payment_status"] = "test"; 
        $orderPayment->save();
        $returnData['RspCode'] = '00';
        $returnData['Message'] = 'Confirm Success';
        echo json_encode($returnData);
        return;
        // dd($orderPayment['payment_status']);
        // $vnp_Amount = $inputData['vnp_Amount']/100;
        // dd($orderPayment["amount"] == $vnp_Amount);


        /* Payment Notify
        * IPN URL: Ghi nhận kết quả thanh toán từ VNPAY
        * Các bước thực hiện:
        * Kiểm tra checksum 
        * Tìm giao dịch trong database
        * Kiểm tra số tiền giữa hai hệ thống
        * Kiểm tra tình trạng của giao dịch trước khi cập nhật
        * Cập nhật kết quả vào Database
        * Trả kết quả ghi nhận lại cho VNPAY
        */

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = $this->domain."/api/auth/vnpay_return";
        $vnp_TmnCode = "IC8XEJH8";//Mã website tại VNPAY 
        $vnp_HashSecret = "MWDHLTWKCLXINXMAHXLLYZXYWCDAFQDE"; //Chuỗi bí mật

        $inputData = $rq->all();
        $returnData = $rq->all();

        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
        $vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán
        $vnp_Amount = $inputData['vnp_Amount']/100; // Số tiền thanh toán VNPAY phản hồi

        $Status = 0; // Là trạng thái thanh toán của giao dịch chưa có IPN lưu tại hệ thống của merchant chiều khởi tạo URL thanh toán.
        $orderId = $inputData['vnp_TxnRef'];

        try {
            //Check Orderid    
            //Kiểm tra checksum của dữ liệu
            if ($secureHash == $vnp_SecureHash) {
                //Lấy thông tin đơn hàng lưu trong Database và kiểm tra trạng thái của đơn hàng, mã đơn hàng là: $orderId            
                //Việc kiểm tra trạng thái của đơn hàng giúp hệ thống không xử lý trùng lặp, xử lý nhiều lần một giao dịch
                //Giả sử: $order = mysqli_fetch_assoc($result);   

                // $order = NULL;
                $order = Order::find($orderId);
                $orderPayment = $order->orderPayments;

                if ($order != NULL) {
                    if($orderPayment["amount"] == $vnp_Amount) //Kiểm tra số tiền thanh toán của giao dịch: giả sử số tiền kiểm tra là đúng. //$order["Amount"] == $vnp_Amount
                    // if(true) //Giả sử đúng giá tiền
                    {
                        if ($orderPayment["payment_status"] != NULL && $order["payment_status"] == "Chờ xác nhận") {
                            if ($inputData['vnp_ResponseCode'] == '00' || $inputData['vnp_TransactionStatus'] == '00') {
                                $Status = 1;
                                $orderPayment["paydate"] = $inputData["vnp_PayDate"];
                                $orderPayment["bank_tran_no"] = $inputData["vnp_BankTranNo"];
                                $orderPayment["bank_code"] = $inputData["vnp_BankCode"];
                                $orderPayment["payment_status"] = "Thành công"; // Trạng thái thanh toán thành công
                                $orderPayment->save();
                            } else {
                                $Status = 2;
                                $orderPayment["payment_status"] = "Thất bại"; // Trạng thái thanh toán thất bại / lỗi
                                $orderPayment->save();
                            }
                            //Cài đặt Code cập nhật kết quả thanh toán, tình trạng đơn hàng vào DB
                            //
                            //
                            //
                            //Trả kết quả về cho VNPAY: Website/APP TMĐT ghi nhận yêu cầu thành công                
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Confirm Success';
                        } else {
                            $returnData['RspCode'] = '02';
                            $returnData['Message'] = 'Order already confirmed';
                        }
                    }
                    else {
                        $returnData['RspCode'] = '04';
                        $returnData['Message'] = 'invalid amount';
                    }
                } else {
                    $returnData['RspCode'] = '01';
                    $returnData['Message'] = 'Order not found';
                }
            } else {
                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Invalid signature';
            }
        } catch (Exception $e) {
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknow error';
        }
        //Trả lại VNPAY theo định dạng JSON
        echo json_encode($returnData);
    }

    public function vnpayReturn(){
        
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        /*
        * To change this license header, choose License Headers in Project Properties.
        * To change this template file, choose Tools | Templates
        * and open the template in the editor.
        */
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = $this->domain."/api/normal/vnpay_return";
        $vnp_TmnCode = "IC8XEJH8";//Mã website tại VNPAY 
        $vnp_HashSecret = "MWDHLTWKCLXINXMAHXLLYZXYWCDAFQDE"; //Chuỗi bí mật

        $vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
        $apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction";
        //Config input format
        //Expire
        $startTime = date("YmdHis");
        $expire = date('YmdHis',strtotime('+15 minutes',strtotime($startTime)));

        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                echo $hashData;
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                echo $hashData;
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        if ($secureHash == $vnp_SecureHash) {
            if ($_GET['vnp_ResponseCode'] == '00') {
                echo "GD Thanh cong";
            } 
            else {
                echo "GD Khong thanh cong";
                }
        } else {
            echo "Chu ky khong hop le";
            }
		
    }

    public function IPN(){
        

    /* Payment Notify
    * IPN URL: Ghi nhận kết quả thanh toán từ VNPAY
    * Các bước thực hiện:
    * Kiểm tra checksum 
    * Tìm giao dịch trong database
    * Kiểm tra số tiền giữa hai hệ thống
    * Kiểm tra tình trạng của giao dịch trước khi cập nhật
    * Cập nhật kết quả vào Database
    * Trả kết quả ghi nhận lại cho VNPAY
    */

    $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    $vnp_Returnurl = $this->domain."/api/auth/vnpay_return";
    $vnp_TmnCode = "IC8XEJH8";//Mã website tại VNPAY 
    $vnp_HashSecret = "MWDHLTWKCLXINXMAHXLLYZXYWCDAFQDE"; //Chuỗi bí mật

    $vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
    $apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction";
    $inputData = array();
    $returnData = array();

    foreach ($_GET as $key => $value) {
        if (substr($key, 0, 4) == "vnp_") {
            $inputData[$key] = $value;
        }
    }

    $vnp_SecureHash = $inputData['vnp_SecureHash'];
    unset($inputData['vnp_SecureHash']);
    ksort($inputData);
    $i = 0;
    $hashData = "";
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }

    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    $vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
    $vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán
    $vnp_Amount = $inputData['vnp_Amount']/100; // Số tiền thanh toán VNPAY phản hồi

    $Status = 0; // Là trạng thái thanh toán của giao dịch chưa có IPN lưu tại hệ thống của merchant chiều khởi tạo URL thanh toán.
    $orderId = $inputData['vnp_TxnRef'];

    try {
        //Check Orderid    
        //Kiểm tra checksum của dữ liệu
        if ($secureHash == $vnp_SecureHash) {
            //Lấy thông tin đơn hàng lưu trong Database và kiểm tra trạng thái của đơn hàng, mã đơn hàng là: $orderId            
            //Việc kiểm tra trạng thái của đơn hàng giúp hệ thống không xử lý trùng lặp, xử lý nhiều lần một giao dịch
            //Giả sử: $order = mysqli_fetch_assoc($result);   

            $order = NULL;
            if ($order != NULL) {
                if($order["Amount"] == $vnp_Amount) //Kiểm tra số tiền thanh toán của giao dịch: giả sử số tiền kiểm tra là đúng. //$order["Amount"] == $vnp_Amount
                {
                    if ($order["Status"] != NULL && $order["Status"] == 0) {
                        if ($inputData['vnp_ResponseCode'] == '00' || $inputData['vnp_TransactionStatus'] == '00') {
                            $Status = 1; // Trạng thái thanh toán thành công
                        } else {
                            $Status = 2; // Trạng thái thanh toán thất bại / lỗi
                        }
                        //Cài đặt Code cập nhật kết quả thanh toán, tình trạng đơn hàng vào DB
                        //
                        //
                        //
                        //Trả kết quả về cho VNPAY: Website/APP TMĐT ghi nhận yêu cầu thành công                
                        $returnData['RspCode'] = '00';
                        $returnData['Message'] = 'Confirm Success';
                    } else {
                        $returnData['RspCode'] = '02';
                        $returnData['Message'] = 'Order already confirmed';
                    }
                }
                else {
                    $returnData['RspCode'] = '04';
                    $returnData['Message'] = 'invalid amount';
                }
            } else {
                $returnData['RspCode'] = '01';
                $returnData['Message'] = 'Order not found';
            }
        } else {
            $returnData['RspCode'] = '97';
            $returnData['Message'] = 'Invalid signature';
        }
    } catch (Exception $e) {
        $returnData['RspCode'] = '99';
        $returnData['Message'] = 'Unknow error';
    }
    //Trả lại VNPAY theo định dạng JSON
    echo json_encode($returnData);
            
    }

    // public function search(Request $request)
    // {
    //     $categories = $this->model->search($request->all());
    //     return new CategoryCollection($categories);
    // }
    // public function detailById($id)
    // {
    //     $category = $this->model->find($id);
    //     if (!$category) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Category không tồn tại'
    //         ], 400);
    //     }
    //     return new CategoryResource($category);
    // }
    // public function detailByCode($code)
    // {
    //     $category = $this->model->where('code', $code)->first();
    //     if (!$category) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Category không tồn tại'
    //         ], 400);
    //     }
    //     return new CategoryResource($category);
    // }
    // public function create(Request $request)
    // {
    //     $attributes = $this->validate($request, [
    //         'code' => ['required', 'string', 'max:50', function ($attribute, $value, $fail) {
    //             $category = $this->model->where('code', $value)->first();
    //             if ($category) {
    //                 $fail('Mã category [' . $value . '] đã tồn tại');
    //             }
    //         }],
    //         'name' => 'required|string|max:100',
    //         'parent_id' => 'nullable|integer|exists:categories,id,deleted_at,NULL',
    //     ], [
    //         'code.required' => 'Mã category không được để trống',
    //         'code.unique' => 'Mã category đã tồn tại',
    //         'code.string' => 'Mã category phải là chuỗi',
    //         'code.max' => 'Mã category không được quá 50 ký tự',
    //         'name.required' => 'Tên category không được để trống',
    //         'name.string' => 'Tên category phải là chuỗi',
    //         'name.max' => 'Tên category không được quá 100 ký tự',
    //         'parent_id.integer' => 'Id danh mục cha phải là số nguyên',
    //         'parent_id.exists' => 'Id danh mục cha không tồn tại',
    //     ]);
    //     $attributes['slug'] = str_slug($attributes['name'] . '-' . $attributes['code']);

    //     $category = $this->model->create($attributes);
    //     return new CategoryResource($category);
    // }

    // public function update($id, Request $request)
    // {
    //     $attributes = $this->validate($request, [
    //         'code' => ['required', 'string', 'max:50', function ($attribute, $value, $fail) use ($id) {
    //             $category = $this->model->where('code', $value)->where('id', '!=', $id)->first();
    //             if ($category) {
    //                 $fail('Mã category [' . $value . '] đã tồn tại');
    //             }
    //         }],
    //         'name' => 'required|string|max:100',
    //         'parent_id' => 'nullable|integer|exists:categories,id,deleted_at,NULL',
    //     ], [
    //         'code.required' => 'Mã category không được để trống',
    //         'code.unique' => 'Mã category đã tồn tại',
    //         'code.string' => 'Mã category phải là chuỗi',
    //         'code.max' => 'Mã category không được quá 50 ký tự',
    //         'name.required' => 'Tên category không được để trống',
    //         'name.string' => 'Tên category phải là chuỗi',
    //         'name.max' => 'Tên category không được quá 100 ký tự',
    //         'parent_id.integer' => 'Id danh mục cha phải là số nguyên',
    //         'parent_id.exists' => 'Id danh mục cha không tồn tại',
    //     ]);
    //     $attributes['slug'] = str_slug($attributes['name'] . '-' . $attributes['code']);

    //     $category = $this->model->find($id);
    //     $category->update($attributes);
    //     return new CategoryResource($category);
    // }
    // public function delete($id)
    // {
    //     $category = $this->model->find($id);
    //     if (!$category) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Category không tồn tại'
    //         ], 400);
    //     }
    //     $category->delete();
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Xóa category thành công'
    //     ], 200);
    // }
}
