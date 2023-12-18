<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\OrderProdect;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class OrderController extends Controller
{
    function listing(Request $request)  {
        try {
                // Validation Rules
                $validator = Validator::make($request->all(), [
                    'q' => ['string'],
                    'sort_by' => [Rule::in(["qty-low-to-high", "qty-high-to-low","low-to-high", "high-to-low", "a-z", "z-a", "old", "new"])],
                    'filter_by_status' => [Rule::in(['paid','unpaid'])],
                    'filter_by_date_range' => ['json'],
                    'filter_by_total_amount_range' => ['json'],
                ]);

                // valid error message
                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validator->errors()
                    ], 422);
                }
                #Preparation query
                $orders = Order::query();
                            //////////////////////////// start filters //////////////////////////////////////////////

                /* Filter by data range */
                if(isset($request->filter_by_date_range)){
                    $filter_by_date_range = json_decode($request->filter_by_date_range);
                    $orders->whereBetween('created_at', [
                        Carbon::parse($filter_by_date_range[0])->format('Y-m-d\TH:i:s.u\Z'),
                        Carbon::parse($filter_by_date_range[1])->format('Y-m-d\TH:i:s.u\Z'),
                    ]);
                }
                /* Filter by search */
                if(isset($request->q)){
                    $query = $request->q;
                    $orders
                    ->where('email', 'like', "%{$query}%")
                    ->orWhereHas('user', function ($q) use ($query) {
                        $q->where('email', 'like', "%{$query}%")
                            ->orWhere('name', 'like', "%{$query}%");
                    });

                }
        
                /* Sort asc */
                if(isset($request->sort_by) && $request->sort_by == "a-z"){
                    $orders->orderBy("id","asc");
                }
        
                /* Sort desc */
                if(isset($request->sort_by) && $request->sort_by == "z-a"){
                    $orders->orderBy("id","desc");
                }
        
                /* Filter by date old */
                if(isset($request->sort_by) && $request->sort_by == "old"){
                    $orders->orderBy("created_at","asc");
                }
            
                /* Filter by date new */
                if(isset($request->sort_by) && $request->sort_by == "new"){
                    $orders->orderBy("created_at","desc");
                }

                /* Filter by status */
                if(isset($request->filter_by_status)){

                        if ($request->filter_by_status =='paid') {
                            $orders->where("status","paid");
                        } elseif ($request->filter_by_status =='unpaid') {
                            $orders->where("status","unpaid");
                        }
                }

                // Filter by price range
                if (isset($request->price_range)) {
                    $price_range = json_decode($request->price_range); // ex. [10, 20]
                    $orders->whereBetween('total_price', $price_range);
                }

                // Filter by price low-to-high
                if (isset($request->sort_by) && $request->sort_by == "low-to-high") {
                    $orders->orderBy('total_price','asc');
                }

                // Filter by price high-to-low
                if (isset($request->sort_by) && $request->sort_by == "high-to-low") {
                    $orders->orderBy('total_price','desc');
                }

                // Filter by qty range
                if (isset($request->qty)) {
                    $qty = json_decode($request->qty); // ex. [10, 20]
                    $orders->whereBetween('qty', $qty);
                }
                
                // Filter by qty low-to-high
                if (isset($request->sort_by) && $request->sort_by == "qty-low-to-high") {
                    $orders->orderBy('qty','asc');
                }

                // Filter by qty high-to-low
                if (isset($request->sort_by) && $request->sort_by == "qty-high-to-low") {
                    $orders->orderBy('qty','desc');
                }

            return response()->json(['data'=>$orders->paginate() , "status"=>true]);
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
            #total_price on order
            $total_price_order = 0;
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
                        $total_price_order += $total_price;

                    } elseif ($product_data->discount_type  == 'percentage') {
                        $price = $product_data->price - (($product_data->price / 100) * $product_data->discount);
                        $total_price = $price * $product['qty_product'];
                        $total_price_order += $total_price;
                    }
                    
                }else {
                    $price = $product_data->price;
                    $total_price = $price * $product['qty_product'];
                    $total_price_order += $total_price;
                }
                #set total_price on order 
                $order->update(['total_price'=>$total_price_order]);
                #create products of order
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
            return response()->json(['messsage'=>'order has been created' ,'status'=>true]);

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

    function updateOrder(UpdateOrderRequest $request ,$id) {
        try {
            $order = Order::where('id',$id)->where('status','unpaid')->first();
            if (!$order) {
                return response()->json(['message'=>'not found', 'status'=>false]);
            }

            #get validation data requests
            $validation_data = $request->validated();
            #set created_by 
            $validation_data['created_by'] = auth()->user()->id;

            // Start transaction so if there's any errors while
            // processing order we can rollback.
            DB::beginTransaction();

            #get qty
            $validation_data['qty'] = count($validation_data['products']);
            #update order
            $keysToCheck = ['name', 'email', 'address', 'user_id'];

            if (!empty(array_intersect($keysToCheck, array_keys($validation_data)))) {
                $order->update(Arr::except($validation_data, ['products']));
            }

            if (array_key_exists('products', $validation_data)) {
                #delete old products
                $order->products_order()->delete();
                #insert new products
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
                    #create products of order
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
            }
            return response()->json(['message'=>'order has been updated','status'=>true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    function deleteOrder($id)  {
        try {
            $order = Order::where('id',$id)->where('status','unpaid')->first();

            if (!$order) {
                return response()->json(['message'=>'not found', 'status'=>false]);
            }
            #delete order products
            $order->products_order()->delete();
            return response()->json(['message'=>'order has been deleted','status'=>true]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }
}
