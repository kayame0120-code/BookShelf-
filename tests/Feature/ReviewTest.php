<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /** F-R1: 投稿 */
    public function test_store_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 4,
            'comment' => '良い本でした',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'レビューを投稿しました');
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => '良い本でした',
        ]);
    }

    /** F-R1: commentは任意 */
    public function test_store_review_without_comment(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post(route('reviews.store', $book), ['rating' => 3])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('reviews', ['book_id' => $book->id, 'rating' => 3, 'comment' => null]);
    }

    /** F-R1: rating必須 */
    public function test_store_review_requires_rating(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post(route('reviews.store', $book), ['comment' => 'x'])
            ->assertSessionHasErrors('rating');
    }

    /** F-R2: 編集の認可 */
    public function test_edit_authorization(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)->get(route('reviews.edit', $review))->assertOk();
        $this->actingAs($other)->get(route('reviews.edit', $review))->assertForbidden();

        $this->actingAs($other)->put(route('reviews.update', $review), ['rating' => 5])
            ->assertForbidden();
        $this->actingAs($owner)->put(route('reviews.update', $review), ['rating' => 5, 'comment' => '更新'])
            ->assertRedirect(route('books.show', $review->book));
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 5, 'comment' => '更新']);
    }

    /** F-R3: 削除の認可 */
    public function test_delete_authorization(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->delete(route('reviews.destroy', $review))->assertForbidden();
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);

        $this->actingAs($owner)->delete(route('reviews.destroy', $review))
            ->assertRedirect(route('books.show', $review->book))
            ->assertSessionHas('success', 'レビューを削除しました');
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /** F-R4: いいねのcascade */
    public function test_review_delete_cascades_likes(): void
    {
        $owner = User::factory()->create();
        $liker = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);
        $review->likedByUsers()->attach($liker->id);

        $this->assertSame(1, \DB::table('review_likes')->where('review_id', $review->id)->count());

        $this->actingAs($owner)->delete(route('reviews.destroy', $review));

        $this->assertSame(0, \DB::table('review_likes')->where('review_id', $review->id)->count());
    }

    /** F-R5: 削除済み書籍下でのレビュー編集・削除 */
    public function test_review_operations_on_deleted_book(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id]);
        $book->delete();

        // 編集画面到達可能
        $this->actingAs($owner)->get(route('reviews.edit', $review))->assertOk();

        // 更新可能
        $this->actingAs($owner)->put(route('reviews.update', $review), ['rating' => 2, 'comment' => 'z'])
            ->assertRedirect(route('books.show', $book));

        // 削除可能
        $this->actingAs($owner)->delete(route('reviews.destroy', $review))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
