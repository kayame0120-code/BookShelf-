<?php

namespace App\Console\Commands;

use App\Enums\NotificationTiming;
use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendReadingPlanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reading-plans:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '進行中の読書計画に対し、期日の3日前・当日・3日後のリマインダー通知を送信する';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();

        // timing 別の対象 status（機能仕様書 notifications.md §5・確定）。
        // three_days_after のみ completed 以外（in_progress ＋ expired）を対象とする。
        // 0:00 の状態更新バッチで既に expired へ切り替わった計画にも「期限切れ」通知を届けるため。
        $this->notify($today->copy()->addDays(3), NotificationTiming::ThreeDaysBefore, [ReadingPlanStatus::InProgress]);
        $this->notify($today->copy(), NotificationTiming::OnDueDate, [ReadingPlanStatus::InProgress]);
        $this->notify($today->copy()->subDays(3), NotificationTiming::ThreeDaysAfter, [ReadingPlanStatus::InProgress, ReadingPlanStatus::Expired]);

        return self::SUCCESS;
    }

    /**
     * 指定日を期日とする対象statusの計画の所有者へ通知を送る。
     *
     * @param  array<int, ReadingPlanStatus>  $statuses
     */
    private function notify(Carbon $targetDate, NotificationTiming $timing, array $statuses): void
    {
        ReadingPlan::whereIn('status', $statuses)
            ->whereDate('target_date', $targetDate)
            ->with('user')
            ->get()
            ->each(function (ReadingPlan $plan) use ($timing): void {
                try {
                    $plan->user->notify(new ReadingPlanReminder($plan, $timing));
                } catch (\Throwable $e) {
                    Log::error("読書計画#{$plan->id}の通知送信に失敗しました: {$e->getMessage()}");
                }
            });
    }
}
