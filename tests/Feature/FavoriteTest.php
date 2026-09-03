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

    /** D-6: 一覧は登録した書籍の作成日時が新しい順 */
    public function test_index_ordered_by_created_at_desc(): void
    {
        $user = User::factory()->create();
        $older = Book::factory()->create(['title' => '古いお気に入り本', 'created_at' => now()->subDay()]);
        $newer = Book::factory()->create(['title' => '新しいお気に入り本', 'created_at' => now()]);

        $user->favoriteBooks()->attach([$older->id, $newer->id]);

        $this->actingAs($user)->get('/favorites')
            ->assertOk()
            ->assertSeeInOrder([$newer->title, $older->title]);
    }

    /** E-1 / E-3: 削除済み書籍は一覧から除外され、復元で再表示される */
    public function test_deleted_book_excluded_and_restored_book_reappears(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'お気に入り削除復元本']);
        $user->favoriteBooks()->attach($book->id);

        // 登録直後は表示される
        $this->actingAs($user)->get('/favorites')->assertSee($book->title);

        // E-1: 削除すると一覧から除外される
        $book->delete();
        $this->actingAs($user)->get('/favorites')->assertDontSee($book->title);

        // E-3: 復元すると一覧に再表示される
        $book->restore();
        $this->actingAs($user)->get('/favorites')->assertSee($book->title);
    }
}
