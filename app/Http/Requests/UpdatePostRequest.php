<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Updating posts is public for now; the app has no authentication yet.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Only the fields sent are validated and updated, so PATCH-style partial
     * updates work, but a field that is sent cannot be blanked out.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string', 'max:10000'],
            'author' => ['sometimes', 'nullable', 'string', 'max:100'],
            'published' => ['sometimes', 'boolean'],
        ];
    }
}
