<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    /** F-P11: db:seed後の件数（reviewsは各書籍rand(2,4)のため固定値では確認しない） */
    public function test_seed_counts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, DB::table('users')->count());
        $this->assertSame(10, DB::table('genres')->count());
        $this->assertSame(11, DB::table('books')->count());

        // 各書籍のレビューは2〜4件の範囲に収まる（件数固定を前提にしない・発注書§2-3）。
        $perBook = DB::table('reviews')
            ->select('book_id', DB::raw('COUNT(*) as c'))
            ->groupBy('book_id')
            ->pluck('c', 'book_id');

        $this->assertCount(11, $perBook); // 全書籍にレビューが1件以上付く
        foreach ($perBook as $count) {
            $this->assertGreaterThanOrEqual(2, $count);
            $this->assertLessThanOrEqual(4, $count);
        }

        // 総数は 11冊 × 2〜4件 = 22〜44 の範囲に収まる。
        $total = DB::table('reviews')->count();
        $this->assertGreaterThanOrEqual(22, $total);
        $this->assertLessThanOrEqual(44, $total);
    }

    /** F-P12: 2回実行しても重複しない（firstOrCreate） */
    public function test_seed_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, DB::table('users')->count());
        $this->assertSame(10, DB::table('genres')->count());
        $this->assertSame(11, DB::table('books')->count());
    }
}
