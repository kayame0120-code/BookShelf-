<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

/**
 * 走行⑪⑫：バッチのスケジュール登録（検品表⑭差し戻し 3）。
 * reading-plans:expire=0:00、reading-plans:send-reminders=7:00 の登録を確認する。
 */
class ScheduleTest extends TestCase
{
    /**
     * 登録済みイベントから、コマンド名を含むイベントのcron式を引く。
     */
    private function expressionForCommand(string $needle): ?string
    {
        $schedule = $this->app->make(Schedule::class);

        foreach ($schedule->events() as $event) {
            if (str_contains($event->command ?? '', $needle)) {
                return $event->expression;
            }
        }

        return null;
    }

    /** reading-plans:expire が毎日0:00に登録されている */
    public function test_expire_scheduled_at_midnight(): void
    {
        $this->assertSame('0 0 * * *', $this->expressionForCommand('reading-plans:expire'));
    }

    /** reading-plans:send-reminders が毎日7:00に登録されている */
    public function test_reminders_scheduled_at_seven(): void
    {
        $this->assertSame('0 7 * * *', $this->expressionForCommand('reading-plans:send-reminders'));
    }
}
