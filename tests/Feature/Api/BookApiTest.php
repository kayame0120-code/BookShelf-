<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ジャンル付き書籍を作成するヘルパ。
     */
    private function makeBook(array $attrs = []): Book
    {
        $book = Book::factory()->create($attrs);
        $book->genres()->sync([Genre::factory()->create()->id]);

        return $book;
    }

    /** F-P1: AP01一覧・正常系（既定per_page=10） */
    public function test_index_success_default_pagination(): void
    {
        Book::factory()->count(15)->create()->each(
            fn ($b) => $b->genres()->sync([Genre::factory()->create()->id])
        );

        $response = $this->getJson('/api/v1/books');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 15);
    }

    /** F-P2: AP01一覧・異常系 */
    public function test_index_validation_error(): void
    {
        $this->getJson('/api/v1/books?per_page=101')->assertStatus(422);
        $this->getJson('/api/v1/books?page=0')->assertStatus(422);
        $this->getJson('/api/v1/books?genre_id=9999')->assertStatus(422);
    }

    /** F-P3: AP02詳細・正常系（commentがnullを含む） */
    public function test_show_success_with_null_comment(): void
    {
        $book = $this->makeBook();
        Review::factory()->create(['book_id' => $book->id, 'rating' => 4, 'comment' => null]);
        Review::factory()->create(['book_id' => $book->id, 'rating' => 5, 'comment' => 'あり']);

        $this->getJson("/api/v1/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $book->id)
            ->assertJsonPath('data.reviews_count', 2)
            ->assertJsonCount(2, 'data.reviews');
    }

    /** F-P4: AP02・存在しないID */
    public function test_show_not_found(): void
    {
        $this->getJson('/api/v1/books/99999')
            ->assertStatus(404)
            ->assertJson(['message' => '指定された書籍が見つかりません。']);
    }

    /** F-P5: AP02・削除済みID */
    public function test_show_soft_deleted_returns_404(): void
    {
        $book = $this->makeBook();
        $book->delete();

        $this->getJson("/api/v1/books/{$book->id}")->assertStatus(404);
    }

    /** F-P6: AP03新規登録 */
    public function test_store_success_and_validation(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $payload = [
            'title' => 'API書籍',
            'author' => 'API著者',
            'isbn' => '9784111111119',
            'published_date' => '2021-05-05',
            'description' => 'desc',
            'image_url' => 'https://example.com/x.jpg',
            'genres' => [$genre->id],
            'user_id' => $user->id,
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'API書籍')
            ->assertJsonMissingPath('data.reviews')
            ->assertJsonMissingPath('data.user_id');
        $this->assertDatabaseHas('books', ['isbn' => '9784111111119', 'user_id' => $user->id]);

        $this->postJson('/api/v1/books', [])->assertStatus(422);
    }

    /** F-P7: AP04更新・正常系（ISBN一意性は自身を除外） */
    public function test_update_success_ignores_own_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = $this->makeBook(['isbn' => '9784222222227', 'user_id' => $user->id]);

        $payload = [
            'title' => '更新後',
            'author' => '著者',
            'isbn' => '9784222222227', // 自身と同じISBN
            'published_date' => '2020-01-01',
            'genres' => [$genre->id],
            'user_id' => $user->id,
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertOk()
            ->assertJsonPath('data.title', '更新後');
    }

    /** F-P8: AP04異常系 */
    public function test_update_not_found_and_validation(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $this->putJson('/api/v1/books/99999', [
            'title' => 'x', 'author' => 'y', 'isbn' => '9784333333336',
            'published_date' => '2020-01-01', 'genres' => [$genre->id], 'user_id' => $user->id,
        ])->assertStatus(404);

        $book = $this->makeBook();
        $this->putJson("/api/v1/books/{$book->id}", [])->assertStatus(422);
    }

    /** F-P9: AP05削除（204・論理削除） */
    public function test_destroy_soft_deletes(): void
    {
        $book = $this->makeBook();

        $this->deleteJson("/api/v1/books/{$book->id}")->assertStatus(204);
        $this->assertSoftDeleted('books', ['id' => $book->id]);
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    /** F-P10: AP05存在しないID */
    public function test_destroy_not_found(): void
    {
        $this->deleteJson('/api/v1/books/99999')->assertStatus(404);
    }
}
