<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class FilterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('is_active')) {
            return;
        }

        $isActive = $this->input('is_active');

        $this->merge([
            'is_active' => match ($isActive) {
                'true' => true,
                'false' => false,
                default => $isActive,
            },
        ]);
    }
}
