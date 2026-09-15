<?php

namespace App\Notifications;

use App\Enums\NotificationTiming;
use App\Models\ReadingPlan;
use Illuminate\Notifications\Notification;

class ReadingPlanReminder extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly ReadingPlan $plan,
        private readonly NotificationTiming $timing,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for the database channel.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'timing' => $this->timing->value,
            'title' => $this->title(),
            'body' => $this->body(),
            'reading_plan_id' => $this->plan->id,
        ];
    }

    /**
     * タイミング別の通知タイトルを返す。
     */
    private function title(): string
    {
        return match ($this->timing) {
            NotificationTiming::ThreeDaysBefore => '読書計画のリマインダー',
            NotificationTiming::OnDueDate => '読書計画の期日です',
            NotificationTiming::ThreeDaysAfter => '読書計画の期日が過ぎました',
        };
    }

    /**
     * タイミング別の通知本文を返す。
     */
    private function body(): string
    {
        $title = $this->plan->book->title;

        return match ($this->timing) {
            NotificationTiming::ThreeDaysBefore => "『{$title}』の期日まで残り3日です。",
            NotificationTiming::OnDueDate => "『{$title}』の期日は本日です。",
            NotificationTiming::ThreeDaysAfter => "『{$title}』の期日から3日が経過したため、計画は自動的に「期限切れ」になりました。",
        };
    }
}
