<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * 採点者がいつ実行しても同じ挙動になるよう、target_date は Carbon::today() 起点で動的に設定する。
     */
    public function run(): void
    {
        // 山田太郎（主要シナリオ5件）と鈴木花子（他ユーザー認可テスト用1件）
        $yamada = User::where('email', 'yamada@example.com')->first();
        $suzuki = User::where('email', 'suzuki@example.com')->first();

        // 計画ごとに異なる書籍を割り当てる（進行中の重複制御に抵触しないようにする）
        $books = Book::orderBy('id')->take(6)->get();

        $plans = [
            // 1. 3日前リマインダー対象
            [
                'user_id' => $yamada->id,
                'book_id' => $books[0]->id,
                'target_date' => Carbon::today()->addDays(3),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            // 2. 当日リマインダー対象
            [
                'user_id' => $yamada->id,
                'book_id' => $books[1]->id,
                'target_date' => Carbon::today(),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            // 3. 本バッチで expired へ変わる（3日前）
            [
                'user_id' => $yamada->id,
                'book_id' => $books[2]->id,
                'target_date' => Carbon::today()->subDays(3),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            // 4. リマインダー対象外（7日後）
            [
                'user_id' => $yamada->id,
                'book_id' => $books[3]->id,
                'target_date' => Carbon::today()->addDays(7),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            // 5. 完了済み
            [
                'user_id' => $yamada->id,
                'book_id' => $books[4]->id,
                'target_date' => Carbon::today()->subDays(10),
                'status' => ReadingPlanStatus::Completed,
                'completed_at' => Carbon::today()->subDays(5),
            ],
            // 6. 鈴木花子（山田ログイン中の 403 確認用）
            [
                'user_id' => $suzuki->id,
                'book_id' => $books[5]->id,
                'target_date' => Carbon::today()->addDays(5),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
        ];

        foreach ($plans as $plan) {
            ReadingPlan::create($plan);
        }
    }
}
