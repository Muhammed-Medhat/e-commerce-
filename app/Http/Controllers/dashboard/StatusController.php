<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    function listing(Request $request) {
        #orders
         // get small order price and big
            $orders = [];
            
        //     $orderPriceSmall = Order::orderBy('total_price','asc')->first();
        //     $orders['orderPriceSmall'] = $orderPriceSmall;

        //     $orderPriceBig = Order::orderBy('total_price','desc')->first();
        //     if ($orders['orderPriceSmall']  !== $orders['orderPriceBig']) {
        //         $orders['orderPriceBig'] = null;
        //     }

        //  // get small order qty and big 
        //     $orderQtySmall = Order::orderBy('qty','asc')->first();
        //     $orders['orderQtySmall'] = $orderQtySmall;

        //     $orderQtyBig = Order::orderBy('qty','desc')->first();
        //     $orders['orderQtyBig'] = $orderQtyBig;

            $count_paid = Order::where('status','paid')->count();
            $orders['order_status_paid_count'] = $count_paid;

            // $order_paid_low_price = Order::orderBy('total_price','asc')->where('status','paid')->first();
            // $orders['order_paid_low_price'] = $order_paid_low_price;

            // $order_paid_hight_price = Order::orderBy('total_price','desc')->where('status','paid')->first();
            // $orders['order_paid_hight_price'] = $order_paid_hight_price;

            $count_unpaid = Order::where('status','unpaid')->count();
            $orders['order_status_unpaid_count'] = $count_unpaid;

            // $order_unpaid_low_price = Order::orderBy('total_price','asc')->where('status','unpaid')->first();
            // $orders['order_unpaid_low_price'] = $order_unpaid_low_price;

            // $order_unpaid_high_price = Order::orderBy('total_price','desc')->where('status','unpaid')->first();
            // $orders['order_unpaid_high_price'] = $order_unpaid_high_price;

            $orders['total_orders'] = Order::get()->count();
            
        # products
            $products = [];

            $products['total-prodcuts'] = Product::get()->count();
            $products['high_price'] = Product::orderBy('price','desc')->value('price');
            $products['low_price'] = Product::orderBy('price','asc')->value('price');
            // $product = Product::withCount(['products_order' => function ($query) {
            //     $query->selectRaw('sum(qty_product) as total_qty');
            // }])
            // ->orderByDesc('products_order_count')
            // ->first();
            $ordersP =Order::get();
            foreach ($ordersP as $order) {
                foreach ($order->products_order as $orderProduct) {
                    $products[] = [
                        'product' => $orderProduct->product->name,
                        'count' => $orderProduct->qty_product
                    ];
                }
            }


            return response()->json(['orders'=>$orders,'products'=>$products],200);
    }
}
