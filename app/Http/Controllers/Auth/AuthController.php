<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\V1\Auth\Models\UserModel;
use App\Http\Controllers\V1\Normal\Controllers\BaseController;
use App\Http\Validators\CMSLoginValidator;
use App\Http\Validators\VerifyRegisterValidator;
use App\Jobs\SendMailRegister;
use App\SERVICE;
use App\Http\Validators\RegisterValidator;
use App\Http\Validators\LoginValidator;
use App\Supports\SERVICE_Error;
use App\Supports\Message;
use App\User;
use App\UserSession;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;

class AuthController extends BaseController
{
    protected static $_user_type_user;
    protected static $_user_expired_day;
    /**
     *
     */
    protected $jwt;

    protected $model;

    /**
     * AuthController constructor.
     */
    public function __construct(FacadesJWTAuth $jwt)
    {
        $this->jwt               = $jwt;
        self::$_user_type_user   = "USER";
        self::$_user_expired_day = 365;
        $this->model             = new UserModel();
    }

    /**
     * @param Request $request
     * @param LoginValidator $loginValidator
     *
     * @return mixed
     * @throws \Exception
     */
    public function authenticate(Request $request)
    {
        $input = $request->all();

        (new CMSLoginValidator)->validate($input);
       
        $credentials = $request->only('phone', 'password');

        try {
            $token = auth()->attempt($credentials);

            if (!$token) {
                return $this->responseError(Message::get("users.admin-login-invalid"), 401);
            }

            $user = User::where(['phone' => $input['phone']])->first();
            if(!empty($request->role)){
                if(!($user->role_id == 1)){#admin
                    return $this->responseError("Tài khoản không có quyền ADMIN", 401);
                }
            }

            if (empty($user)) {
                return $this->responseError(Message::get("users.admin-login-invalid"), 401);
            }

            if ($user->is_active == "0") {
                return $this->responseError(Message::get("users.user-inactive"), 401);
            }
            // Write User Session
            $now = time();
            UserSession::where('user_id', $user->id)->update([
                'deleted'    => 1,
                'updated_at' => date("Y-m-d H:i:s", $now),
                'updated_by' => Auth::id(),
            ]);
            UserSession::where('user_id', $user->id)->delete();

            $device_type = array_get($input, 'device_type', 'UNKNOWN');
            UserSession::insert([
                'user_id'     =>Auth::id(),
                'token'       => $token,
                'login_at'    => date("Y-m-d H:i:s", $now),
                'expired_at'  => date("Y-m-d H:i:s", ($now + config('jwt.ttl') * 60)),
                'device_type' => $device_type,
                'device_id'   => array_get($input, 'device_id'),
                'deleted'     => 0,
                'created_at'  => date("Y-m-d H:i:s", $now),
                'created_by'  => Auth::id(),
            ]);
        } catch (JWTException $e) {
            return response()->json(['errors' => [[$e->getMessage()]]], 500);
        } catch (\Exception $ex) {
            return response()->json(['errors' => [[$ex->getMessage()]]], 500);
        }

        // All good so return the token
        return response()->json(compact('token'));
    }

    /**
     * @param Request $request
     * @param RegisterValidator $registerValidator
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function register(Request $request)
    {
        $input                = $request->all();
        (new RegisterValidator)->validate($input);

        try {
            DB::beginTransaction();
            $phone = str_replace(" ", "", $input['phone']);
            $phone = preg_replace('/\D/', '', $phone);
            $names = explode(" ", trim($input['name']));
            $first = $names[0];
            unset($names[0]);
            $last        = !empty($names) ? implode(" ", $names) : null;
            $email       = Arr::get($input, 'email', null);
            $verify_code = mt_rand(100000, 999999);
            $param       = [
                'phone'        => $phone,
                'code'         => $phone,
                'username'     => $phone,
                'first_name'   => $first,
                'last_name'    => $last,
                'short_name'   => $input['name'],
                'full_name'    => $input['name'],
                'email'        => $email,
                'verify_code'  => $verify_code,
                'role_id'      => 2,
                'expired_code' => date('Y-m-d H:i:s', strtotime("+5 minutes")),
                'password'     => password_hash($input['password'], PASSWORD_BCRYPT),
                'genre'        => array_get($input, 'genre', 'O'),
                'address'      => array_get($input, 'address', null),
                'is_active'    => 1,
            ];
            // Create User
            $user = $this->model->create($param);
            // Send Mail
            // if ($email) {
            //     $this->dispatch(new SendMailRegister($email, [
            //         'name'        => $input['name'],
            //         'phone'       => $input['phone'],
            //         'email'       => $input['email'],
            //         'verify_code' => $verify_code,
            //     ]));
            // }
            DB::commit();
            return response()->json(['status' => Message::get("users.register-success", $user->phone)], 200);
        } catch (QueryException $ex) {
            $response = SERVICE_Error::handle($ex);
            return response()->json(['errors' => [[$response['message']]]], 401);
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            return response()->json(['errors' => [[$response['message']]]], 401);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyRegister(Request $request)
    {
        $input = $request->all();
        (new VerifyRegisterValidator())->validate($input);
        try {
            DB::beginTransaction();
            $now  = date('Y-m-d H:i:s', time());
            $user = User::where(['email' => $input['email'], 'verify_code' => $input['verify_code']])->first();
            if (!$user) {
                return $this->responseError(Message::get('V018'));
            }
            if ($user->is_active !== 0) {
                return $this->responseError(Message::get('V020'));
            }
            if ($user->expired_code < $now) {
                return $this->responseError(Message::get('V019'));
            }
            $user->is_active = 1;
            $user->save();
            DB::commit();
            return response()->json(['status' => Message::get("users.active-success", $user->email)], 200);
        } catch (\Exception $ex) {
            DB::rollBack();
            $response = SERVICE_Error::handle($ex);
            return response()->json(['errors' => [[$response['message']]]], 401);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            $token = $this->jwt->getToken();
            $userId = SERVICE::getCurrentUserId();
            if (empty($userId)) {
                return response()->json([
                    'message'     => Message::get('unauthorized'),
                    'status_code' => Response::HTTP_UNAUTHORIZED,
                ], Response::HTTP_UNAUTHORIZED);
            }
            UserSession::where('user_id', $userId)->where('deleted', '0')->update([
                'deleted'    => 1,
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_by' => $userId,
            ]);
            $this->jwt->invalidate($token);
        } catch (TokenInvalidException $exInvalid) {
            return response()->json([
                'message'     => 'A token is invalid',
                'status_code' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        } catch (TokenExpiredException $exExpire) {
            return response()->json([
                'message'     => 'A token is expired',
                'status_code' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        } catch (JWTException $jwtEx) {
            return response()->json([
                'message'     => Message::get('logout-success'),
                'status_code' => Response::HTTP_OK,
            ], Response::HTTP_OK);
        }
        return response()->json([
            'message'     => Message::get('logout-success'),
            'status_code' => Response::HTTP_OK,
        ], Response::HTTP_OK);
    }
}
