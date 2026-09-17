<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * 走行⑩：公開API（Sanctumトークン認証）。検品表 G ＋ 発注書§7の書き直し対象。
 * 読み取り系（GET 2本）は認証不要。書き込み系（POST/PUT/DELETE 3本）は auth:sanctum 必須。
 */
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

    /**
     * 有効な書き込みペイロード（API側はisbn/published_date必須のまま）。
     *
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'API書籍',
            'author' => 'API著者',
            'isbn' => '9784111111119',
            'published_date' => '2021-05-05',
            'description' => 'desc',
            'image_url' => 'https://example.com/x.jpg',
            'genres' => [Genre::factory()->create()->id],
        ], $overrides);
    }

    // ================= G-4: 読み取り系（GET 2本）は認証なしで200 =================

    /** G-4: GET一覧は未認証で200 */
    public function test_index_is_public_returns_200(): void
    {
        Book::factory()->count(3)->create()->each(
            fn ($b) => $b->genres()->sync([Genre::factory()->create()->id])
        );

        $this->getJson('/api/v1/books')
            ->assertOk()
            ->assertJsonPath('meta.total', 3);
    }

    /** G-4: GET詳細は未認証で200 */
    public function test_show_is_public_returns_200(): void
    {
        $book = $this->makeBook();
        Review::factory()->create(['book_id' => $book->id, 'rating' => 4]);

        $this->getJson("/api/v1/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $book->id);
    }

    /** 一覧：keyword絞り込みで該当書籍のみ返る（BookController::index の keyword 分岐） */
    public function test_index_keyword_filter(): void
    {
        $hit = Book::factory()->create(['title' => 'APIキーワードヒット本', 'author' => '著者X']);
        $hit->genres()->sync([Genre::factory()->create()->id]);
        $miss = Book::factory()->create(['title' => '無関係の本', 'author' => '著者Y']);
        $miss->genres()->sync([Genre::factory()->create()->id]);

        $this->getJson('/api/v1/books?keyword=キーワードヒット')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['id' => $hit->id])
            ->assertJsonMissing(['id' => $miss->id]);
    }

    /** 一覧：genre_id絞り込みで該当書籍のみ返る（BookController::index の genre_id 分岐） */
    public function test_index_genre_filter(): void
    {
        $genre = Genre::factory()->create();
        $otherGenre = Genre::factory()->create();

        $inGenre = Book::factory()->create(['title' => '対象ジャンルの本']);
        $inGenre->genres()->sync([$genre->id]);
        $outGenre = Book::factory()->create(['title' => '別ジャンルの本']);
        $outGenre->genres()->sync([$otherGenre->id]);

        $this->getJson('/api/v1/books?genre_id='.$genre->id)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['id' => $inGenre->id])
            ->assertJsonMissing(['id' => $outGenre->id]);
    }

    // ================= G-1: 書き込み系の正常系（Sanctum認証） =================

    /** G-1: POST（登録）はSanctum認証で201・レコード作成 */
    public function test_store_success_with_sanctum(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/books', $this->validPayload(['isbn' => '9784111111119']))
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'API書籍');

        $this->assertDatabaseHas('books', ['isbn' => '9784111111119', 'user_id' => $user->id]);
    }

    /** G-1: PUT（更新）はSanctum認証（所有者）で200・レコード更新 */
    public function test_update_success_with_sanctum(): void
    {
        $user = User::factory()->create();
        $book = $this->makeBook(['isbn' => '9784222222227', 'user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->putJson("/api/v1/books/{$book->id}", $this->validPayload([
            'title' => '更新後タイトル',
            'isbn' => '9784222222227',
        ]))
            ->assertOk()
            ->assertJsonPath('data.title', '更新後タイトル');

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => '更新後タイトル']);
    }

    /** G-1: DELETE（削除）はSanctum認証（所有者）で204・論理削除 */
    public function test_destroy_success_with_sanctum(): void
    {
        $user = User::factory()->create();
        $book = $this->makeBook(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/books/{$book->id}")->assertStatus(204);
        $this->assertSoftDeleted('books', ['id' => $book->id]);
    }

    // ================= G-2: 未認証の書き込みは401（メソッドごと個別） =================

    /** G-2: 未認証POSTは401「認証が必要です。」 */
    public function test_store_unauthenticated_returns_401(): void
    {
        $this->postJson('/api/v1/books', $this->validPayload())
            ->assertStatus(401)
            ->assertJson(['message' => '認証が必要です。']);
    }

    /** G-2: 未認証PUTは401「認証が必要です。」 */
    public function test_update_unauthenticated_returns_401(): void
    {
        $book = $this->makeBook();

        $this->putJson("/api/v1/books/{$book->id}", $this->validPayload())
            ->assertStatus(401)
            ->assertJson(['message' => '認証が必要です。']);
    }

    /** G-2: 未認証DELETEは401「認証が必要です。」 */
    public function test_destroy_unauthenticated_returns_401(): void
    {
        $book = $this->makeBook();

        $this->deleteJson("/api/v1/books/{$book->id}")
            ->assertStatus(401)
            ->assertJson(['message' => '認証が必要です。']);
    }

    // ================= G-3: 他人の書籍への書き込みは403（メソッドごと個別） =================

    /** G-3: 他人の書籍へのPUTは403「この操作を実行する権限がありません。」 */
    public function test_update_other_users_book_returns_403(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = $this->makeBook(['user_id' => $owner->id]);
        Sanctum::actingAs($other);

        $this->putJson("/api/v1/books/{$book->id}", $this->validPayload())
            ->assertStatus(403)
            ->assertJson(['message' => 'この操作を実行する権限がありません。']);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'user_id' => $owner->id]);
    }

    /** G-3: 他人の書籍へのDELETEは403「この操作を実行する権限がありません。」 */
    public function test_destroy_other_users_book_returns_403(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = $this->makeBook(['user_id' => $owner->id]);
        Sanctum::actingAs($other);

        $this->deleteJson("/api/v1/books/{$book->id}")
            ->assertStatus(403)
            ->assertJson(['message' => 'この操作を実行する権限がありません。']);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'deleted_at' => null]);
    }

    // ================= 応用の周辺確認（読み取り系の挙動） =================

    /** GET詳細：存在しないIDは404 JSON（非破壊） */
    public function test_show_not_found_returns_json_404(): void
    {
        $this->getJson('/api/v1/books/99999')
            ->assertStatus(404)
            ->assertJson(['message' => '指定された書籍が見つかりません。']);
    }

    /** GET詳細：論理削除済みIDは404（一覧・詳細のGETは標準除外挙動） */
    public function test_show_soft_deleted_returns_404(): void
    {
        $book = $this->makeBook();
        $book->delete();

        $this->getJson("/api/v1/books/{$book->id}")->assertStatus(404);
    }

    /** POST：認証済みでも入力不正は422 */
    public function test_store_validation_error_when_authenticated(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/books', [])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }
}
