<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    /** F-P11: db:seed後の件数 */
    public function test_seed_counts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, DB::table('users')->count());
        $this->assertSame(10, DB::table('genres')->count());
        $this->assertSame(11, DB::table('books')->count());
        $this->assertSame(32, DB::table('reviews')->count());
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
