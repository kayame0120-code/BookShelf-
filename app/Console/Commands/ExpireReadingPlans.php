<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireReadingPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reading-plans:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '期日を3日以上過ぎた進行中の読書計画を期限切れに更新する';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        DB::transaction(function (): void {
            ReadingPlan::where('status', ReadingPlanStatus::InProgress)
                ->where('target_date', '<=', Carbon::today()->subDays(3))
                ->get()
                ->each(function (ReadingPlan $plan): void {
                    try {
                        $plan->update(['status' => ReadingPlanStatus::Expired]);
                    } catch (\Throwable $e) {
                        Log::error("読書計画#{$plan->id}の失効処理に失敗しました: {$e->getMessage()}");
                    }
                });
        });

        return self::SUCCESS;
    }
}
