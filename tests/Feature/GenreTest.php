<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /** 画面表示: index/create/edit/show が表示できる */
    public function test_genre_screens_render(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '技術書']);
        $book = Book::factory()->create(['title' => 'ジャンル所属本']);
        $book->genres()->sync([$genre->id]);

        $this->actingAs($user)->get(route('genres.index'))->assertOk()->assertSee('技術書');
        $this->actingAs($user)->get(route('genres.create'))->assertOk();
        $this->actingAs($user)->get(route('genres.edit', $genre))->assertOk();
        $this->actingAs($user)->get(route('genres.show', $genre))->assertOk()->assertSee('ジャンル所属本');
    }

    /** ジャンル登録成功 */
    public function test_store_success(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/genres', ['name' => '新ジャンル'])
            ->assertRedirect(route('genres.index'))
            ->assertSessionHas('success', 'ジャンルを登録しました');
        $this->assertDatabaseHas('genres', ['name' => '新ジャンル']);
    }

    /** F-G1: name未入力 */
    public function test_store_requires_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/genres', [])
            ->assertSessionHasErrors('name');
    }

    /** F-G2: name重複 */
    public function test_store_unique_name(): void
    {
        $user = User::factory()->create();
        Genre::factory()->create(['name' => '技術書']);

        $this->actingAs($user)->post('/genres', ['name' => '技術書'])
            ->assertSessionHasErrors('name');
    }

    /** F-G3: 編集は作成者でなくても可（所有者概念なし） */
    public function test_edit_allowed_for_any_authenticated_user(): void
    {
        $creator = User::factory()->create();
        $other = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '旧名']);

        $this->actingAs($other)->get(route('genres.edit', $genre))->assertOk();
        $this->actingAs($other)->put(route('genres.update', $genre), ['name' => '新名'])
            ->assertRedirect(route('genres.index'))
            ->assertSessionHas('success', 'ジャンルを更新しました');
        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => '新名']);
    }

    /** F-G4: 削除制限（紐付きあり） */
    public function test_destroy_blocked_when_books_attached(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->sync([$genre->id]);

        $this->actingAs($user)->delete(route('genres.destroy', $genre))
            ->assertRedirect(route('genres.index'))
            ->assertSessionHas('error', 'このジャンルに紐づく書籍が存在するため削除できません');
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }

    /** F-G4補足: 削除済み書籍のみ紐付きでも削除不可（withTrashed判定） */
    public function test_destroy_blocked_when_only_trashed_book_attached(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->sync([$genre->id]);
        $book->delete();

        $this->actingAs($user)->delete(route('genres.destroy', $genre))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }

    /** F-G5: 削除成功（紐付きなし） */
    public function test_destroy_success_when_no_books(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user)->delete(route('genres.destroy', $genre))
            ->assertRedirect(route('genres.index'))
            ->assertSessionHas('success', 'ジャンルを削除しました');
        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    /** F-G6: 二重防御 — DB制約restrictOnDeleteが機能 */
    public function test_db_constraint_prevents_delete(): void
    {
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->sync([$genre->id]);

        $this->expectException(QueryException::class);
        // アプリ側チェックを迂回した直接削除
        $genre->delete();
    }

    /** B-2: 削除済み書籍のみ紐づくジャンルは一覧で0冊表示 */
    public function test_index_shows_zero_count_for_genre_with_only_trashed_books(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '削除済みのみジャンル']);
        $book = Book::factory()->create();
        $book->genres()->sync([$genre->id]);
        $book->delete();

        $this->actingAs($user)->get(route('genres.index'))
            ->assertOk()
            ->assertSee('削除済みのみジャンル')
            ->assertSee('0冊');
    }

    /** B-3: 詳細の所属書籍は10件ページネーション */
    public function test_show_paginates_books_at_10(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $books = Book::factory()->count(11)->create();
        foreach ($books as $book) {
            $book->genres()->sync([$genre->id]);
        }

        $response = $this->actingAs($user)->get(route('genres.show', $genre));
        $response->assertOk();
        $this->assertTrue($response->viewData('books')->hasMorePages());
    }
}
