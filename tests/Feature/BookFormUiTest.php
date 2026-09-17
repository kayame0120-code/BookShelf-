<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 書籍一覧の検索フォームと、登録画面のISBN自動入力セクションが
 * 画面に描画されることを確認する（デザイン画像1・2準拠）。
 * コントローラ挙動ではなくUI要素の存在を検証する。
 */
class BookFormUiTest extends TestCase
{
    use RefreshDatabase;

    /** 一覧画面に検索フォーム（キーワード・ジャンル・並び順＋検索/リセット/登録）が描画される */
    public function test_index_renders_search_form(): void
    {
        $genre = Genre::factory()->create(['name' => 'テストジャンル']);

        $response = $this->get('/books');

        $response->assertOk();
        // 検索フォームと入力要素
        $response->assertSee('<form method="GET"', false);
        $response->assertSee('name="keyword"', false);
        $response->assertSee('name="genre"', false);
        $response->assertSee('name="sort"', false);
        // ラベルとボタン
        $response->assertSee('キーワード');
        $response->assertSee('並び順');
        $response->assertSee('検索');
        $response->assertSee('リセット');
        $response->assertSee('書籍を登録');
        // ジャンルselectに登録ジャンルが並ぶ／並び順の選択肢
        $response->assertSee('すべて');
        $response->assertSee('テストジャンル');
        $response->assertSeeInOrder(['新しい順', '古い順', 'タイトル順', '評価が高い順']);
    }

    /** 検索実行後、フォームに入力値が保持される */
    public function test_search_form_retains_input_values(): void
    {
        $genre = Genre::factory()->create(['name' => '保持ジャンル']);

        $response = $this->get('/books?keyword=あいう&genre='.$genre->id.'&sort=title');

        $response->assertOk();
        $response->assertSee('value="あいう"', false);
        // 選択中のジャンル・並び順にselectedが付く
        $response->assertSee('value="'.$genre->id.'" selected', false);
        $response->assertSee('value="title" selected', false);
    }

    /** 登録画面の最上部にISBN自動入力セクションが描画される */
    public function test_create_renders_isbn_autofill_section(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/books/create');

        $response->assertOk();
        $response->assertSee('ISBN から書籍情報を自動入力');
        $response->assertSee('Google Books API');
        $response->assertSee('id="isbn_search"', false);
        $response->assertSee('id="isbn_search_button"', false);
        // 自動入力用に searchByIsbn エンドポイントを叩くスクリプトが埋め込まれている
        $response->assertSee('/books/isbn/', false);
    }
}
