<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithStrictNullComparison, WithTitle, ShouldAutoSize
{

    public $id_arr = [];
    public $status;

    public function __construct($ex_id_arr = null ,$status = null ) {
        $this->id_arr = $ex_id_arr;
        $this->status = $status;
    }

    public function title(): string
    {
        return 'Orders';
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        try {
                if ($this->id_arr !== null){
                        foreach ($this->id_arr as $key => $value) {
                            $orders[]= Order::find($value);
                        }
                        return collect($orders);
                }else {
                    if ($this->status == 'paid') {
                        return Order::where('status','paid')->get();
                    }elseif ($this->status == 'unpaid') {
                        return Order::where('status','unpaid')->get();
                    }else {
                        return Order::get();
                    }
                }
                
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function map($order) : array {
        $productNames = $order->products_order->map(function ($productOrder) {
            return $productOrder->product->name;
        })->implode(', ');

        return [
            $order->id,
            $order->name,
            $order->email,
            $order->address,
            $order->status,
            $order->qty,
            $order->total_price,
            $order->stripe_session_id,
            $order->user_created_by->name,
            $order->user->name,
            $productNames,
            $order->created_at->toDateString(),
        ];
    }

    public function headings() : array {

        return [
            'order.id',
            'order.name',
            'order.email',
            'order.address',
            'order.status',
            'order.qty',
            'order.total_price',
            'order.stripe_session_id',
            'created_by.name',
            'user.name',
            'order.products',
            'Date',
        ] ;
    }
}
