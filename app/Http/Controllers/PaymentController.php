<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function index()
    {
        return response([
            "data" => Payment::with('user', 'appointment')->latest()->paginate(),
            "status" => 'ok',
            "success" => true,
            "message" => "success",
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "amount" => ['numeric', 'required'],
            "order_ref" => ['string', 'required', 'unique:payments'],
            "provider" => ['in:PAYSTACK,FLUTTERWAVE', 'required'],
        ]);

        if ($validator->fails()) {
            return response([
                "status" => 'failed',
                "success" => false,
                "message" => $validator->errors()->all(),
                "data" => [],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $paystack_url = config('app.paystack_url');
        $paystack_sk = config('app.paystack_sk');
        $flw_url = config('app.flw_url');
        $flw_sk = config('app.flw_sk');

        $pay_ref = 'HAAS-' . random_int(10000000, 99999999);

        if ($request->provider == 'PAYSTACK') {
            $response = Http::withToken($paystack_sk)->post($paystack_url, [
                'email' => Auth::user()->email,
                'amount' => $request->amount * 100,
                'reference' => $pay_ref,
            ]);

            if ($response->successful()) {

                $res = $response['data']['authorization_url'];

                Payment::create([
                    "user_id" => Auth::user()->id,
                    "provider" => 'PAYSTACK',
                    "reference" => $pay_ref,
                    "amount" => $request->amount,
                    "order_ref" => $request->order_ref,
                    "payment_link" => $res,
                ]);

            }
        }
        if ($request->provider == 'FLUTTERWAVE') {

            $response = Http::withToken($flw_sk)->post($flw_url, [
                "tx_ref" => $pay_ref,
                "amount" => $request->amount,
                "currency" => "NGN",
                "redirect_url" => $flw_url,
                "customer" => [
                    "email" => Auth::user()->email,
                    "name" => Auth::user()->first_name . ' ' . Auth::user()->last_name ?: "Haas User",
                ],
            ]);
            if ($response->successful()) {

                $res = $response['data']['link'];

                Payment::create([
                    "user_id" => Auth::user()->id,
                    "provider" => 'FLUTTERWAVE',
                    "reference" => $pay_ref,
                    "amount" => $request->amount,
                    "order_ref" => $request->order_ref,
                    "payment_link" => $res,
                ]);
            }

        }

        return response([
            "data" => $res,
            "reference" => $pay_ref,
            "status" => 'ok',
            "success" => true,
            "message" => "success",
        ], Response::HTTP_OK);

    }

    public function paystack(Request $request)
    {
        $ip = Log::info($request->ip());

        echo "client IP.." . $ip;

        $payload = $request->all();

        if ($payload['data']['status'] = 'success') {
            Payment::where(['reference' => $payload['data']['reference']])->update(["status" => "successful", "paid" => true, "webhook_response" => $payload]);
            Appointment::where(['reference' => $payload['txRef']])->update(["status" => "paid"]);

            $this->sendEmail($payload['data']['reference']);

            return response(200);

        }

    }

    public function flw(Request $request)
    {
        $secretHash = config('app.wh_sk');
        $signature = $request->header('verif-hash');
        if (!$signature || ($signature !== $secretHash)) {
            abort(401);
        }
        $payload = $request->all();

        Payment::where(['reference' => $payload['txRef']])->update(["status" => $payload['status'], "paid" => true, "webhook_response" => $payload]);
        Appointment::where(['reference' => $payload['txRef']])->update(["status" => "paid"]);

        $this->sendEmail($payload['txRef']);

        return response(200);
    }

    private function sendEmail($ref)
    {
        $userId = Payment::where('reference', $ref)->pluck('user_id');
        $user = User::find($userId);
        $info = [
            'first_name' => $user->first_name ?: 'User',
            'email' => $user->email,
        ];

        Mail::to($request->email)->send(new PaymentEmail($info));
        return;
    }

    public function fetchPayment($ref)
    {
        return response([
            "data" => Payment::where('reference', $ref)->with('user')->first(),
            "status" => 'ok',
            "success" => true,
            "message" => "success",
        ], Response::HTTP_OK);
    }

    public function history()
    {
        return response([
            "data" => Payment::where('user_id', Auth::user()->id)->latest()->paginate(),
            "status" => 'ok',
            "success" => true,
            "message" => "success",
        ], Response::HTTP_OK);
    }
}
