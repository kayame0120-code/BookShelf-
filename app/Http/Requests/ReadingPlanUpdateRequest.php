<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReadingPlanUpdateRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'target_date.required' => '期日を入力してください',
            'target_date.after_or_equal' => '期日は本日以降の日付で指定してください',
        ];
    }
}
