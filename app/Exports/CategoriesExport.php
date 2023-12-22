<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesExport implements FromCollection, WithHeadings, WithMapping, WithStrictNullComparison, WithTitle, ShouldAutoSize
{
    public $id_arr = [];
    
    public function __construct($ex_id_arr = null ) {
        $this->id_arr = $ex_id_arr;
    }

    public function title(): string
    {
        return 'Categories';
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        try {
            if ($this->id_arr !== null){

                foreach ($this->id_arr as $key => $value) {
                    $categories[]= Category::find($value);
                }
                return collect($categories);
            }else {
                return Category::get();
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        
    }

    public function map($category) : array {
        return [
            $category->id,
            $category->name,
            $category->slug,
            $category->parent_category,
            $category->status == 1 ? 'active' : 'unactive',
            $category->products->pluck('name')->implode(', '),
            $category->created_at->toDateString(),
        ];
    }
    public function headings() : array {
        return [
            'id',
            'name',
            'slug',
            'parent_category',
            'status',
            'products.name',
            'Date',
        ];
    }
}