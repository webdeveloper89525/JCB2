<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Stripe;

class StripePaymentController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function stripe()
    {
        return view('stripe');
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function stripePost(Request $request)
    {
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        Stripe\Charge::create ([
                "amount" => 100 * 100,
                "currency" => "usd",
                "source" => $request->stripeToken,
                "description" => "Test payment from junkcarboys.com."
        ]);

        Session::flash('success', 'Payment successful!');

        return back();
    }

    public function createIntent(Request $request)
    {
        try {
            \Stripe\Stripe::setApiKey(env('Stripe_Api_SECRETKey'));
        } catch (\Exception $Exception) {
            return response()->json([
                'error' => 'Payments Configuration Error'
            ])->setStatusCode(500);
        }
        try {
            $Intent = Stripe\PaymentIntent::create([
                'amount' => $request->input('amount'),
                'currency' =>  $request->input('currency'),
                'metadata' => [
                    'UserId' =>  $request->input('user_id'),
                    'UserEmail' => $request->input('user_email')
                ]
            ]);
        } catch (\Exception $Exception) {
            return response()->json([
                'error' => $Exception->getMessage()
            ])->setStatusCode(500);
        }
        return response()->json([
            'Intent' => [
                'Secret' => $Intent->client_secret
            ]
        ])->setStatusCode(201);
    }
}
