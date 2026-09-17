<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 走行⑪：自動失効バッチ reading-plans:expire（検品表 J）。
 * 対象は「本日の3日前ちょうど・in_progress」のみ（等値実装）。時刻は setTestNow で固定する。
 */
class ExpireReadingPlansTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // J-5: 時刻依存を避けるため today を固定する。
        Carbon::setTestNow(Carbon::parse('2026-09-17'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    private function makePlan(ReadingPlanStatus $status, string $targetDate, ?string $completedAt = null): ReadingPlan
    {
        return ReadingPlan::create([
            'user_id' => User::factory()->create()->id,
            'book_id' => Book::factory()->create()->id,
            'target_date' => $targetDate,
            'status' => $status,
            'completed_at' => $completedAt,
        ]);
    }

    /** J-1: 本日の3日前ちょうど・in_progress が expired に変わる */
    public function test_exactly_three_days_ago_in_progress_becomes_expired(): void
    {
        $plan = $this->makePlan(ReadingPlanStatus::InProgress, Carbon::today()->subDays(3)->toDateString());

        $this->artisan('reading-plans:expire')->assertExitCode(0);

        $this->assertSame(ReadingPlanStatus::Expired, $plan->fresh()->status);
    }

    /** J-2: 本日の4日前・in_progress は in_progress のまま（等値実装を強制） */
    public function test_four_days_ago_in_progress_stays_in_progress(): void
    {
        $plan = $this->makePlan(ReadingPlanStatus::InProgress, Carbon::today()->subDays(4)->toDateString());

        $this->artisan('reading-plans:expire')->assertExitCode(0);

        $this->assertSame(ReadingPlanStatus::InProgress, $plan->fresh()->status);
    }

    /** J-3: 本日の2日前・当日・未来は変わらない */
    public function test_two_days_ago_today_and_future_stay_in_progress(): void
    {
        $twoDaysAgo = $this->makePlan(ReadingPlanStatus::InProgress, Carbon::today()->subDays(2)->toDateString());
        $today = $this->makePlan(ReadingPlanStatus::InProgress, Carbon::today()->toDateString());
        $future = $this->makePlan(ReadingPlanStatus::InProgress, Carbon::today()->addDays(3)->toDateString());

        $this->artisan('reading-plans:expire')->assertExitCode(0);

        $this->assertSame(ReadingPlanStatus::InProgress, $twoDaysAgo->fresh()->status);
        $this->assertSame(ReadingPlanStatus::InProgress, $today->fresh()->status);
        $this->assertSame(ReadingPlanStatus::InProgress, $future->fresh()->status);
    }

    /** J-4: 完了済み（3日前）は completed のまま、completed_at も書き換わらない */
    public function test_completed_plan_unchanged(): void
    {
        $completedAt = '2026-09-10 12:00:00';
        $plan = $this->makePlan(ReadingPlanStatus::Completed, Carbon::today()->subDays(3)->toDateString(), $completedAt);

        $this->artisan('reading-plans:expire')->assertExitCode(0);

        $fresh = $plan->fresh();
        $this->assertSame(ReadingPlanStatus::Completed, $fresh->status);
        $this->assertSame($completedAt, $fresh->completed_at->toDateTimeString());
    }
}
