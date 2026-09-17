<?php

namespace Tests\Unit;

use App\Enums\ReadingPlanStatus;
use PHPUnit\Framework\TestCase;

/**
 * 走行⑪：ReadingPlanStatus Enum の表示ヘルパ（検品表⑭差し戻し 2）。
 * label()・badgeClass() が各caseで確定文言・確定クラスを返すことを確認する。
 */
class ReadingPlanStatusTest extends TestCase
{
    /** label() が各caseで正しい日本語ラベルを返す */
    public function test_label_for_each_case(): void
    {
        $this->assertSame('進行中', ReadingPlanStatus::InProgress->label());
        $this->assertSame('完了', ReadingPlanStatus::Completed->label());
        $this->assertSame('期限切れ', ReadingPlanStatus::Expired->label());
    }

    /** badgeClass() が各caseで正しいTailwindクラス文字列を返す */
    public function test_badge_class_for_each_case(): void
    {
        $this->assertSame('bg-blue-100 text-blue-800', ReadingPlanStatus::InProgress->badgeClass());
        $this->assertSame('bg-green-100 text-green-800', ReadingPlanStatus::Completed->badgeClass());
        $this->assertSame('bg-red-100 text-red-800', ReadingPlanStatus::Expired->badgeClass());
    }

    /** enumのvalue（DB格納値）が確定値である */
    public function test_enum_values(): void
    {
        $this->assertSame('in_progress', ReadingPlanStatus::InProgress->value);
        $this->assertSame('completed', ReadingPlanStatus::Completed->value);
        $this->assertSame('expired', ReadingPlanStatus::Expired->value);
    }
}
