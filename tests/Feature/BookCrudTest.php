<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCrudTest extends TestCase
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

    /** F-B1: 登録成功 */
    public function test_store_book_success(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validPayload([$genre->id]));

        $book = Book::first();
        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を登録しました');
        $this->assertDatabaseHas('books', ['title' => 'テスト書籍', 'user_id' => $user->id]);
    }

    /** F-B2: 登録バリデーション */
    public function test_store_validation_errors(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        // 必須未入力・genre未選択
        $this->actingAs($user)->post('/books', [])
            ->assertSessionHasErrors(['title', 'author', 'isbn', 'published_date', 'genres']);

        // isbn 13桁でない
        $this->actingAs($user)->post('/books', $this->validPayload([$genre->id], ['isbn' => '123']))
            ->assertSessionHasErrors('isbn');

        // isbn 重複
        Book::factory()->create(['isbn' => '9784000000009']);
        $this->actingAs($user)->post('/books', $this->validPayload([$genre->id], ['isbn' => '9784000000009']))
            ->assertSessionHasErrors('isbn');
    }

    /** F-B3: ジャンル紐付けsync（登録・編集） */
    public function test_genre_sync_on_store_and_update(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(3)->create();

        $this->actingAs($user)->post('/books', $this->validPayload([$genres[0]->id, $genres[1]->id]));
        $book = Book::first();
        $this->assertEqualsCanonicalizing(
            [$genres[0]->id, $genres[1]->id],
            $book->genres->pluck('id')->all()
        );

        // 編集で別ジャンルのみにsync
        $this->actingAs($user)->put(route('books.update', $book), $this->validPayload([$genres[2]->id]));
        $this->assertEqualsCanonicalizing([$genres[2]->id], $book->fresh()->genres->pluck('id')->all());
    }

    /** F-B4: 編集の認可 */
    public function test_edit_authorization(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)->get(route('books.edit', $book))->assertOk();
        $this->actingAs($other)->get(route('books.edit', $book))->assertForbidden();
    }

    /** F-B5: 削除の認可 */
    public function test_delete_authorization(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->delete(route('books.destroy', $book))->assertForbidden();
        $this->assertNull($book->fresh()->deleted_at);

        $this->actingAs($owner)->delete(route('books.destroy', $book))
            ->assertRedirect(route('books.index'));
        $this->assertNotNull($book->fresh()->deleted_at);
    }

    /** F-B6: 削除後の一覧・ランキング・API除外 */
    public function test_deleted_book_excluded_from_listings(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $book->genres()->sync([$genre->id]);
        Review::factory()->create(['book_id' => $book->id, 'rating' => 5]);

        $book->delete();

        $this->get('/books')->assertDontSee($book->title);
        $this->get('/ranking')->assertDontSee($book->title);
        $this->getJson('/api/v1/books')->assertJsonMissing(['id' => $book->id]);
    }

    /** F-B7: 削除済み書籍詳細は404にならずバナー表示 */
    public function test_deleted_book_show_page(): void
    {
        $book = Book::factory()->create();
        $book->delete();

        $this->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('この本は削除されました');
    }

    /** F-B8: 削除済み時のボタン・投稿フォーム非表示 */
    public function test_deleted_book_hides_buttons(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $book->delete();

        $response = $this->actingAs($owner)->get(route('books.show', $book));
        $response->assertDontSee('編集');
        $response->assertSee('削除済みの書籍にはレビューを投稿できません');
        $response->assertSee('復元する');
    }

    /** F-B9: 削除済み時もレビュー閲覧維持 */
    public function test_deleted_book_reviews_still_visible(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();
        $book = Book::factory()->create();
        Review::factory()->create(['book_id' => $book->id, 'user_id' => $author->id, 'comment' => '公開レビュー本文']);
        $book->delete();

        $this->actingAs($viewer)->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('公開レビュー本文');
    }

    /** F-B10: 復元 */
    public function test_restore_book(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        Review::factory()->create(['book_id' => $book->id, 'rating' => 5]);
        $book->delete();

        $this->actingAs($owner)->patch(route('books.restore', $book))
            ->assertRedirect(route('books.show', $book))
            ->assertSessionHas('success', '書籍を復元しました');

        $this->assertNull($book->fresh()->deleted_at);
        $this->get('/books')->assertSee($book->title);
        $this->get('/ranking')->assertSee($book->title);
    }

    /** F-B11: 復元の認可 */
    public function test_restore_authorization(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $book->delete();

        $this->actingAs($other)->patch(route('books.restore', $book))->assertForbidden();
        $this->assertNotNull($book->fresh()->deleted_at);
    }

    /** F-B12: 削除済みISBNの一意性 */
    public function test_deleted_isbn_uniqueness(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['isbn' => '9784000000123']);
        $book->delete();

        $this->actingAs($user)->post('/books', $this->validPayload([$genre->id], ['isbn' => '9784000000123']))
            ->assertSessionHasErrors('isbn');
    }

    /** F-B13: 削除後もreviewsが物理保持される */
    public function test_reviews_kept_after_book_soft_delete(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $book->delete();

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'book_id' => $book->id]);
    }

    /** D-3: 自分自身のISBNを維持したまま更新してもuniqueエラーにならない */
    public function test_update_ignores_own_isbn(): void
    {
        $owner = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id, 'isbn' => '9784000000055']);
        $book->genres()->sync([$genre->id]);

        $response = $this->actingAs($owner)->put(
            route('books.update', $book),
            $this->validPayload([$genre->id], ['isbn' => '9784000000055', 'title' => '更新後タイトル'])
        );

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
            'isbn' => '9784000000055',
        ]);
    }

    /** G-4: 未削除の書籍に対する復元は本人でも不可 */
    public function test_restore_undeleted_book_forbidden(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)->patch(route('books.restore', $book))->assertForbidden();
        $this->assertNull($book->fresh()->deleted_at);
    }
}
