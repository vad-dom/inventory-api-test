<?php

namespace App\Http\Requests\Stock;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockBalancesRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],

            'product_id' => [
                'sometimes',
                'integer',
                Rule::exists(Product::class, 'id'),
            ],

            'warehouse_id' => [
                'sometimes',
                'integer',
                Rule::exists(Warehouse::class, 'id'),
            ],

            'only_positive' => ['sometimes', 'boolean'],

            'sort' => [
                'sometimes',
                'string',
                Rule::in([
                    'quantity',
                    'created_at',
                    'updated_at',
                ]),
            ],

            'direction' => [
                'sometimes',
                'string',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],
        ];
    }
}
