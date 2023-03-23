<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Appointment::latest()->paginate();
        return response([
            "data" => $data,
            "status" => 'ok',
            "success" => true,
            "groups" => true,
            "message" => "success",
        ], Response::HTTP_OK);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "notes" => ['string'],
        ]);

        if ($validator->fails()) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => $validator->errors()->all(),
                "data" => [],
            ], Response::HTTP_UNAUTHORIZED);

        }

        $data = Appointment::create(array_merge(
            $request->all(), [
                "patient_id" => Auth::user()->id,
                "notes" => $request->notes,
                'order_ref' => random_int(100000, 999999),
            ]
        ));

        return response([
            "status" => 'ok',
            "success" => true,
            "message" => "Appointment added succesfully",
            "data" => $data,
        ], Response::HTTP_CREATED);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $appointment)
    {
        $validator = Validator::make($request->all(), [
            "doctor_id" => ['uuid'],
            "duration_minutes" => ['numeric'],
            "appointment_date" => ['string'],
            "status" => ['string'],
        ]);

        if ($validator->fails()) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => $validator->errors()->all(),
                "data" => [],
            ], Response::HTTP_UNAUTHORIZED);
        }
        $data = Appointment::where('id', $appointment)->update($request->except('id'));

        return response([
            "data" => Appointment::find($appointment),
            "status" => 'ok',
            "success" => true,
            "message" => "Appointment Updated Succesfully",
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Appointment  $Appointment
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response([
            "data" => Appointment::find($id),
            "status" => 'ok',
            "success" => true,
            "message" => "success",
        ], Response::HTTP_OK);

    }

    /**
     *
     * @param  \App\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response([
            "data" => [],
            "status" => 'ok',
            "success" => true,
            "message" => "Appointment deleted successfully",
        ], Response::HTTP_OK);

    }
}
