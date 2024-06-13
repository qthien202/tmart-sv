<?php
namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Normal\Models\Condition;
use App\Http\Controllers\V1\Normal\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Voucher();
    }

    public function createVoucher(Request $request){
        $voucher = $this->validate($request,[
            "voucher_code" => "required|max:20",
            "voucher_value" => "required",
            "voucher_type" => "required",
            "title" => "required",
            "voucher_date_start" => "sometimes|required",
            "voucher_date_end" => "sometimes|required",
        ]);
        $condition = $this->validate($request,[
            "max_discoun" => "sometimes|required",
            "min_order_amount" => "sometimes|required",
            "first_order_only" => "sometimes|required",
            "mobile_app_only" => "sometimes|required",
            "for_loged_in_users" => "sometimes|required",
            "applicable_product" => "sometimes|required",
        ]);
        DB::beginTransaction();
        try {
            $this->model->create($voucher);
            
            $condition = new Condition();
            $condition->voucher_code = $request?->voucher_code;
            $condition->max_discoun = $request?->max_discoun;
            $condition->min_order_amount = $request?->min_order_amount ;
            $condition->first_order_only = $request?->first_order_only ;
            $condition->mobile_app_only = $request?->mobile_app_only ;
            $condition->for_loged_in_users = $request?->for_loged_in_users ;
            $condition->applicable_product = $request?->applicable_product ;
            $condition->save();

            DB::commit();
            return $this->responseSuccess("Thêm thành công");
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseError($th->getMessage());
        }
        
        
        return "aa";
    }
}