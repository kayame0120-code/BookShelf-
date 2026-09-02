<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /** F-K1: 平均評価降順でTOP10 */
    public function test_ranking_ordered_by_avg_rating_desc(): void
    {
        $low = Book::factory()->create(['title' => '低評価本']);
        $high = Book::factory()->create(['title' => '高評価本']);
        Review::factory()->create(['book_id' => $low->id, 'rating' => 3]);
        Review::factory()->create(['book_id' => $high->id, 'rating' => 5]);

        $rankedBooks = $this->get('/ranking')->assertOk()->viewData('rankedBooks');

        $this->assertSame($high->id, $rankedBooks->first()->id);
        $this->assertTrue(
            $rankedBooks->search(fn ($b) => $b->id === $high->id) <
            $rankedBooks->search(fn ($b) => $b->id === $low->id)
        );
    }

    /** F-K2: レビュー0件は対象外 */
    public function test_ranking_excludes_books_without_reviews(): void
    {
        $reviewed = Book::factory()->create(['title' => 'レビューあり本']);
        $noReview = Book::factory()->create(['title' => 'レビューなし本']);
        Review::factory()->create(['book_id' => $reviewed->id, 'rating' => 4]);

        $ids = $this->get('/ranking')->viewData('rankedBooks')->pluck('id');

        $this->assertTrue($ids->contains($reviewed->id));
        $this->assertFalse($ids->contains($noReview->id));
    }

    /** F-K3: 削除済み書籍は集計対象外 */
    public function test_ranking_excludes_deleted_books(): void
    {
        $book = Book::factory()->create(['title' => '削除される本']);
        Review::factory()->create(['book_id' => $book->id, 'rating' => 5]);
        $book->delete();

        $this->get('/ranking')
            ->assertOk()
            ->assertDontSee('削除される本');
    }

    /** F-K4: 未ログインでも閲覧可 */
    public function test_ranking_public(): void
    {
        $this->get('/ranking')->assertOk();
    }
}
