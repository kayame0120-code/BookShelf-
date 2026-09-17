<?php

namespace Tests\Unit;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 応用フェーズで追加されたモデルの未テストリレーションを確認する（検品表⑭差し戻し 5）。
 * Book::favoritedByUsers / User::books・reviews・readingPlans。
 */
class ModelRelationTest extends TestCase
{
    use RefreshDatabase;

    /** Book::favoritedByUsers() が favorites 中間テーブル経由でユーザーを返す */
    public function test_book_favorited_by_users(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->favoriteBooks()->attach($book->id);

        $this->assertTrue($book->favoritedByUsers->contains($user));
        $this->assertSame(1, $book->favoritedByUsers()->count());
    }

    /** User::books() が自分の登録書籍を返す */
    public function test_user_books(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(2)->create(['user_id' => $user->id]);
        Book::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->assertSame(2, $user->books()->count());
        $this->assertTrue($user->books->every(fn (Book $b) => $b->user_id === $user->id));
    }

    /** User::reviews() が自分のレビューを返す */
    public function test_user_reviews(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        Review::factory()->count(3)->create(['user_id' => $user->id, 'book_id' => $book->id]);
        Review::factory()->create(['user_id' => User::factory()->create()->id, 'book_id' => $book->id]);

        $this->assertSame(3, $user->reviews()->count());
    }

    /** User::readingPlans() が自分の読書計画を返す */
    public function test_user_reading_plans(): void
    {
        $user = User::factory()->create();
        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create()->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $this->assertSame(1, $user->readingPlans()->count());
        $this->assertSame($user->id, $user->readingPlans->first()->user_id);
    }
}
