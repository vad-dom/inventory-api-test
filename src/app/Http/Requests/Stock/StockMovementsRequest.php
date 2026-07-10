<?php

namespace App\Http\Requests\Stock;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Support\Pagination;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockMovementsRequest extends FormRequest
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

            'type' => [
                'sometimes',
                'string',
                Rule::in(StockMovement::TYPES),
            ],

            'created_from' => [
                'sometimes',
                'date',
                'before_or_equal:created_to',
            ],

            'created_to' => [
                'sometimes',
                'date',
                'after_or_equal:created_from',
            ],

            'sort' => [
                'sometimes',
                'string',
                Rule::in([
                    'created_at',
                    'quantity',
                    'type',
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
