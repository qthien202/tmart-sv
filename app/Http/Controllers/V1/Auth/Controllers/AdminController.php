<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\Order;
use App\Http\Controllers\V1\Auth\Models\Product;
use App\SERVICE;
use App\User;
use Carbon\Carbon;

class AdminController extends BaseController
{
    protected $model;

    public function __construct()
    {
        // $this->model = new AddressBook();
    }

    public function statistical(){
        $numUser = User::where('role_id',2)->where('is_active',1) ->count();
        // $numOrder = Order::where('status_code','delivered')->count();
        $numOrder = Order::count();
        $order = Order::where('status_code','delivered')->get();
        $totalRevenue = 0;
        foreach ($order as $item) {
            // dd($item->info_total_amount);
            array_filter($item->info_total_amount,function($a) use (&$totalRevenue){
                if($a['code'] == "total"){
                    $totalRevenue+=$a['value'];
                }
            });
        }
        $views = Product::sum('views');
        $data = [
            'num_user' =>$numUser,
            'num_order' => $numOrder,
            'revenue'=>$totalRevenue,
            'view' =>$views,
        ];
        return $this->responseSuccess(null,$data);
    }

    public function chartData(){
        // Order::where('status_code','delivered')
        Carbon::setLocale('vi');
        $timeNow = Carbon::now();
        $time =$timeNow->subWeek();
        $timeStr = $time->toDateString();
        $orders = Order::where('order_date','>=',$timeStr)->get();
        $revenueDate = [];
        for ($i=0; $i < 8; $i++) { 
            // Chưa tối ưu
            $date =  Carbon::now()->subWeek()->addDays($i);
            $dateStr = $date->toDateString();
            $revenueDate[$dateStr] = ["revenue"=>0,"profit"=>0];
        }
        // [Doanh thu, loi nhuan (test)]
        foreach ($orders as $order) {
            $orderDate = $order->order_date;
            array_filter($order->info_total_amount,function($a) use (&$revenueDate,$orderDate){
                if($a['code'] == "total"){
                    $revenueDate[$orderDate]["revenue"]+=(int)($a['value']);
                    $revenueDate[$orderDate]["profit"]+=$a['value'] > 10000 ? (int)(0.95*$a['value']):0;
                }
            });
        }
        $data = [];
        foreach ($revenueDate as $key => $value) {
            $data[] = ["date"=>$key,"revenue"=>$value["revenue"],"profit"=>$value["profit"]];
        }

        return $this->responseSuccess(null,$data);
    }

    public function checkAdmin(){
        if(SERVICE::isAdminUser()){
            return $this->responseSuccess(null,["role"=>"admin"]);
            // return response()->json(["role"=>"admin"],200);
        }else{
            return $this->responseSuccess(null,["role"=>"user"]);
            // return response()->json(["role"=>"user"],200);
        }
    }
}