<?php

namespace App\Http\Controllers\V1\Normal\Controllers;

use App\Events\SessionCreated;
use App\Http\Controllers\V1\Normal\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SessionController extends BaseController
{
    protected $model;
    public function __construct()
    {
        $this->model = new Session();

    }
    public function setSession(){
        $sessionID = strtoupper(uniqid() . time());

        $this->model->session_id=$sessionID;
        $this->model->save();
        // $guest = new Session();
        // $guest->session_id=$sessionID ;
        // $guest->save();
        return response()->json(["session_id"=>$sessionID],200);
        // $this->responseSuccess(null,$sessionID);        
    }
    public function removeSession(Request $request){
        $this->validate($request,[
            "guest_session" => "required|exists:sessions,session_id"
        ],[
            "required" => "Trường :attribute bắt buộc",
            "exists" => "Session không tồn tại"
        ]);
        $result = Session::where("session_id",$request->guest_session)->delete();
        if ($result>0) {
            return $this->responseSuccess("Xóa session thành công");
        }
        return $this->responseError("Xóa session không thành công");
    }
    
}
