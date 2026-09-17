<?php

namespace Tests\Feature;

use App\Enums\NotificationTiming;
use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 走行⑫：リマインダーバッチ reading-plans:send-reminders（検品表 K）。
 * three_days_before・on_due_date は in_progress のみ。
 * three_days_after は in_progress ＋ expired（完了以外）を対象とする。
 */
class ReadingPlanReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-09-17'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    /**
     * 指定ユーザー・書籍・状態・期日の計画を作る。
     */
    private function makePlan(User $user, ReadingPlanStatus $status, string $targetDate): ReadingPlan
    {
        return ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create()->id,
            'target_date' => $targetDate,
            'status' => $status,
        ]);
    }

    /**
     * 指定ユーザーが受け取った通知の timing 値の一覧。
     *
     * @return array<int, string>
     */
    private function timingsOf(User $user): array
    {
        return $user->fresh()->notifications
            ->map(fn ($n) => $n->data['timing'])
            ->all();
    }

    /** K-1: three_days_before・on_due_date は in_progress のみを対象とする */
    public function test_three_days_before_and_on_due_date_target_in_progress_only(): void
    {
        // 3日前リマインダー対象（in_progress・target=today+3）
        $beforeUser = User::factory()->create();
        $this->makePlan($beforeUser, ReadingPlanStatus::InProgress, Carbon::today()->addDays(3)->toDateString());

        // 当日リマインダー対象（in_progress・target=today）
        $dueUser = User::factory()->create();
        $this->makePlan($dueUser, ReadingPlanStatus::InProgress, Carbon::today()->toDateString());

        // completedは対象外（target=today+3, today でそれぞれ）
        $completedBeforeUser = User::factory()->create();
        $this->makePlan($completedBeforeUser, ReadingPlanStatus::Completed, Carbon::today()->addDays(3)->toDateString());
        $completedDueUser = User::factory()->create();
        $this->makePlan($completedDueUser, ReadingPlanStatus::Completed, Carbon::today()->toDateString());

        $this->artisan('reading-plans:send-reminders')->assertExitCode(0);

        $this->assertSame([NotificationTiming::ThreeDaysBefore->value], $this->timingsOf($beforeUser));
        $this->assertSame([NotificationTiming::OnDueDate->value], $this->timingsOf($dueUser));
        $this->assertSame([], $this->timingsOf($completedBeforeUser));
        $this->assertSame([], $this->timingsOf($completedDueUser));
    }

    /** K-2: three_days_after は in_progress ＋ expired を対象とし、expired にも通知される */
    public function test_three_days_after_targets_in_progress_and_expired(): void
    {
        // expired・target=today-3 → three_days_after 通知される
        $expiredUser = User::factory()->create();
        $this->makePlan($expiredUser, ReadingPlanStatus::Expired, Carbon::today()->subDays(3)->toDateString());

        // in_progress・target=today-3 → three_days_after 通知される
        $inProgressUser = User::factory()->create();
        $this->makePlan($inProgressUser, ReadingPlanStatus::InProgress, Carbon::today()->subDays(3)->toDateString());

        // completed・target=today-3 → 対象外
        $completedUser = User::factory()->create();
        $this->makePlan($completedUser, ReadingPlanStatus::Completed, Carbon::today()->subDays(3)->toDateString());

        $this->artisan('reading-plans:send-reminders')->assertExitCode(0);

        $this->assertSame([NotificationTiming::ThreeDaysAfter->value], $this->timingsOf($expiredUser));
        $this->assertSame([NotificationTiming::ThreeDaysAfter->value], $this->timingsOf($inProgressUser));
        $this->assertSame([], $this->timingsOf($completedUser));
    }

    /**
     * K-3: シードシナリオ（計画#3の二重シナリオ）。
     * target=today-3・in_progress の計画は 0:00 失効バッチで expired 化し、
     * 7:00 リマインダーで three_days_after 通知が作成される。
     */
    public function test_plan3_double_scenario(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan($user, ReadingPlanStatus::InProgress, Carbon::today()->subDays(3)->toDateString());

        // 0:00 失効バッチ → expired 化
        $this->artisan('reading-plans:expire')->assertExitCode(0);
        $this->assertSame(ReadingPlanStatus::Expired, $plan->fresh()->status);

        // 7:00 リマインダーバッチ → three_days_after 通知
        $this->artisan('reading-plans:send-reminders')->assertExitCode(0);

        $this->assertSame([NotificationTiming::ThreeDaysAfter->value], $this->timingsOf($user));
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
        ]);
    }
}
