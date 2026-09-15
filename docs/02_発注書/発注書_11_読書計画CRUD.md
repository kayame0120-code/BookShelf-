status: draft

# 発注書 11 — 読書計画CRUD＋自動失効バッチ

**対象走行**: 走行⑪（第6週）
**出典**: 要件シート.xlsx シート3 R33・R34・R36（★Schedule+ConsoleCommand・★DB Transaction・★PHP Enum）／シート7 R57〜R66（★読書計画一覧〜編集の各画面）／シート8 R19（★読書計画作成・編集バリデーション）／シート9 R13（★ReadingPlanSeeder）／シート10 R28・R29・R31（★読書計画／★読書計画（期限変更）／★自動失効バッチ テスト観点）／シート11 DR09（★読書計画データ要件）／シート12（reading_plansテーブル） ／ Bladeモック `Preparedblade-mockcase-BookShelf` advancedブランチ実測（`reading-plans/{index,create,edit}.blade.php` の変数契約、面談準備資料「advanced Blade契約サマリ」PG14〜PG16）
**対の検品表**: `docs/03_検品表/11_読書計画CRUD.md`
**前提**: 走行①〜⑩が合格・確定済み。`layouts/navigation.blade.php` は発注書12まで意図的に未移入のため、本画面群はヘッダーのナビゲーションメニューにまだ表示されない。動作確認は `/reading-plans` への直接URLアクセスで行う。通知機能（発注書12）はまだ存在しないため、本走行の自動失効バッチは状態更新のみを行い、通知の発火は行わない。

---

## 0. スコープ

### やること

1. `Bladeモック参照.md` の手順でadvancedブランチをcloneし、`resources/views/reading-plans/index.blade.php`・`create.blade.php`・`edit.blade.php` の3ファイルをコピーする。
2. `reading_plans` テーブルのマイグレーション作成・実行。
3. `App\Enums\ReadingPlanStatus` Enumの作成。
4. `ReadingPlan` モデルの作成（`User`・`Book` へのリレーション、`status` のEnumキャスト）。
5. `User` モデルに `readingPlans(): HasMany` を追加。
6. `ReadingPlanPolicy` の作成。
7. 読書計画の作成・更新用FormRequestの作成（重複制御・更新対象の限定を含む）。
8. `ReadingPlanController` の作成（`index`・`create`・`store`・`edit`・`update`・`complete`・`destroy` の7メソッド）。
9. ルーティング7本の追加。
10. `ExpireReadingPlans` コマンドの作成と、毎日0:00の実行スケジュール登録。
11. `ReadingPlanSeeder`（★版、6件シナリオ）の作成と `DatabaseSeeder` への登録。
12. シーダー追加を反映するための `sail artisan migrate:fresh --seed` の再実行。

### やらないこと

1. 通知機能（`Notification`、7:00のお知らせ送信バッチ）の実装。発注書12で扱う。本走行の自動失効バッチ（0:00）は状態更新のみを行い、通知は一切送信しない。
2. `layouts/navigation.blade.php` の移入。発注書12まで行わない。
3. 自動テストコード（PHPUnit）の作成。走行⑭で一括して書く。

---

## 1. マイグレーション

```php
Schema::create('reading_plans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->restrictOnDelete();
    $table->foreignId('book_id')->constrained()->cascadeOnDelete();
    $table->date('target_date');
    $table->string('status', 20);
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
```

---

## 2. `ReadingPlanStatus` Enum

```php
enum ReadingPlanStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => '進行中',
            self::Completed => '完了',
            self::Expired => '期限切れ',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::InProgress => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Expired => 'bg-red-100 text-red-800',
        };
    }
}
```

`cases()` の並び順（in_progress → completed → expired）は上記の宣言順のままでよい。読書計画一覧の状態絞り込みプルダウンはこの順で選択肢を生成する。

---

## 3. `ReadingPlan` モデル

```php
class ReadingPlan extends Model
{
    protected function casts(): array
    {
        return ['status' => ReadingPlanStatus::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
```

`Book` モデルには `readingPlans` リレーションを追加しない（書籍側から計画をたどる集計は本機能に存在しないため）。`User` モデルには以下を追加する。

```php
public function readingPlans(): HasMany
{
    return $this->hasMany(ReadingPlan::class);
}
```

---

