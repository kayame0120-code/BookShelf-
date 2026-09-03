<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /** U-R1: Review belongsTo Book */
    public function test_review_belongs_to_book(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $this->assertInstanceOf(Book::class, $review->book);
        $this->assertSame($book->id, $review->book->id);
    }

    /** U-R2: Review belongsTo User */
    public function test_review_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertSame($user->id, $review->user->id);
    }

    /** U-R3: Review belongsToMany User (likedByUsers) */
    public function test_review_liked_by_users(): void
    {
        $review = Review::factory()->create();
        $users = User::factory()->count(2)->create();
        $review->likedByUsers()->sync($users->pluck('id')->all());

        $this->assertCount(2, $review->fresh()->likedByUsers);
        $this->assertInstanceOf(User::class, $review->likedByUsers->first());
    }

    /** U-R4: Review::book() has withTrashed */
    public function test_review_book_uses_with_trashed(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);
        $book->delete();

        $this->assertNotNull($review->fresh()->book);
        $this->assertSame($book->id, $review->fresh()->book->id);
    }
}
