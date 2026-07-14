<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class FilterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach ($this->booleanFields() as $field) {
            $this->normalizeBoolean($field);
        }
    }

    /**
     * @return array<int, string>
     */
    abstract protected function booleanFields(): array;

    private function normalizeBoolean(string $field): void
    {
        if (! $this->has($field)) {
            return;
        }

        $value = $this->input($field);

        $this->merge([
            $field => match ($value) {
                'true' => true,
                'false' => false,
                default => $value,
            },
        ]);
    }
}
