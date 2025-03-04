<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\Order;
use App\Http\Controllers\V1\Auth\Models\Product;
use App\SERVICE;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        // $orders = Order::where('order_date','>=',$timeStr)->get();
        $orders = Order::where('order_date','>=',$timeStr)->where('status_code','delivered')->get();
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
                    $revenueDate[$orderDate]["profit"]+=$a['value'] > 10000 ? (int)(0.75*$a['value']):0;
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

    public function fakeDataComment(){
        $textData = [
            'Sản phẩm này thật tuyệt vời!',
            'Tôi thấy sản phẩm rất hữu ích cho công việc hàng ngày.',
            'Không hài lòng với hiệu suất của sản phẩm.',
            'Giá cả rất hợp lý so với chất lượng.',
            'Chắc chắn sẽ giới thiệu cho người khác.',
            'Thời lượng pin cần cải thiện.',
            'Sản phẩm vượt xa mong đợi của tôi.',
            'Thiết kế đẹp mắt và hiện đại.',
            'Gặp một số vấn đề về kết nối.',
            'Dịch vụ hỗ trợ khách hàng tuyệt vời.',
            'Sản phẩm hoạt động rất mượt mà.',
            'Chất lượng âm thanh rất tốt.',
            'Màn hình hiển thị sắc nét và rõ ràng.',
            'Sản phẩm quá đắt so với chất lượng.',
            'Tôi sẽ không mua lại sản phẩm này.',
            'Thời gian giao hàng rất nhanh chóng.',
            'Rất hài lòng với tính năng của sản phẩm.',
            'Sản phẩm dễ dàng sử dụng và cài đặt.',
            'Chất lượng hoàn thiện rất cao cấp.',
            'Tôi rất thích thiết kế của sản phẩm này.',
            'Có một số lỗi nhỏ nhưng không đáng kể.',
            'Tôi rất thất vọng với dịch vụ sau bán hàng.',
            'Sản phẩm này thực sự xứng đáng với số tiền bỏ ra.',
            'Tôi đã sử dụng sản phẩm này hàng ngày và rất hài lòng.',
            'Nên cải thiện phần mềm để sản phẩm hoạt động tốt hơn.',
        ];
        for ($i=0; $i < 100; $i++) { 
        // }
        // foreach ([3,4] as $key) {
            $data = [
                // "product_id"=> $key,
                "product_id"=> rand(1,310),
                "text"=> $textData[rand(0,count($textData)-1)],
                "rating"=> rand(1,5),
                // "image_url"=> [
                //     "1"=>"www",
                //     "2"=>"www2"
                // ],
                "parent_id"=>null,
                // "test"=>true
            ];
            $request = new Request($data);
            $comment = new CommentController();
            $result = $comment->addComment($request);
            echo "product_id: ".$data['product_id'];
            echo "\ntext: ".$data['text'];
            echo "\nrating: ".$data['rating']."\n";
            echo $result;
            echo "\n ------------------------------------ ";
        }
        
    }
}