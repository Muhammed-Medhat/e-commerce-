<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStrictNullComparison, WithTitle, ShouldAutoSize
{
    public function title(): string
    {
        return 'Products';
    }

    public function collection()
    {
        return Product::get();

    }

    public function map($product) : array {
        return [
            $product->id,
            $product->name,
            $product->type,
            $product->model_number,
            $product->slug,
            $product->weight,
            $product->length,
            $product->breadth,
            $product->height,
            $product->price,
            $product->discount,
            $product->discount_type,
            $product->description,
            $product->meta_title,
            $product->meta_description,
            $product->video_link,
            $product->status == 1 ? 'active' : 'unactive',
            $product->images->pluck('url')->implode(', '),
            $product->category->name,
            $product->brand->name,
            $product->created_at->toDateString(),
        ];
    }

    public function headings() : array {

        return [
            'id',
            'name',
            'type',
            'model_number',
            'slug',
            'weight',
            'length',
            'breadth',
            'height',
            'price',
            'discount',
            'discount_type',
            'description_ar',
            'meta_title',
            'meta_description',
            'video_link',
            'status',
            'images.url',
            'categories.name',
            'brands.name',
            'Date',
        ] ;
    }
}