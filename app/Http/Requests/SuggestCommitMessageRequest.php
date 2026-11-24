<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuggestCommitMessageRequest extends FormRequest
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
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'diff' => ['required', 'string', 'max:131072'],
            'files' => ['required', 'array', 'min:1', 'max:100'],
            'files.*' => ['required', 'string', 'max:255'],
            'language' => ['nullable', Rule::in(['en', 'id'])],
        ];
    }
}
