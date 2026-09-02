<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /** U-B1: Book belongsTo User */
    public function test_book_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $book->user);
        $this->assertSame($user->id, $book->user->id);
    }

    /** U-B2: Book belongsToMany Genre (book_genre) */
    public function test_book_belongs_to_many_genres(): void
    {
        $book = Book::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book->genres()->sync($genres->pluck('id')->all());

        $this->assertCount(2, $book->fresh()->genres);
        $this->assertInstanceOf(Genre::class, $book->genres->first());
    }

    /** U-B3: Book hasMany Review */
    public function test_book_has_many_reviews(): void
    {
        $book = Book::factory()->create();
        Review::factory()->count(3)->create(['book_id' => $book->id]);

        $this->assertCount(3, $book->fresh()->reviews);
        $this->assertInstanceOf(Review::class, $book->reviews->first());
    }

    /** U-B4: withAvg reviews rating / null when no reviews */
    public function test_reviews_avg_rating(): void
    {
        $book = Book::factory()->create();
        Review::factory()->create(['book_id' => $book->id, 'rating' => 4]);
        Review::factory()->create(['book_id' => $book->id, 'rating' => 2]);

        $withReviews = Book::withAvg('reviews', 'rating')->find($book->id);
        $this->assertEquals(3.0, (float) $withReviews->reviews_avg_rating);

        $noReview = Book::factory()->create();
        $result = Book::withAvg('reviews', 'rating')->find($noReview->id);
        $this->assertNull($result->reviews_avg_rating);
    }

    /** U-B5: SoftDeletes standard exclusion */
    public function test_soft_delete_exclusion(): void
    {
        $book = Book::factory()->create();
        $book->delete();

        $this->assertNull(Book::find($book->id));
        $this->assertNotNull(Book::withTrashed()->find($book->id));
    }
}
