<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 走行⑪：読書計画CRUD・重複制御・編集制限（検品表 I）。
 */
class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 読書計画レコードを直接作成するヘルパ（バリデーションを経由しない）。
     */
    private function makePlan(User $user, Book $book, ReadingPlanStatus $status, string $targetDate, ?string $completedAt = null): ReadingPlan
    {
        return ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
            'status' => $status,
            'completed_at' => $completedAt,
        ]);
    }

    // ================= I-1: CRUDの正常系（状態＋遷移先） =================

    /** I-1: 作成の正常系（in_progressで作成され、一覧へリダイレクト） */
    public function test_store_success(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を登録しました');
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    /** I-1: 更新の正常系（target_dateが変わり一覧へリダイレクト） */
    public function test_update_success(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $plan = $this->makePlan($user, $book, ReadingPlanStatus::InProgress, now()->addDays(3)->toDateString());
        $newDate = now()->addDays(10)->toDateString();

        $response = $this->actingAs($user)->put("/reading-plans/{$plan->id}", ['target_date' => $newDate]);

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を更新しました');
        $this->assertSame($newDate, $plan->fresh()->target_date->toDateString());
    }

    /** I-1: 読了（complete）の正常系（completedになり completed_at が入る） */
    public function test_complete_success(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $plan = $this->makePlan($user, $book, ReadingPlanStatus::InProgress, now()->addDays(3)->toDateString());

        $response = $this->actingAs($user)->post("/reading-plans/{$plan->id}/complete");

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を完了しました');
        $fresh = $plan->fresh();
        $this->assertSame(ReadingPlanStatus::Completed, $fresh->status);
        $this->assertNotNull($fresh->completed_at);
    }

    /** I-1: 削除の正常系（レコード削除・一覧へリダイレクト） */
    public function test_destroy_success(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $plan = $this->makePlan($user, $book, ReadingPlanStatus::InProgress, now()->addDays(3)->toDateString());

        $response = $this->actingAs($user)->delete("/reading-plans/{$plan->id}");

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を削除しました');
        $this->assertDatabaseMissing('reading_plans', ['id' => $plan->id]);
    }

    // ================= I-2: 重複制御 =================

    /** I-2: 同一ユーザー・同一書籍・進行中の重複作成が弾かれる */
    public function test_duplicate_in_progress_plan_rejected(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->makePlan($user, $book, ReadingPlanStatus::InProgress, now()->addDays(3)->toDateString());

        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertSessionHasErrors(['book_id' => 'この書籍は既に読書計画に登録されています']);
        $this->assertSame(1, ReadingPlan::where('book_id', $book->id)->count());
    }

    // ================= I-3: 完了・期限切れなら同一書籍で再作成可 =================

    /** I-3: 完了済み計画があっても同一書籍で新規作成できる */
    public function test_can_create_when_existing_plan_completed(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->makePlan($user, $book, ReadingPlanStatus::Completed, now()->subDays(1)->toDateString(), now()->toDateTimeString());

        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHasNoErrors();
        $this->assertSame(2, ReadingPlan::where('book_id', $book->id)->count());
    }

    /** I-3: 期限切れ計画があっても同一書籍で新規作成できる */
    public function test_can_create_when_existing_plan_expired(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->makePlan($user, $book, ReadingPlanStatus::Expired, now()->subDays(3)->toDateString());

        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHasNoErrors();
        $this->assertSame(2, ReadingPlan::where('book_id', $book->id)->count());
    }

    // ================= I-4: 編集はtarget_dateのみ =================

    /** I-4: 編集で target_date のみ変更でき、book_id/status を混ぜても変わらない */
    public function test_update_changes_only_target_date(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $otherBook = Book::factory()->create();
        $plan = $this->makePlan($user, $book, ReadingPlanStatus::InProgress, now()->addDays(3)->toDateString());
        $newDate = now()->addDays(9)->toDateString();

        $this->actingAs($user)->put("/reading-plans/{$plan->id}", [
            'target_date' => $newDate,
            'book_id' => $otherBook->id,      // 混入させても無視される
            'status' => ReadingPlanStatus::Completed->value, // 混入させても無視される
        ])->assertRedirect(route('reading-plans.index'));

        $fresh = $plan->fresh();
        $this->assertSame($newDate, $fresh->target_date->toDateString());
        $this->assertSame($book->id, $fresh->book_id);              // 変わらない
        $this->assertSame(ReadingPlanStatus::InProgress, $fresh->status); // 変わらない
    }

    // ================= I-5: 他人の計画への操作は403（メソッドごと個別） =================

    /** I-5: 他人の計画のedit（GET）は403 */
    public function test_other_users_plan_edit_forbidden(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $plan = $this->makePlan($owner, Book::factory()->create(), ReadingPlanStatus::InProgress, now()->addDays(3)->toDateString());

        $this->actingAs($other)->get("/reading-plans/{$plan->id}/edit")->assertForbidden();
    }

    /** I-5: 他人の計画のupdate（PUT）は403 */
    public function test_other_users_plan_update_forbidden(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $plan = $this->makePlan($owner, Book::factory()->create(), ReadingPlanStatus::InProgress, now()->addDays(3)->toDateString());

        $this->actingAs($other)->put("/reading-plans/{$plan->id}", ['target_date' => now()->addDays(9)->toDateString()])
            ->assertForbidden();
    }

    /** I-5: 他人の計画のcomplete（POST）は403 */
    public function test_other_users_plan_complete_forbidden(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $plan = $this->makePlan($owner, Book::factory()->create(), ReadingPlanStatus::InProgress, now()->addDays(3)->toDateString());

        $this->actingAs($other)->post("/reading-plans/{$plan->id}/complete")->assertForbidden();
        $this->assertSame(ReadingPlanStatus::InProgress, $plan->fresh()->status);
    }

    /** I-5: 他人の計画のdestroy（DELETE）は403 */
    public function test_other_users_plan_destroy_forbidden(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $plan = $this->makePlan($owner, Book::factory()->create(), ReadingPlanStatus::InProgress, now()->addDays(3)->toDateString());

        $this->actingAs($other)->delete("/reading-plans/{$plan->id}")->assertForbidden();
        $this->assertDatabaseHas('reading_plans', ['id' => $plan->id]);
    }

    // ================= I-6: 完了済み計画への直接completeは403 =================

    /** I-6: 完了済み計画への直接completeは403 */
    public function test_complete_on_already_completed_plan_forbidden(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan($user, Book::factory()->create(), ReadingPlanStatus::Completed, now()->subDays(1)->toDateString(), now()->toDateTimeString());

        $this->actingAs($user)->post("/reading-plans/{$plan->id}/complete")->assertForbidden();
    }
}
