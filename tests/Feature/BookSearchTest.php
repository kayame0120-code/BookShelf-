<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 走行⑦：書籍一覧のキーワード検索・ジャンル絞り込み・並び替え（検品表 D）。
 */
class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ジャンルを紐付けた書籍を作成するヘルパ。
     */
    private function makeBook(array $attrs, ?Genre $genre = null): Book
    {
        $book = Book::factory()->create($attrs);
        if ($genre !== null) {
            $book->genres()->sync([$genre->id]);
        }

        return $book;
    }

    /** D-1: キーワード部分一致（タイトル・著者）で該当書籍のみが返る */
    public function test_keyword_partial_match_returns_only_matching_books(): void
    {
        $this->makeBook(['title' => 'Laravel実践入門', 'author' => '田中太郎']);
        $this->makeBook(['title' => 'Python基礎講座', 'author' => '佐藤花子']);

        // タイトル部分一致
        $this->get('/books?keyword=Laravel')
            ->assertOk()
            ->assertSee('Laravel実践入門')
            ->assertDontSee('Python基礎講座');

        // 著者部分一致
        $this->get('/books?keyword=佐藤')
            ->assertOk()
            ->assertSee('Python基礎講座')
            ->assertDontSee('Laravel実践入門');
    }

    /** D-2: ジャンル絞り込みで該当書籍のみが返る */
    public function test_genre_filter_returns_only_matching_books(): void
    {
        $tech = Genre::factory()->create(['name' => '技術書']);
        $novel = Genre::factory()->create(['name' => '小説']);

        $this->makeBook(['title' => '技術書のほう'], $tech);
        $this->makeBook(['title' => '小説のほう'], $novel);

        $this->get('/books?genre='.$tech->id)
            ->assertOk()
            ->assertSee('技術書のほう')
            ->assertDontSee('小説のほう');
    }

    /** D-3: 並び替えが指定順で返る（title昇順とnewestで順序が変わる） */
    public function test_sort_returns_books_in_specified_order(): void
    {
        // created_atを明示し、title順とnewest順が明確に食い違うようにする。
        $this->makeBook(['title' => 'AAA Book', 'created_at' => now()->subDays(3)]);
        $this->makeBook(['title' => 'MMM Book', 'created_at' => now()->subDays(2)]);
        $this->makeBook(['title' => 'ZZZ Book', 'created_at' => now()->subDay()]);

        // title昇順：AAA→MMM→ZZZ
        $this->get('/books?sort=title')
            ->assertOk()
            ->assertSeeInOrder(['AAA Book', 'MMM Book', 'ZZZ Book']);

        // newest（作成日時の降順）：ZZZ→MMM→AAA（titleとは逆順）
        $this->get('/books?sort=newest')
            ->assertOk()
            ->assertSeeInOrder(['ZZZ Book', 'MMM Book', 'AAA Book']);
    }

    /** D-4: 検索条件を維持したままページ送りできる（withQueryStringで条件保持） */
    public function test_search_condition_preserved_across_pagination(): void
    {
        // キーワード一致書籍を11件作成し2ページに分割させる。
        for ($i = 1; $i <= 11; $i++) {
            $this->makeBook(['title' => "ZZUNIQUE {$i}"]);
        }

        $response = $this->get('/books?keyword=ZZUNIQUE');
        $response->assertOk();
        // ページネーションリンクにkeywordとpage=2が引き継がれている。
        $response->assertSee('keyword=ZZUNIQUE', false);
        $response->assertSee('page=2', false);
    }

    /** 論理削除済み書籍は一覧・検索から除外される（CLAUDE.md §9-2） */
    public function test_soft_deleted_book_excluded_from_search(): void
    {
        $book = $this->makeBook(['title' => '削除される本KEYWORDX']);
        $book->delete();

        $this->get('/books?keyword=KEYWORDX')
            ->assertOk()
            ->assertDontSee('削除される本KEYWORDX');
    }

    /** 認可：一覧・検索は未ログインでも閲覧できる（公開画面） */
    public function test_search_is_public(): void
    {
        $this->makeBook(['title' => '公開一覧の本']);

        $this->get('/books?keyword=公開一覧')
            ->assertOk()
            ->assertSee('公開一覧の本');
    }
}
