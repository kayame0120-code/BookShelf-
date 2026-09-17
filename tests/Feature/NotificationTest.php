<?php

namespace Tests\Feature;

use App\Enums\NotificationTiming;
use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 走行⑫：通知一覧の表示・既読化（検品表⑭差し戻し 1）。
 */
class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 指定ユーザーに読書計画リマインダー通知を1件作成し、そのDatabaseNotificationを返す。
     */
    private function notifyUser(User $user, NotificationTiming $timing, string $bookTitle): \Illuminate\Notifications\DatabaseNotification
    {
        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create(['title' => $bookTitle])->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $user->notify(new ReadingPlanReminder($plan, $timing));

        return $user->fresh()->notifications()->latest()->first();
    }

    /** 通知一覧：自分の通知のみ・新しい順に表示される */
    public function test_index_shows_own_notifications_newest_first(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $older = $this->notifyUser($user, NotificationTiming::ThreeDaysBefore, '古い通知の本');
        $newer = $this->notifyUser($user, NotificationTiming::OnDueDate, '新しい通知の本');
        // created_at を明示して並び順を確定させる。
        $older->forceFill(['created_at' => now()->subMinutes(10)])->save();
        $newer->forceFill(['created_at' => now()])->save();

        // 他人の通知（一覧に出てはいけない）
        $this->notifyUser($other, NotificationTiming::ThreeDaysBefore, '他人の通知の本');

        $response = $this->actingAs($user)->get('/notifications');
        $response->assertOk();

        // 自分の通知は2件、新しい順（新しい通知→古い通知）。
        $this->assertCount(2, $response->viewData('notifications'));
        $response->assertSeeInOrder(['新しい通知の本', '古い通知の本']);
        // 他人の通知は表示されない。
        $response->assertDontSee('他人の通知の本');
    }

    /** 既読化：本人は既読にでき、read_atが入る */
    public function test_owner_can_mark_notification_read(): void
    {
        $user = User::factory()->create();
        $notification = $this->notifyUser($user, NotificationTiming::OnDueDate, '既読にする本');

        $this->assertNull($notification->read_at);

        $this->actingAs($user)
            ->from('/notifications')
            ->post("/notifications/{$notification->id}/read")
            ->assertRedirect('/notifications');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    /** 既読化：他人の通知は既読化できない（403）・read_atは変わらない */
    public function test_other_user_cannot_mark_notification_read(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $notification = $this->notifyUser($owner, NotificationTiming::OnDueDate, '他人が触れない本');

        $this->actingAs($other)
            ->post("/notifications/{$notification->id}/read")
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }

    /** 認可：未認証で通知一覧を叩くと/loginへリダイレクト */
    public function test_guest_redirected_from_notifications(): void
    {
        $this->get('/notifications')->assertRedirect('/login');
    }
}
