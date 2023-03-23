<?php

namespace App\Http\Controllers;

use App\Http\Resources\UsersResource;
use App\Mail\ResetPasswordOtp;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "first_name" => ['string'],
            "last_name" => ['string'],
            "password" => ['string', 'required', 'confirmed', 'min:6'],
            "email" => ['required', 'email:dns,rfc', 'unique:users'],
            "phone" => ['required', 'string', 'unique:users'],
        ]);

        if ($validator->fails()) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => $validator->errors()->all(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $info = [
            'first_name' => $request->first_name ?: 'User',
            'email' => $request->email,
            'url' => 'http://haas.test',
        ];

        Mail::to($request->email)->send(new WelcomeEmail($info));

        $user = User::create($request->all());

        return response([
            "token" => $user->createToken($request->email)->plainTextToken,
            "data" => new UsersResource(User::find($user->id)),
            "status" => 'ok',
            "success" => true,
            "message" => "Registration Successful",
        ], Response::HTTP_OK);

    }

    /**
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required_without:phone', 'email'],
            'phone' => ['string'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => $validator->errors()->all(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $field = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = request([$field, 'password']);

        if (!Auth::attempt($credentials)) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => "Unauthorized",
            ], 400);
        }

        $user = auth()->user();

        return response([
            "token" => $user->createToken($field)->plainTextToken,
            "data" => new UsersResource(User::find($user->id)),
            "status" => 'ok',
            "success" => true,
            "message" => "Logged in Successfully",
        ], Response::HTTP_OK);
    }

    public function passwordReset(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "email" => ['required', 'email:dns,rfc'],
        ]);
        if ($validator->fails()) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => $validator->errors()->all(),
                "data" => [],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validData = User::where('email', $request->email)->first();

        if ($validData == null) {
            return response([
                'status' => 'false',
                'success' => false,
                "message" => "Email is not valid",
                'data' => $validData->email,
            ], Response::HTTP_UNAUTHORIZED);
        }

        DB::table('password_resets')->insert([
            'email' => $validData->email,
            'token' => random_int(100000, 999999),
            'created_at' => Carbon::now(),
        ]);

        $tokenData = DB::table('password_resets')->where('email', $validData->email)->first();

        $reset = $tokenData->token;

        //mailable data
        $info = [
            'first_name' => $validData->first_name,
            'token' => $reset,
        ];

        Mail::to($validData->email)->send(new ResetPasswordOtp($info));

        $response = array(
            'status' => "ok",
            'success' => true,
            'message' => 'Check your email for OTP to reset your password',
            'email' => $validData->email,
        );
        return response($response, Response::HTTP_OK);
    }

    public function token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => ['required', 'numeric', 'min:6'],
        ]);
        if ($validator->fails()) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => $validator->errors()->all(),
                "data" => [],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = $request->token;
        $tokenData = DB::table('password_resets')->where('token', $token)->first();

        if ($tokenData == null) {
            return response([
                'message' => "Request Token has expired or doesn't exist",
                'success' => false,
                'status' => 'ok',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $response = array('message' => 'Token Verified successfully', 'success' => true);
        return response($response, Response::HTTP_OK);
    }

    public function resetNow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => ['numeric', 'required', 'min:6'],
            "password" => ['string', 'required', 'confirmed', 'min:6'],
        ]);
        if ($validator->fails()) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => $validator->errors()->all(),
                "data" => [],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = $request->token;
        $tokenData = DB::table('password_resets')->where('token', $token)->first();

        if ($tokenData == null) {
            return response([
                'message' => "Account not found",
                'success' => false,
                'status' => 'ok',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = User::where('email', $tokenData->email)->first();

        if ($user == null) {
            return response([
                'message' => 'Something is wrong with the user account ' . $user,
                'success' => 'false',
            ], Response::HTTP_UNAUTHORIZED
            );
        }

        $pwd = bcrypt($request->password);
        User::where('email', $user->email)->update(['password' => $pwd]);
        DB::table('password_resets')->where('email', $user->email)->delete();

        $response = array('message' => 'Password changed successfully', 'success' => true);
        return response($response, Response::HTTP_OK);
    }

    public function password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['string', 'required'],
            'new_password' => ['string', 'required'],
        ]);

        if ($validator->fails()) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => $validator->errors()->all(),
            ], 400);
        }
        if (Auth::Check()) {
            if (\Hash::check($request->current_password, Auth::User()->password)) {
                $user = User::find(Auth::user()->id)->update(["password" => $request->new_password]);

                return response([
                    "message" => "Password has been changed successfully",
                    "status" => 'ok',
                    "success" => true,
                    "data" => User::find(Auth::user()->id),
                ], Response::HTTP_OK);
            } else {
                return response([
                    "status" => 'ok',
                    "success" => false,
                    "message" => "Your current password is not valid",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        return response([
            "status" => 'ok',
            "success" => false,
            "message" => "We cannot verify the cause of this problem :(, please retry",
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function logout()
    {
        return response([
            "data" => auth()->user()->tokens()->delete(),
            "status" => 'ok',
            "success" => true,
            "message" => "Access Token revoked",
        ], Response::HTTP_OK);
    }

}
