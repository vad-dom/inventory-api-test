<?php

namespace App\Http\Requests\Stock;

use App\Http\Requests\FilterRequest;
use App\Models\Product;
use App\Models\Warehouse;
use App\Support\Pagination;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StockBalancesRequest extends FilterRequest
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
            'per_page' => [
                'sometimes',
                'integer',
                'min:'.Pagination::MIN_PER_PAGE,
                'max:'.Pagination::MAX_PER_PAGE,
            ],

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
                'required_with:direction',
                'string',
                Rule::in([
                    'quantity',
                    'created_at',
                    'updated_at',
                ]),
            ],

            'direction' => [
                'required_with:sort',
                'string',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],
        ];
    }

    protected function booleanFields(): array
    {
        return ['only_positive'];
    }
}