## 4. `ReadingPlanPolicy`

```php
class ReadingPlanPolicy
{
    public function update(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id;
    }

    public function delete(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id;
    }

    public function complete(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id
            && $plan->status !== ReadingPlanStatus::Completed;
    }
}
```

**決定台帳D2参照**: `complete` に完了済み除外を含める。完了済みの計画に対して直接URLで `complete` を叩くと403になる。一覧画面では完了済みの計画にボタン自体を表示しないため、この防御は直接アクセス対策として機能する。

---

## 5. FormRequest

### 5-1. 作成（`ReadingPlanStoreRequest`）

```php
public function rules(): array
{
    return [
        'book_id' => ['required', 'exists:books,id'],
        'target_date' => ['required', 'date', 'after_or_equal:today'],
    ];
}

public function messages(): array
{
    return [
        'book_id.required' => '書籍を選択してください',
        'book_id.exists' => '選択された書籍が存在しません',
        'target_date.required' => '期日を入力してください',
        'target_date.after_or_equal' => '期日は本日以降の日付で指定してください',
    ];
}

public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator): void {
        $duplicate = ReadingPlan::where('user_id', Auth::id())
            ->where('book_id', $this->input('book_id'))
            ->where('status', ReadingPlanStatus::InProgress)
            ->exists();

        if ($duplicate) {
            $validator->errors()->add('book_id', 'この書籍は既に読書計画に登録されています');
        }
    });
}
```

重複制御は「対象ユーザー×book_idで進行中(`in_progress`)の既存レコードがあるか」のみを見る。完了・期限切れの計画は対象外（重複とみなさない）。

書籍プルダウン（`$books`）は論理削除済みの書籍を除外する（`Book::query()` に対して標準のSoftDelete除外がそのまま働くため、追加の `withTrashed()` 呼び出しはしない）。進行中の計画がある書籍もプルダウンからは除外しない。

### 5-2. 更新（`ReadingPlanUpdateRequest`）

```php
public function rules(): array
{
    return [
        'target_date' => ['required', 'date', 'after_or_equal:today'],
    ];
}
```

エラー文言は作成時の `target_date` と同一。`book_id`・`status` はフォームに入力欄がないため受け取らない。

---

## 6. `ReadingPlanController`

- `index`: `status` クエリで絞り込み。無効な値が指定された場合はどの状態にも一致せず0件表示になる（バリデーションエラーにはしない）。自分の計画のみを新しい順に取得する。
- `create`: `$books`（削除済みを除く書籍。id/title/author）をセレクト用に渡す。
- `store`: FormRequestでバリデーション後、`Auth::id()` を `user_id` として作成。成功時 `reading-plans.index` へ遷移、フラッシュ「読書計画を登録しました」。
- `edit`: `$this->authorize('update', $plan)`。`$readingPlan` をそのまま渡す。
- `update`: `$this->authorize('update', $plan)`。更新対象を `target_date` の1カラムに明示的に限定する（`$plan->update(['target_date' => $request->validated('target_date')])`）。他カラムは変更しない。成功時 `reading-plans.index` へ遷移、フラッシュ「読書計画を更新しました」。
- `complete`: `$this->authorize('complete', $plan)`。`status` を `Completed` に、`completed_at` を現在日時に更新。成功時 `reading-plans.index` へ遷移、フラッシュ「読書計画を完了しました」。
- `destroy`: `$this->authorize('delete', $plan)`。削除。成功時 `reading-plans.index` へ遷移、フラッシュ「読書計画を削除しました」。

`edit`・`update`・`complete`・`destroy` はいずれも「所有者本人以外は403」。この4件はHTTPメソッドごとに個別の動作として実装し、内部で共通処理に丸めても認可チェック自体は各アクションで個別に行う。

---

## 7. ルーティング

```php
Route::get('/reading-plans', [ReadingPlanController::class, 'index'])->name('reading-plans.index');
Route::get('/reading-plans/create', [ReadingPlanController::class, 'create'])->name('reading-plans.create');
Route::post('/reading-plans', [ReadingPlanController::class, 'store'])->name('reading-plans.store');
Route::get('/reading-plans/{plan}/edit', [ReadingPlanController::class, 'edit'])->name('reading-plans.edit');
Route::put('/reading-plans/{plan}', [ReadingPlanController::class, 'update'])->name('reading-plans.update');
Route::post('/reading-plans/{plan}/complete', [ReadingPlanController::class, 'complete'])->name('reading-plans.complete');
Route::delete('/reading-plans/{plan}', [ReadingPlanController::class, 'destroy'])->name('reading-plans.destroy');
```

