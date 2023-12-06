<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Laravel\Cashier\Cashier;
use Illuminate\Http\Request;

class StipeController extends Controller
{
    public $stripe;

    public function __construct() {$this->stripe = Cashier::stripe();}

    function pay($id) {
        try {
            #get order by ID
            $order = Order::find($id);
            #check if order not found in DB
            if (!$order) {
                return response()->json(['message'=>'something wrong','status'=>false],404);
            }
            #check if order status is paid
            if ($order->status == 'paid') {
                return response()->json(['message'=>'order is alraedy paided','status'=>false],200);
            }
            #get email of user
            $customer_email = $order['user']['email'];
                #get data to send it to stripe
                $line_items = [];
                foreach ($order['products_order'] as $key => $value) {
                    $line_items[] = [
                        'price_data' => [
                            "currency"=>"USD",
                            "unit_amount"=>$value['price'] * 100,
                            "product_data"=>[
                                "name"=>$value['product']['name'],
                                "description"=>$value['product']['description']
                            ]
                        ],
                        'quantity' =>$value['qty_product'],
                    ];
                }
                #open session 
                $checkout = $this->stripe->checkout->sessions->create([
                'success_url' => route("stripe.checkout.success") . '?session_id={CHECKOUT_SESSION_ID}', # should be real page frontend not like this 
                'customer_email'=>$customer_email,
                'line_items' => $line_items,
                'mode' => 'payment',
            ]);
            #set or update seesion_id of order
            $order->update(['stripe_session_id'=>$checkout->id]);
            
        return response()->json(["data" => $checkout["url"]]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function stripeCheckoutSuccess(Request $request )
    {
        try {
            // $session = $this->stripe->checkout->sessions->retrieve($request->session_id);

            // $paymentIntent = $this->stripe->paymentIntents->retrieve($session['payment_intent'], []);
            // return response()->json($session);
            $session = $this->stripe->checkout->sessions->retrieve($request->session_id);
            return response()->json($session);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function webhook() {
                #code from stripe.com
        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = env("STRIPE_WEBHOOK_SECRET");

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $endpoint_secret
        );
        } catch(\UnexpectedValueException $e) {
        // Invalid payload
        return response('',400);
        exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        return response('',400);
        exit();
        }

        // Handle the event
        switch ($event->type) {
        case 'checkout.session.completed':
            $session = $event->data->object;
            #get order by session ID
            $order =  Order::where('stripe_session_id',$session->id)->first();
            if ($order) {
                #update status order
                $order->update(['status'=>'paid']);
            }
        // ... handle other event types
        default:
            echo 'Received unknown event type ' . $event->type;
        }

        return response('');
            }
}
