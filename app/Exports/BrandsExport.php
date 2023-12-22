<?php

namespace App\Exports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class BrandsExport implements FromCollection, WithHeadings, WithMapping, WithStrictNullComparison, WithTitle, ShouldAutoSize
{
    public $id_arr = [];
    
    public function __construct($ex_id_arr = null ) {
        $this->id_arr = $ex_id_arr;
    }

    public function title(): string
    {
        return 'Brands';
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        try {
            if ($this->id_arr !== null){

                foreach ($this->id_arr as $key => $value) {
                    $brands[]= Brand::find($value);
                }
                return collect($brands);
            }else {
                return Brand::get();
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function map($brand) : array {
        return [
            $brand->id,
            $brand->name,
            $brand->description,
            $brand->website_link,
            $brand->status == 1 ? 'active' : 'unactive',
            $brand->products->pluck('name')->implode(', '),
            $brand->created_at->toDateString(),
        ];
    }

    public function headings() : array {
        return [
            'id',
            'name_ar',
            'name_en',
            'description_ar',
            'description_en',
            'website_link',
            'status',
            'featured',
            'meta_title',
            'meta_description',
            'products.name',
            'Date',
        ];
    }
}