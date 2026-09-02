<?php

namespace App\Http\Requests\Api\V1;

class StoreApiBookRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'regex:/^[0-9]{13}$/', 'unique:books,isbn'],
            'published_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
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
            'title.required' => 'タイトルを入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'author.required' => '著者名を入力してください',
            'author.max' => '著者名は255文字以内で入力してください',
            'isbn.required' => 'ISBNを入力してください',
            'isbn.regex' => 'ISBNは13桁の数字で入力してください',
            'isbn.unique' => 'このISBNは既に登録されています',
            'published_date.required' => '出版日を入力してください',
            'published_date.date' => '出版日は正しい日付形式で入力してください',
            'description.max' => '説明は1000文字以内で入力してください',
            'image_url.url' => '画像URLの形式が正しくありません',
            'image_url.max' => '画像URLは255文字以内で入力してください',
            'genres.required' => 'ジャンルを1つ以上選択してください',
            'genres.min' => 'ジャンルを1つ以上選択してください',
            'genres.*.exists' => '選択されたジャンルが存在しません',
            'user_id.required' => '登録者IDを指定してください',
            'user_id.integer' => '登録者IDを指定してください',
            'user_id.exists' => '指定された登録者が存在しません',
        ];
    }
}
