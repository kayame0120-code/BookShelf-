<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 走行⑨：マイ読書レポート（検品表 F）。
 * レビュー件数の固定値には依存せず、投入したレビューの内容から統計を検証する。
 */
class ReadingReportTest extends TestCase
{
    use RefreshDatabase;

    /** F-1 / F-2: 4種の統計（総レビュー数・読了冊数・評価分布・高評価TOP5・ジャンル別TOP5）を確認する */
    public function test_report_shows_all_statistics(): void
    {
        $user = User::factory()->create();
        $genreTech = Genre::factory()->create(['name' => '技術書']);
        $genreNovel = Genre::factory()->create(['name' => '小説']);

        $bookA = Book::factory()->create(['title' => '高評価の本', 'author' => '著者A', 'user_id' => $user->id]);
        $bookA->genres()->sync([$genreTech->id]);
        $bookB = Book::factory()->create(['title' => '低評価の本', 'author' => '著者B', 'user_id' => $user->id]);
        $bookB->genres()->sync([$genreNovel->id]);

        // 自分のレビュー：bookA=5, bookB=2。他人のレビューは集計対象外。
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $bookA->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $bookB->id, 'rating' => 2]);
        Review::factory()->create(['user_id' => User::factory()->create()->id, 'book_id' => $bookA->id, 'rating' => 1]);

        $response = $this->actingAs($user)->get('/reports');
        $response->assertOk();

        $stats = $response->viewData('stats');

        // 基本サマリー：総レビュー数=2、読了冊数（ユニーク書籍数）=2、平均=(5+2)/2=3.5
        $this->assertSame(2, $stats['summary']['total_reviews']);
        $this->assertSame(2, $stats['summary']['books_read']);
        $this->assertSame(3.5, $stats['summary']['average_rating']);

        // 評価分布：添字0=★1〜添字4=★5。★2が1件、★5が1件。
        $this->assertSame(1, $stats['rating_distribution'][1]); // ★2
        $this->assertSame(1, $stats['rating_distribution'][4]); // ★5
        $this->assertSame(0, $stats['rating_distribution'][0]); // ★1（他人分は含めない）

        // 高評価TOP5：4以上のみ。bookAのみ該当。
        $topIds = collect($stats['top_rated_books'])->pluck('id')->all();
        $this->assertContains($bookA->id, $topIds);
        $this->assertNotContains($bookB->id, $topIds);

        // ジャンル別TOP5：技術書=5.0、小説=2.0。
        $genreRatings = collect($stats['genre_ratings'])->keyBy('name');
        $this->assertSame(5.0, (float) $genreRatings['技術書']['average_rating']);
        $this->assertSame(2.0, (float) $genreRatings['小説']['average_rating']);
    }

    /**
     * F-2: 統計がレビュー件数の固定値に依存していないことの明示。
     * 同一書籍への自分の複数レビュー（可変件数）でも読了冊数はユニーク書籍数で数える。
     */
    public function test_books_read_counts_unique_books_not_review_count(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $book->genres()->sync([Genre::factory()->create()->id]);

        // 同じ本に3件のレビュー（rand(2,4)相当の可変件数を模す）。
        Review::factory()->count(3)->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 4]);

        $stats = $this->actingAs($user)->get('/reports')->viewData('stats');

        $this->assertSame(3, $stats['summary']['total_reviews']);
        $this->assertSame(1, $stats['summary']['books_read']); // 件数3でも冊数は1
    }

    /** F-3: 論理削除済み書籍への自分のレビューも集計・リンクに含まれる */
    public function test_soft_deleted_book_review_included_in_report(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => 'ジャンルX']);
        $book = Book::factory()->create(['title' => '削除済みだが高評価', 'user_id' => $user->id]);
        $book->genres()->sync([$genre->id]);

        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 5]);

        $book->delete();

        $response = $this->actingAs($user)->get('/reports');
        $response->assertOk();
        $stats = $response->viewData('stats');

        // 削除済み書籍のレビューも総レビュー数・高評価TOP5・ジャンル別に含まれる。
        $this->assertSame(1, $stats['summary']['total_reviews']);
        $this->assertContains($book->id, collect($stats['top_rated_books'])->pluck('id')->all());
        $this->assertContains('ジャンルX', collect($stats['genre_ratings'])->pluck('name')->all());
        // 一覧のリンク（削除済み書籍の詳細）が表示されている。
        $response->assertSee('削除済みだが高評価');
    }

    /** 認可：レポートは未ログインだと/loginへリダイレクト */
    public function test_report_requires_authentication(): void
    {
        $this->get('/reports')->assertRedirect('/login');
    }
}
