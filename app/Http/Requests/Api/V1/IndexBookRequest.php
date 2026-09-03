<?php

namespace App\Http\Requests\Api\V1;

class IndexBookRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
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
            'keyword.max' => '検索キーワードは255文字以内で入力してください',
            'genre_id.integer' => '指定されたジャンルが存在しません',
            'genre_id.exists' => '指定されたジャンルが存在しません',
            'page.integer' => 'ページ番号は1以上の整数で指定してください',
            'page.min' => 'ページ番号は1以上の整数で指定してください',
            'per_page.integer' => '取得件数は1〜100の範囲で指定してください',
            'per_page.min' => '取得件数は1〜100の範囲で指定してください',
            'per_page.max' => '取得件数は1〜100の範囲で指定してください',
        ];
    }
}
