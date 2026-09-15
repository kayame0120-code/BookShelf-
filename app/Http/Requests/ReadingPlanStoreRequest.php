<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ReadingPlanStoreRequest extends FormRequest
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
            'book_id' => ['required', 'exists:books,id'],
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
            'book_id.required' => '書籍を選択してください',
            'book_id.exists' => '選択された書籍が存在しません',
            'target_date.required' => '期日を入力してください',
            'target_date.after_or_equal' => '期日は本日以降の日付で指定してください',
        ];
    }

    /**
     * 進行中の同一書籍の計画が既に存在する場合は重複エラーを追加する。
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $duplicate = ReadingPlan::where('user_id', Auth::id())
                ->where('book_id', $this->input('book_id'))
                ->where('status', ReadingPlanStatus::InProgress)
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('book_id', 'この書籍は既に読書計画に登録されています');
            }
        });
    }
}
