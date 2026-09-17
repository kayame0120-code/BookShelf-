<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * 走行⑧：ISBNからGoogle Books APIを検索して自動入力する機能（検品表 E）。
 * 実際のGoogle Books APIは呼ばず Http::fake() でモックする。
 */
class IsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    /** E-1 / E-2: ISBN13桁ちょうどの正常系。Http::fake()で外部APIをモックする。 */
    public function test_isbn_13_digits_success_with_http_fake(): void
    {
        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'totalItems' => 1,
                'items' => [[
                    'volumeInfo' => [
                        'title' => 'モック書籍',
                        'authors' => ['著者A', '著者B'],
                        'description' => 'モックの説明',
                        'imageLinks' => ['thumbnail' => 'https://example.com/thumb.jpg'],
                        'publishedDate' => '2020-01-01',
                    ],
                ]],
            ], 200),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/books/isbn/9784000000001')
            ->assertOk()
            ->assertJson([
                'title' => 'モック書籍',
                'author' => '著者A、著者B',
                'description' => 'モックの説明',
                'image_url' => 'https://example.com/thumb.jpg',
                'published_date' => '2020-01-01',
            ]);

        // 実APIではなくモックが呼ばれたこと（googleapis宛のリクエストが発生したこと）。
        Http::assertSent(fn ($request) => str_contains($request->url(), 'googleapis.com'));
    }

    /** E-2: 12桁は422（境界の下側・非該当） */
    public function test_isbn_12_digits_returns_422(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/books/isbn/978400000000')
            ->assertStatus(422)
            ->assertJson(['message' => 'ISBNは13桁の数字で入力してください']);
    }

    /** E-2: 14桁は422（境界の上側・非該当） */
    public function test_isbn_14_digits_returns_422(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/books/isbn/97840000000012')
            ->assertStatus(422)
            ->assertJson(['message' => 'ISBNは13桁の数字で入力してください']);
    }

    /** E-2: 非数字は422 */
    public function test_isbn_non_numeric_returns_422(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/books/isbn/978abcd000001')
            ->assertStatus(422)
            ->assertJson(['message' => 'ISBNは13桁の数字で入力してください']);
    }

    /** E-3: 該当なし（totalItems=0）は404「該当する書籍が見つかりませんでした」 */
    public function test_isbn_not_found_returns_404(): void
    {
        Http::fake([
            'www.googleapis.com/*' => Http::response(['totalItems' => 0], 200),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/books/isbn/9784000000001')
            ->assertStatus(404)
            ->assertJson(['message' => '該当する書籍が見つかりませんでした']);
    }

    /** E-3: 外部APIエラー応答（500）は502「書籍情報の取得に失敗しました」 */
    public function test_isbn_upstream_failure_returns_502(): void
    {
        Http::fake([
            'www.googleapis.com/*' => Http::response('', 500),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/books/isbn/9784000000001')
            ->assertStatus(502)
            ->assertJson(['message' => '書籍情報の取得に失敗しました']);
    }

    /** E-3: 接続例外（ConnectionException）も502「書籍情報の取得に失敗しました」 */
    public function test_isbn_connection_exception_returns_502(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection timed out');
        });

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/books/isbn/9784000000001')
            ->assertStatus(502)
            ->assertJson(['message' => '書籍情報の取得に失敗しました']);
    }
}