7本のみ。`show` ルートは作らない（Bladeモックに詳細画面が存在しないため）。全ルートに `auth` ミドルウェアを適用する。

---

## 8. 自動失効バッチ（0:00）

**決定台帳D3参照**: コマンドクラス名は `ExpireReadingPlans`（シグネチャ `reading-plans:expire`）とする。

```php
class ExpireReadingPlans extends Command
{
    protected $signature = 'reading-plans:expire';

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
```

`routes/console.php`（またはコンソールカーネル）に以下を登録する。

```php
Schedule::command('reading-plans:expire')->dailyAt('00:00');
```

判定基準:

- 対象は `status` が `in_progress` かつ `target_date` が本日の3日前以前の計画。
- 境界: 2日前は対象外。当日・未来の日付は対象外。
- `completed` の計画は対象外（`completed_at` も書き換えない）。
- 既に `expired` の計画に再実行しても状態は変わらない（べき等）。
- `DB::transaction()` で一括更新を囲み、途中で例外が発生しても中途半端な更新を残さない。
- 対象0件でも異常終了しない。
- 個別レコードの失敗はログに記録し、他レコードの処理は継続する（`each` 内の `try-catch`）。

---

## 9. シーディング（★ReadingPlanSeeder）

`reading_plans` テーブルに6件投入する。`Carbon::today()` 起点で `target_date` を動的に設定し、採点者がいつ実行しても同じ挙動になるようにする。

- 山田太郎（ID1想定・主要シナリオ5件）:
  1. `target_date = Carbon::today()->addDays(3)` / `status = in_progress` → 3日前リマインダー対象
  2. `target_date = Carbon::today()` / `status = in_progress` → 当日リマインダー対象
  3. `target_date = Carbon::today()->subDays(3)` / `status = in_progress` → 本バッチでexpireへ変わる（かつ発注書12のリマインダーバッチでは3日後通知の対象にもなる二重シナリオ）
  4. `target_date = Carbon::today()->addDays(7)` / `status = in_progress` → リマインダー対象外
  5. `target_date = Carbon::today()->subDays(10)` / `status = completed` / `completed_at = Carbon::today()->subDays(5)` → 完了済み
- 鈴木花子（ID6想定・他ユーザー認可テスト用）:
  6. `target_date = Carbon::today()->addDays(5)` / `status = in_progress` → 山田太郎ログイン中に `/reading-plans/6/edit` 直打ちで403確認用

`book_id` は計画ごとに異なる書籍を割り当てる（同一ユーザー・進行中の重複制御に抵触しないようにする）。`create` を使用する。`DatabaseSeeder` の `run()` に、既存6Seederの後（`BookSeeder`・`UserSeeder` より後）に呼び出しを追加する。

---

## 10. 禁止事項

1. `reading-plans/index.blade.php`・`create.blade.php`・`edit.blade.php` 以外のBladeファイルを1文字も変更しない。
2. `layouts/navigation.blade.php` を本発注書では移入しない。
3. 通知（`Notification`）関連の実装を行わない。自動失効バッチは状態更新のみで、通知は送信しない。
4. 本発注書に明記されていないロジックを独自に追加しない。
5. `main` ブランチへ直接コミットしない。

---

## 11. 未定義事項に当たったとき

`QUESTIONS.md` へ以下4欄を記入し、該当作業を止めて次の作業に移る。

- 発生日 ／ 対象発注書（`02_発注書/11_読書計画CRUD.md`） ／ 止まった箇所 ／ CCの解釈候補（2案以上）

---

## 12. 完了時の報告

- 作業ブランチ名と最終コミットID
- `sail artisan route:list --path=reading-plans` の出力
- `sail bin pint --test` の出力
- `sail artisan migrate:fresh --seed` の出力（`reading_plans` に6件投入されることが分かる出力）
- `sail artisan schedule:list` の出力（`reading-plans:expire` が0:00に登録されていることが分かる出力）
- `QUESTIONS.md` に追記した行の有無
