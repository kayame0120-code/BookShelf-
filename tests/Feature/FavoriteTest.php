<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** F-F1: トグルON */
    public function test_toggle_on(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post(route('favorites.toggle', $book));
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'book_id' => $book->id]);
    }

    /** F-F2: トグルOFF */
    public function test_toggle_off(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->favoriteBooks()->attach($book->id);

        $this->actingAs($user)->post(route('favorites.toggle', $book));
        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'book_id' => $book->id]);
    }

    /** F-F3: flash非使用 */
    public function test_toggle_has_no_flash(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post(route('favorites.toggle', $book))
            ->assertSessionMissing('success');
    }

    /** F-F4: 未ログインで/favoritesは/loginへ */
    public function test_guest_redirected_from_favorites(): void
    {
        $this->get('/favorites')->assertRedirect('/login');
    }

    /** F-F5: 一覧は自分のお気に入りのみ */
    public function test_index_shows_only_own_favorites(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $mine = Book::factory()->create(['title' => 'マイお気に入り本']);
        $theirs = Book::factory()->create(['title' => '他人のお気に入り本']);

        $user->favoriteBooks()->attach($mine->id);
        $other->favoriteBooks()->attach($theirs->id);

        $this->actingAs($user)->get('/favorites')
            ->assertOk()
            ->assertSee('マイお気に入り本')
            ->assertDontSee('他人のお気に入り本');
    }
}
