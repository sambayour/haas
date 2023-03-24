<?php

namespace App\Http\Controllers;

use App\Http\Resources\UsersResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{

    public function upgrade(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => ['uuid', 'required'],
            "role" => ['in:ADMIN,DOCTOR', 'required'],
        ]);

        if ($validator->fails()) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => $validator->errors()->all(),
                "data" => [],
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($request->role === 'ADMIN') {
            $data = User::find($request->user_id)
                ->fill(["level" => 99])
                ->save();
        } elseif ($request->role === 'DOCTOR') {
            $data = User::find($request->user_id)
                ->fill(["level" => 59])
                ->save();
        }

        return response([
            "message" => 'Additional privilege granted',
            "status" => 'ok',
            "success" => true,
            "data" => new UsersResource(User::find($request->user_id)),
        ], Response::HTTP_OK);
    }

    public function revoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => ['string', 'required'],
        ]);

        if ($validator->fails()) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => $validator->errors()->all(),
                "data" => [],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = User::find($request->user_id)
            ->fill(["level" => 0])
            ->save();

        return response([
            "message" => 'all privilege revoked',
            "status" => 'ok',
            "success" => true,
            "data" => new UsersResource(User::find($request->user_id)),
        ], Response::HTTP_OK);
    }

    public function super_users()
    {
        return response([
            "message" => 'success',
            "status" => 'ok',
            "success" => true,
            "data" => User::where('level', 99)->latest()->paginate(),
        ], Response::HTTP_OK);
    }

    public function doctors()
    {
        return response([
            "message" => 'success',
            "status" => 'ok',
            "success" => true,
            "data" => User::where('level', 59)->latest()->paginate(),
        ], Response::HTTP_OK);
    }

    public function patients()
    {
        return response([
            "message" => 'success',
            "status" => 'ok',
            "success" => true,
            "data" => User::where('level', 0)->latest()->paginate(),
        ], Response::HTTP_OK);
    }
}
