<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\UserModel;
use App\Http\Controllers\V1\Auth\Resources\User\UserCollection;
use App\Http\Controllers\V1\Auth\Resources\User\UserResource;
use App\Http\Controllers\V1\Auth\Validators\UserChangePasswordValidator;
use App\Http\Controllers\V1\Auth\Validators\UserCreateValidator;
use App\Http\Controllers\V1\Auth\Validators\UserUpdateValidator;
use App\SERVICE;
use App\Supports\Log;
use App\Supports\SERVICE_Email;
use App\Supports\SERVICE_Error;
use App\Supports\Message;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class UserController
 *
 * @package App\V1\CMS\Controllers
 */
class UserController extends BaseController
{

    /**
     * @var UserModel
     */
    protected UserModel $model;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->model = new UserModel();
    }


    public function search(Request $request)
    {
        $input  = $request->all();
        $limit  = array_get($input, 'limit', 20);
        $result = $this->model->search($input, [], $limit);
        Log::view($this->model->getTable());
        return new UserCollection($result);
    }

    public function view($id)
    {
        $result = User::find($id);
        if (empty($result)) {
            return ['data' => null];
        }
        Log::view($this->model->getTable());
        return new UserResource($result);
    }

    public function viewUserFromToken()
    {
        $userID = SERVICE::getCurrentUserId();
        $result = User::find($userID);
        if (empty($result)) {
            return ['data' => null];
        }
        Log::view($this->model->getTable());
        return new UserResource($result);
    }

    public function updateUserFromToken(Request $request)
    {
        $userID = SERVICE::getCurrentUserId();

        $input       = $request->all();
        $input['id'] = $userID;
        
        try {
            DB::beginTransaction();
            $result = $this->model->upsert($input);
            Log::update($this->model->getTable(), $result->code);
            DB::commit();
            return $this->responseSuccess("Thành công");
        } catch (\Exception $ex) {
            DB::rollBack();
            $response = SERVICE_Error::handle($ex);
            return $this->responseError($response['message']);
        }
    }

    /**
     * @param Request $request
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $input = $request->all();
        (new UserCreateValidator())->validate($input);

        try {
            DB::beginTransaction();
            $result = $this->model->upsert($input);
            Log::create($this->model->getTable(), $result->code);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            $response = SERVICE_Error::handle($ex);
            return $this->responseError($response['message']);
        }
        return new UserResource($result);
    }

    /**
     * @param $id
     * @param Request $request
     * @param UserUpdateValidator $userUpdateValidator
     * @param UserTransformer $userTransformer
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function update(
        $id,
        Request $request,
        UserUpdateValidator $userUpdateValidator,
    )
    {
        $input       = $request->all();
        $input['id'] = $id;
        $userUpdateValidator->validate($input);
        try {
            DB::beginTransaction();
            $result = $this->model->upsert($input);
            Log::update($this->model->getTable(), $result->code);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            $response = SERVICE_Error::handle($ex);
            return $this->responseError($response['message']);
        }

        return new UserResource($result);
    }

    /**
     * @param Request $request
     * @param UserChangePasswordValidator $userChangePasswordValidator
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request, UserChangePasswordValidator $userChangePasswordValidator)
    {
        $input      = $request->all();
        $user_id    = SERVICE::getCurrentUserId();
        // $company_id = SERVICE::getCurrentCompanyId();
        $userChangePasswordValidator->validate($input);
        if ($request->new_password != $request->password_new_confirmation) {
            return $this->responseError("Mật khẩu không giống nhau");
        }
        try {
            DB::beginTransaction();
            $user = User::where([
                'id'         => $user_id,
                // 'company_id' => $company_id
            ])->first();
            // Change password
            if (!password_verify($input['password'], $user->password)) {
                throw new \Exception(Message::get("V002", Message::get("password")));
            }
            $user->password = password_hash($input['new_password'], PASSWORD_BCRYPT);
            $user->save();

            Log::update($this->model->getTable());
            DB::commit();
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            return $this->responseError($response['message']);
        }
        return ['status' => Message::get("users.change-password")];
    }

    /**
     * @param Request $request
     * @return array|bool
     */
    public function resetPassword(Request $request)
    {
        $input       = $request->all();
        $userCheck   = User::where('email', $input['email'])->first();
        $verify_code = mt_rand(100000, 999999);
        $param       = [
            'verify_code'  => $verify_code,
            'expired_code' => date('Y-m-d H:i:s', strtotime("+5 minutes")),
        ];
        if (!empty($userCheck)) {
            $userCheck->update($param);

            $paramSendMail = [
                'username'    => $userCheck->username,
                'verify_code' => $verify_code,
            ];

            SERVICE_Email::send('mail_send_reset_password', $userCheck->email, $paramSendMail);
            return ['status' => Message::get("users.reset-password-success")];
        } else {
            return false;
        }
    }
    public function confirmPhone(Request $request){
        $this->validate($request,[
            "phone" => "required"
        ]);
        $userPhoneCheck = User::where('phone',$request->phone)->where('deleted_at',null)->exists();
        if (empty($userPhoneCheck)) {
            return $this->responseSuccess(null,["status"=>false]);
        }
        else{
            return $this->responseSuccess(null,["status"=>true]);
        }
    }
    public function uploadAvatar(Request $request)
    {
        $userId = SERVICE::getCurrentUserId();
        // $userId = auth()->id();
        // Kiểm tra đuôi
        // Kiểm tra xem tệp đã được tải lên hay chưa
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $domain = "https://tmart.tuanthanhdev.id.vn";
            $imagePath = $domain.'/uploads/' . $imageName;
            // Lưu tệp vào thư mục lưu trữ
            $image->move(public_path('uploads'), $imageName);

            $avatar = User::find($userId)->update([
                'avatar' => $imagePath
            ]);
            // $avatar = DB::table("users")->where('id',auth()->id())->update([
            //     'avatar'=>$imagePath
            // ]);
            if($avatar){
                return $this->responseSuccess("Cập nhật ảnh đại diện thành công");
            }
            else{
                return $this->responseError("Không cập nhật được ảnh đại diện");
            }
        }
        return $this->responseError("Không có tệp nào được tải lên!");
    }
}
