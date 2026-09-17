<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 走行⑦〜⑧：書籍CRUDの応用（isbn/published_dateのnullable化・認可詳細）。検品表 H。
 * Web側の書籍登録・更新では isbn・published_date は任意（nullable）。
 */
class BookCrudAdvancedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 有効な書籍登録ペイロードを返す。
     *
     * @return array<string, mixed>
     */
    private function validPayload(array $genreIds, array $overrides = []): array
    {
        return array_merge([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2020-01-01',
            'description' => '説明文',
            'image_url' => 'https://example.com/a.jpg',
            'genres' => $genreIds,
        ], $overrides);
    }

    /** H-1: isbn・published_dateを空のまま登録が成功する（nullable） */
    public function test_store_succeeds_with_empty_isbn_and_published_date(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validPayload([$genre->id], [
            'isbn' => '',
            'published_date' => '',
        ]));

        $book = Book::first();
        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'テスト書籍',
            'isbn' => null,
            'published_date' => null,
        ]);
    }

    /** H-1: isbn・published_dateを空のまま更新が成功する（nullable） */
    public function test_update_succeeds_with_empty_isbn_and_published_date(): void
    {
        $owner = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id, 'isbn' => '9784000000055']);
        $book->genres()->sync([$genre->id]);

        $response = $this->actingAs($owner)->put(route('books.update', $book), $this->validPayload([$genre->id], [
            'isbn' => '',
            'published_date' => '',
            'title' => '更新後タイトル',
        ]));

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
            'isbn' => null,
            'published_date' => null,
        ]);
    }

    /** H-2: isbnに値がある場合のみ桁数チェックが働く（13桁でない→エラー） */
    public function test_isbn_format_validated_only_when_present(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user)->post('/books', $this->validPayload([$genre->id], ['isbn' => '123']))
            ->assertSessionHasErrors(['isbn' => 'ISBNは13桁の数字で入力してください（入力がある場合のみ）']);
    }

    /** H-2: isbnに値がある場合のみ一意性チェックが働く（重複→エラー） */
    public function test_isbn_uniqueness_validated_only_when_present(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Book::factory()->create(['isbn' => '9784000000009']);

        $this->actingAs($user)->post('/books', $this->validPayload([$genre->id], ['isbn' => '9784000000009']))
            ->assertSessionHasErrors(['isbn' => 'このISBNは既に登録されています']);
    }

    /** H-2: published_dateに値がある場合のみ日付形式チェックが働く（不正→エラー） */
    public function test_published_date_format_validated_only_when_present(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user)->post('/books', $this->validPayload([$genre->id], ['published_date' => 'not-a-date']))
            ->assertSessionHasErrors(['published_date' => '出版日は正しい日付形式で入力してください（入力がある場合のみ）']);
    }

    /** H-3: 空POSTでも isbn/published_date には必須エラーが立たない（title/author/genresのみ必須） */
    public function test_empty_post_does_not_require_isbn_or_published_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', []);

        $response->assertSessionHasErrors(['title', 'author', 'genres']);
        $response->assertSessionDoesntHaveErrors(['isbn', 'published_date']);
    }

    /** H-4: 他人の書籍の編集画面表示（GET edit）は403 */
    public function test_other_user_cannot_view_edit(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->get(route('books.edit', $book))->assertForbidden();
    }

    /** H-4: 他人の書籍の更新（PUT）は403・レコード不変 */
    public function test_other_user_cannot_update(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id, 'title' => '元タイトル']);
        $book->genres()->sync([$genre->id]);

        $this->actingAs($other)->put(route('books.update', $book), $this->validPayload([$genre->id], ['title' => '乗っ取り']))
            ->assertForbidden();
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => '元タイトル']);
    }

    /** H-4: 他人の書籍の削除（DELETE）は403・削除されない */
    public function test_other_user_cannot_delete(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->delete(route('books.destroy', $book))->assertForbidden();
        $this->assertNull($book->fresh()->deleted_at);
    }
}
