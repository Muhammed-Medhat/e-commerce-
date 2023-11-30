<?php

namespace App\Http\Requests;

use App\Rules\isBase64Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateProductRequest extends FormRequest
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
            "name"=>"required|string|unique:products,name",
            "description"=>"required|string|unique:products,description",
            'status' => ['required', Rule::in([0, 1])],
            "meta_title"=>"required|string|unique:products,meta_title",
            "meta_description"=>"required|string|unique:products,meta_description",
            "weight"=>"required|numeric",
            "length"=>"required|numeric",
            "breadth"=>"required|numeric",
            "height"=>"required|numeric",
            "model_number"=>"required|string",
            "price"=>"numeric",
            "discount"=>"numeric",
            'discount_type' => [Rule::in(['amount', 'percentage'])],
            "video_link"=>"url",
            'type' => [Rule::in(['single', 'variant'])],
            "category_id" => "required|exists:categories,id",
            "brand_id" => "required|exists:brands,id",
            "images" => "array",
            "images.*" => [new isBase64Image()],
            "related_products" => "array",
            "related_products.*" => "required|min:1|exists:products,id",
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
