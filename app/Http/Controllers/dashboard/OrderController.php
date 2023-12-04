<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Models\Order;
use App\Models\OrderProdect;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    function listing()  {
        try {
            $orders = Order::paginate();
            return response()->json(['data'=>$orders , "status"=>true]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function createOrder(CreateOrderRequest $request)  {
        try {
            #get validation data requests
            $validation_data = $request->validated();
            #set created_by 
            $validation_data['created_by'] = auth()->user()->id;

            // Start transaction so if there's any errors while
            // processing order we can rollback.
            DB::beginTransaction();

            #get qty
            $validation_data['qty'] = count($validation_data['products']);
            #create order
            $order = Order::create(Arr::except($validation_data,['products']));

            foreach ($validation_data['products'] as $key => $product) {
                #get data of product
                $product_data = Product::where('id',$product['product_id'])->first();
                #check status of product
                if ($product_data->status == 0) {
                    // rollback for previous insert queries
                    DB::rollback();
                }
                #calculate total_price
                if ($product_data->discount !== null ) {

                    if ($product_data->discount_type == 'amount') {
                        $price = $product_data->price - $product_data->discount;
                        $total_price = $price * $product['qty_product'];

                    } elseif ($product_data->discount_type  == 'percentage') {
                        $price = $product_data->price - (($product_data->price / 100) * $product_data->discount);
                        $total_price = $price * $product['qty_product'];
                    }
                    
                }
                $prodcuts_order = OrderProdect::create([
                    'order_id'=>$order->id,
                    'product_id'=>$product['product_id'],
                    'qty_product'=>$product['qty_product'],
                    'price'=>$price,
                    'total_price'=>$total_price,
                ]);
            }
            if ($prodcuts_order) {
                DB::commit();
            }
            return response()->json(['messsage'=>'done' ,'status'=>true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function viewOrder($id) {
        try {
            $order = Order::find($id);
            if (!$order) {
                return response()->json(['message'=>'not found', 'status'=>false]);
            }
            return response()->json([$order, 'status'=>true]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
