<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * 走行⑮：Web画面エラーの日本語化とAPI側のJSON非破壊（検品表 L）。
 */
class WebErrorPageTest extends TestCase
{
    use RefreshDatabase;

    /** L-1: 存在しないWeb URLは日本語の404ページを返す */
    public function test_web_404_returns_japanese_page(): void
    {
        $response = $this->get('/this-route-does-not-exist');

        $response->assertStatus(404)
            ->assertSee('ページが見つかりません')
            ->assertSee('お探しのページは存在しないか、移動または削除された可能性があります。');
    }

    /** L-1: 権限のないWeb操作は日本語の403ページを返す */
    public function test_web_403_returns_japanese_page(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->get(route('books.edit', $book));

        $response->assertStatus(403)
            ->assertSee('このページにアクセスする権限がありません');
    }

    /** L-1: TokenMismatch（419）は日本語のセッション切れページを返す */
    public function test_web_419_returns_japanese_page(): void
    {
        Route::get('/__test_token_mismatch', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        });

        $response = $this->get('/__test_token_mismatch');

        $response->assertStatus(419)
            ->assertSee('セッションの有効期限が切れました');
    }

    /** L-2: API向けの404はJSON応答（非破壊）のまま */
    public function test_api_404_returns_json(): void
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404)
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson(['message' => '指定された書籍が見つかりません。']);
    }

    /** L-2: API向けの401はJSON応答（非破壊）のまま */
    public function test_api_401_returns_json(): void
    {
        $response = $this->postJson('/api/v1/books', []);

        $response->assertStatus(401)
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson(['message' => '認証が必要です。']);
    }

    /** L-2: API向けの403はJSON応答（非破壊）のまま */
    public function test_api_403_returns_json(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $book->genres()->sync([Genre::factory()->create()->id]);
        Sanctum::actingAs($other);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => 'x',
            'author' => 'y',
            'isbn' => '9784999999999',
            'published_date' => '2020-01-01',
            'genres' => [Genre::factory()->create()->id],
        ]);

        $response->assertStatus(403)
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson(['message' => 'この操作を実行する権限がありません。']);
    }
}
