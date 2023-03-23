<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsersResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index()
    {
        return response([
            "data" => UsersResource::collection(User::latest()->paginate())->response()->getData(true),
            "status" => 'ok',
            "success" => true,
            "message" => "success",
        ], Response::HTTP_OK);
    }

    public function show($id)
    {

        return response([
            "data" => new UsersResource(User::find($id)),
            "status" => 'ok',
            "success" => true,
            "message" => "success",
        ], Response::HTTP_OK);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $user)
    {
        $validator = Validator::make($request->all(), [
            "first_name" => ['string'],
            "last_name" => ['string'],
            "phone" => ['string'],
        ]);

        if ($validator->fails()) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => $validator->errors()->all(),
                "data" => [],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user = Auth::user()->id;
        $data = User::where('id', $user)->update($request->except('id'));
        return response([
            "data" => new UsersResource(User::find($user)),
            "status" => 'ok',
            "success" => true,
            "message" => "User updated successfully",
        ], Response::HTTP_CREATED);
    }

    /**
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response([
            "data" => [],
            "status" => 'ok',
            "success" => true,
            "message" => "User deleted successfully",
        ], Response::HTTP_OK);

    }
}
