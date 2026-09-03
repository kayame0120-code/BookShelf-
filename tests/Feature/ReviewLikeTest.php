<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    /** F-L1: いいねトグルON/OFF */
    public function test_like_toggle_on_off(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $this->actingAs($user)->post(route('reviews.like', $review));
        $this->assertDatabaseHas('review_likes', ['review_id' => $review->id, 'user_id' => $user->id]);

        $this->actingAs($user)->post(route('reviews.like', $review));
        $this->assertDatabaseMissing('review_likes', ['review_id' => $review->id, 'user_id' => $user->id]);
    }

    /** F-L2: 削除済み書籍下でのいいね */
    public function test_like_on_deleted_book_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);
        $book->delete();

        $this->actingAs($user)->post(route('reviews.like', $review));
        $this->assertSame(1, DB::table('review_likes')->where('review_id', $review->id)->count());
    }

    /** C-4: 未ログインでいいねを押すと/loginへリダイレクト */
    public function test_guest_cannot_like_review(): void
    {
        $review = Review::factory()->create();

        $this->post(route('reviews.like', $review))->assertRedirect('/login');
    }
}
