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

        $this->notify($today->copy()->addDays(3), NotificationTiming::ThreeDaysBefore);
        $this->notify($today->copy(), NotificationTiming::OnDueDate);
        $this->notify($today->copy()->subDays(3), NotificationTiming::ThreeDaysAfter);

        return self::SUCCESS;
    }

    /**
     * 指定日を期日とする進行中の計画の所有者へ通知を送る。
     */
    private function notify(Carbon $targetDate, NotificationTiming $timing): void
    {
        ReadingPlan::where('status', ReadingPlanStatus::InProgress)
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
