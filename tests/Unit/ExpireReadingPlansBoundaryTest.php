<?php

namespace Tests\Unit;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 走行⑪：自動失効バッチの日付境界判定の補強（検品表 J、発注書§5）。
 * 3日前ちょうど（=対象）と4日前（=非対象）の等値境界を単体で突く。
 */
class ExpireReadingPlansBoundaryTest extends TestCase
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

    private function makeInProgress(string $targetDate): ReadingPlan
    {
        return ReadingPlan::create([
            'user_id' => User::factory()->create()->id,
            'book_id' => Book::factory()->create()->id,
            'target_date' => $targetDate,
            'status' => ReadingPlanStatus::InProgress,
        ]);
    }

    /**
     * 境界：3日前ちょうどは失効、4日前は据え置き（等値比較であることの確認）。
     *
     * @return array<string, array{0: string, 1: ReadingPlanStatus}>
     */
    public static function boundaryProvider(): array
    {
        return [
            '3日前ちょうど→失効' => ['-3 days', ReadingPlanStatus::Expired],
            '4日前→据え置き' => ['-4 days', ReadingPlanStatus::InProgress],
            '2日前→据え置き' => ['-2 days', ReadingPlanStatus::InProgress],
        ];
    }

    /**
     * @dataProvider boundaryProvider
     */
    public function test_boundary(string $modifier, ReadingPlanStatus $expected): void
    {
        $plan = $this->makeInProgress(Carbon::today()->modify($modifier)->toDateString());

        $this->artisan('reading-plans:expire')->assertExitCode(0);

        $this->assertSame($expected, $plan->fresh()->status);
    }
}
