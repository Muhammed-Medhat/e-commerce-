<?php

namespace App\Http\Requests;

use App\Rules\isBase64Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "name" => ['string', Rule::unique('products', 'name')->ignore($this->route('id'))],
            "description"=>['string', Rule::unique('products', 'description')->ignore($this->route('id'))],
            'status' => [ Rule::in([0, 1])],
            "meta_title"=>['string', Rule::unique('products', 'meta_title')->ignore($this->route('id'))],
            "meta_description"=>['string', Rule::unique('products', 'meta_description')->ignore($this->route('id'))],
            "weight"=>"numaric",
            "length"=>"numaric",
            "breadth"=>"numaric",
            "height"=>"numaric",
            "height"=>"numaric",
            "model_number"=>"string",
            "price"=>"numaric",
            "discount"=>"numaric",
            'discount_type' => [Rule::in(['amount', 'percentage'])],
            "video_link"=>"url",
            'type' => [Rule::in(['single', 'variant'])],
            "category_id" => "exists:categories,id",
            "brand_id" => "exists:brands,id",
            "images" => "nullable|array",
            "images.*" => [new isBase64Image()],
            "related_product" => "array",
            "related_product.*" => "min:1",
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }
}
