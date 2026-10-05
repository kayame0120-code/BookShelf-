<?php

namespace App\Models;

use App\Enums\ReadingPlanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'target_date',
        'status',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => ReadingPlanStatus::class,
        'target_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * 読書計画を立てた会員を取得する。
     *
     * @return BelongsTo<User, ReadingPlan>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 読書計画の対象の書籍を、削除済みも含めて取得する。
     *
     * @return BelongsTo<Book, ReadingPlan>
     */
    public function book(): BelongsTo
    {
        // 論理削除済みの書籍に紐づく読書計画も一覧・編集で表示するため（CLAUDE.md §9-1）
        return $this->belongsTo(Book::class)->withTrashed();
    }
}
