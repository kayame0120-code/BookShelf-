<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScreenAccessTest extends TestCase
{
    use RefreshDatabase;

    /** F-A1: guest専用画面はログイン済みだとbooks.indexへ */
    public function test_authenticated_user_redirected_from_guest_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/register')->assertRedirect('/books');
        $this->actingAs($user)->get('/login')->assertRedirect('/books');
    }

    /** F-A2: 認証必須画面は未ログインで/loginへ */
    public function test_guest_redirected_from_auth_pages(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();

        $this->get('/books/create')->assertRedirect('/login');
        $this->get("/books/{$book->id}/edit")->assertRedirect('/login');
        $this->get('/genres/create')->assertRedirect('/login');
        $this->get("/genres/{$genre->id}/edit")->assertRedirect('/login');
        $this->get('/favorites')->assertRedirect('/login');
    }

    /** F-A3: 公開画面は未ログインでも閲覧可 */
    public function test_public_pages_accessible_by_guest(): void
    {
        $book = Book::factory()->create();

        $this->get('/books')->assertOk();
        $this->get("/books/{$book->id}")->assertOk();
        $this->get('/ranking')->assertOk();
        $this->getJson('/api/v1/books')->assertOk();
    }

    /** F-A4: intended URL — ログイン後に元画面へ戻る */
    public function test_intended_url_after_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $this->get('/books/create')->assertRedirect('/login');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/books/create');
    }

    /** B-2: 未ログインで書籍の書き込み系操作は/loginへリダイレクト */
    public function test_guest_cannot_perform_book_write_actions(): void
    {
        $book = Book::factory()->create();

        $this->post(route('books.store'), [])->assertRedirect('/login');
        $this->put(route('books.update', $book), [])->assertRedirect('/login');
        $this->delete(route('books.destroy', $book))->assertRedirect('/login');
        $this->patch(route('books.restore', $book))->assertRedirect('/login');
    }
}
