<?php

namespace App\Http\Requests\Stock;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WriteOffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists(Product::class, 'id'),
            ],

            'warehouse_id' => [
                'required',
                'integer',
                Rule::exists(Warehouse::class, 'id'),
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'comment' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
