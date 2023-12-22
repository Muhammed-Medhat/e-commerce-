<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStrictNullComparison, WithTitle, ShouldAutoSize
{
    public $id_arr = [];
    
    public function __construct($ex_id_arr = null ) {
        $this->id_arr = $ex_id_arr;
    }

    public function title(): string
    {
        return 'Customers';
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        try {
            if ($this->id_arr !== null){

                foreach ($this->id_arr as $key => $value) {
                    $customers[]= User::where('isAdmin',0)->find($value);
                }
                return collect($customers);
            }else {
                return User::where('isAdmin',0)->get();
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function map($customer) : array {
        return [
            $customer->id,
            $customer->name,
            $customer->email,
            $customer->gender,
            $customer->description,
            $customer->image,
            $customer->status == 1 ? 'active' : 'unactive',
            $customer->created_at->toDateString(),
        ];
    }

    public function headings() : array {
        return [
            'id',
            'name',
            'email',
            'gender',
            'description',
            'image',
            'status',
            'Date',
        ];
    }
}