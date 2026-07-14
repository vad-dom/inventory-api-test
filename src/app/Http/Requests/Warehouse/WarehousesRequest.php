<?php

namespace App\Http\Requests\Warehouse;

use App\Http\Requests\FilterRequest;
use App\Support\Pagination;
use Illuminate\Contracts\Validation\ValidationRule;

class WarehousesRequest extends FilterRequest
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
            'search' => ['sometimes', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function booleanFields(): array
    {
        return ['is_active'];
    }
}
