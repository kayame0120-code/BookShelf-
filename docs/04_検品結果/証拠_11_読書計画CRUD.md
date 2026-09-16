# 証拠_11 — 読書計画CRUD＋自動失効バッチ（新版発注書・検品表 追従後）

本ファイルは CLAUDE.md §12-1 に従い、生の実行結果のみを記録する。判定語（YES/PASS/合格/正しく動作した/問題ない 等）は書かない。判定はチャット側が行う。
（`docs/04_検品結果/証拠フォーマット規則.md` は存在しないため CLAUDE.md §12-4 の5点に従った。）

## やったこと（事実のみ）
- 実装済みコード（`ExpireReadingPlans`・`ReadingPlanController`・`ReadingPlan`・`ReadingPlanStatus`・`ReadingPlanPolicy`・両FormRequest・`ReadingPlanSeeder`・`routes/web.php`・`app/Console/Kernel.php`・reading-plans 3 blade）を読み、検品表 A〜K 全46行の確認方法に沿って採取した。
- 実機（ブラウザ／HTTP）と実DB（MySQL）は docker/sail 停止のため到達不能。失効バッチ（I-2〜I-8, I-10, I-13）は、実 MySQL に触れずランタイムで sqlite in-memory 接続へ切り替え、実装の `reading-plans:expire` コマンドを `Carbon::setTestNow()` 固定日で実行して採取した。実行後は一時スクリプトを削除した（アプリ本体・DBは無改変）。

## 変更ファイル（git diff --stat：作業ツリー）
実行コマンド：
```
$ git diff --stat
```
出力：
```
 ...\273\266\343\202\267\343\203\274\343\203\210.xlsx" | Bin 3087933 -> 0 bytes
 1 file changed, 0 insertions(+), 0 deletions(-)
```
（変更は docs 配下の xlsx 1件の削除のみ。reading-plan 実装ソースの作業ツリー変更なし。`git status --short` は `.claude/` や docs 配下の未追跡ファイルのみを表示。）

---

## 証拠収集前に一度だけ実行するコマンド

### git branch --show-current
```
$ git branch --show-current
fix/11-reading-plan-doc-sync
```

### git log --oneline -5
```
$ git log --oneline -5
294d5f9 fix: 読書計画⑪の実装を新版発注書・検品表に追従
61388e0 docs: 応用フェーズ⑨〜⑬の検品証拠・再検品・正本乖離調査を記録
c8ecaca fix: show.blade を『削除済みバナー＋ISBN/出版日nullable表示』で確定（作業ツリー汚染の解消）
2dc339e refactor: 全既存Controller/Modelに型宣言を遡及適用 (発注書13)
9eb0d0d fix: three_days_after通知の対象をcompleted以外(in_progress+expired)に (発注書12/機能仕様§5)
```

### pint --test（sail 不可のため vendor/bin/pint --test を使用）
```
$ vendor/bin/pint --test
  ............................................................................
  ................................................

  ──────────────────────────────────────────────────────────────────── Laravel  
    PASS   ......................................................... 124 files  
```
（終了コード 0）

### route:list（sail 不可のため php artisan route:list を使用）
```
$ php artisan route:list --path=reading-plans

  GET|HEAD  reading-plans .. reading-plans.index › ReadingPlanController@index
  POST      reading-plans .. reading-plans.store › ReadingPlanController@store
  GET|HEAD  reading-plans/create reading-plans.create › ReadingPlanController…
  PUT       reading-plans/{plan} reading-plans.update › ReadingPlanController…
  DELETE    reading-plans/{plan} reading-plans.destroy › ReadingPlanControlle…
  POST      reading-plans/{plan}/complete reading-plans.complete › ReadingPla…
  GET|HEAD  reading-plans/{plan}/edit reading-plans.edit › ReadingPlanControl…

                                                            Showing [7] routes
```

### schedule:list（sail 不可のため php artisan schedule:list を使用）
```
$ php artisan schedule:list

  0 0 * * *  php artisan reading-plans:expire ................ Next Due: 11時間後
  0 7 * * *  php artisan reading-plans:send-reminders ........ Next Due: 18時間後
```

---

## A. 認可・未認証

### A-1 未ログインで各エンドポイントにアクセスすると /login へリダイレクト
確認方法＝実機。docker/sail 停止のため実HTTPは到達不能。**未確認（手段: sail/docker 起動＋ブラウザ or curl が必要）**。
静的採取のみ：全7ルートが `Route::middleware('auth')->group` 内に定義されている。
```
$ sed -n '69,78p' routes/web.php
// 読書計画（認証必須）
Route::middleware('auth')->group(function () {
    Route::get('/reading-plans', [ReadingPlanController::class, 'index'])->name('reading-plans.index');
    Route::get('/reading-plans/create', [ReadingPlanController::class, 'create'])->name('reading-plans.create');
    Route::post('/reading-plans', [ReadingPlanController::class, 'store'])->name('reading-plans.store');
    Route::get('/reading-plans/{plan}/edit', [ReadingPlanController::class, 'edit'])->name('reading-plans.edit');
    Route::put('/reading-plans/{plan}', [ReadingPlanController::class, 'update'])->name('reading-plans.update');
    Route::post('/reading-plans/{plan}/complete', [ReadingPlanController::class, 'complete'])->name('reading-plans.complete');
    Route::delete('/reading-plans/{plan}', [ReadingPlanController::class, 'destroy'])->name('reading-plans.destroy');
});
```

---

## B. 一覧・絞り込み

### B-1 一覧に自分の計画のみ表示
確認方法＝実機（2ユーザー比較）。**未確認（手段: sail/docker 起動＋実DB＋2ユーザーログインが必要）**。
静的採取：`ReadingPlanController::index` が `where('user_id', Auth::id())` で自ユーザーに限定。
```
$ sed -n '20,31p' app/Http/Controllers/ReadingPlanController.php
    public function index(Request $request): View
    {
        $currentStatus = $request->query('status');

        $readingPlans = ReadingPlan::where('user_id', Auth::id())
            ->with('book')
            ->when(filled($currentStatus), fn ($query) => $query->where('status', $currentStatus))
            ->latest()
            ->get();

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }
```

### B-2 0件のとき「該当する読書計画はありません。」
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：index.blade.php の空表示分岐。
```
$ sed -n '35,36p' resources/views/reading-plans/index.blade.php
                    @if($readingPlans->isEmpty())
                        <p class="text-gray-500">該当する読書計画はありません。</p>
```

### B-3 status=in_progress / completed / expired で当該状態のみ
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：B-1 の `->when(filled($currentStatus), fn ($query) => $query->where('status', $currentStatus))`（上記引用）。

### B-4 無指定ではすべての状態が表示
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：B-1 引用のとおり `filled($currentStatus)` が false のとき `where('status', ...)` は付与されない。

### B-5 status に未定義値を渡すとバリデーションエラーにならず0件表示
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：index に status のバリデーション定義はなく、`where('status', '未定義値')` が付くのみ（B-1 引用のとおり）。

---

## C. 作成

### C-1 書籍と期日を入力して登録 → index 遷移＋「読書計画を登録しました」
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：`store` のリダイレクト先とフラッシュ文言。
```
$ sed -n '46,57p' app/Http/Controllers/ReadingPlanController.php
    public function store(ReadingPlanStoreRequest $request): RedirectResponse
    {
        ReadingPlan::create([
            'user_id' => Auth::id(),
            'book_id' => $request->validated('book_id'),
            'target_date' => $request->validated('target_date'),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を登録しました');
    }
```

### C-2 書籍未選択・期日未入力・過去日の3ケースで back()＋$errors
確認方法＝実機（3ケース個別）。**未確認（手段: sail/docker＋実DB＋実HTTP が必要）**。
静的採取：`ReadingPlanStoreRequest::rules()` / `messages()`。
```
$ sed -n '26,47p' app/Http/Requests/ReadingPlanStoreRequest.php
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
```

---

## D. 重複制御

### D-1 同一ユーザー・同一書籍・進行中の2件目 → book_id に「この書籍は既に読書計画に登録されています」
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：`withValidator`。
```
$ sed -n '52,64p' app/Http/Requests/ReadingPlanStoreRequest.php
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

### D-2 既存が完了/期限切れなら同一書籍でも新規作成できる
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：D-1 引用の重複判定は `status = InProgress` のみを対象にしている。

### D-3 別ユーザーが同一書籍で進行中でも自分の新規作成は成功
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：D-1 引用の重複判定は `where('user_id', Auth::id())` で自ユーザーに限定している。

---

## E. 読了操作

### E-1 「読了する」で完了＋completed_at 記録 → index 遷移＋「読書計画を完了しました」
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：`complete`。
```
$ sed -n '85,96p' app/Http/Controllers/ReadingPlanController.php
    public function complete(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('complete', $plan);

        $plan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を完了しました');
    }
```

### E-2 完了の計画には「読了する」「編集」非表示、削除のみ
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：index.blade.php の操作列。
```
$ sed -n '67,79p' resources/views/reading-plans/index.blade.php
                                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                            @if($plan->status !== \App\Enums\ReadingPlanStatus::Completed)
                                                <form action="{{ route('reading-plans.complete', $plan) }}" method="POST" class="inline" novalidate>
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900">読了する</button>
                                                </form>
                                                <a href="{{ route('reading-plans.edit', $plan) }}" class="text-indigo-600 hover:text-indigo-900">編集</a>
                                            @endif
                                            <form action="{{ route('reading-plans.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('本当に削除しますか？');" novalidate>
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
```

### E-3 完了済みに直接 POST complete → 403
確認方法＝実機（直接リクエスト）。**未確認（手段: sail/docker＋実HTTP が必要）**。
静的採取：`ReadingPlanPolicy::complete` に完了済み除外。
```
$ sed -n '30,34p' app/Policies/ReadingPlanPolicy.php
    public function complete(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id
            && $plan->status !== ReadingPlanStatus::Completed;
    }
```

### E-4 期限切れでも「読了する」「編集」操作でき完了へ遷移
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：E-2 の分岐は `status !== Completed` のみを非表示条件にしており、Expired は該当しない（上記 E-2 引用）。`complete` Policy は Completed 以外を許可（E-3 引用）。

---

## F. 編集

### F-1 編集初期表示：書籍タイトルと現在状態が表示専用、期日欄に既存 target_date が Y-m-d
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：edit.blade.php。
```
$ sed -n '12,26p' resources/views/reading-plans/edit.blade.php
                    <div class="mb-4">
                        <p class="text-sm text-gray-700">対象書籍: <strong>{{ $readingPlan->book->title }}</strong></p>
                        <p class="text-sm text-gray-700">現在の状態:
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $readingPlan->status->badgeClass() }}">
                                {{ $readingPlan->status->label() }}
                            </span>
                        </p>
                    </div>

                    <form action="{{ route('reading-plans.update', $readingPlan) }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="target_date" class="block text-sm font-medium text-gray-700">期日 <span class="text-red-500">*</span></label>
                            <input type="date" name="target_date" id="target_date" value="{{ old('target_date', $readingPlan->target_date->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
```

### F-2 期日変更で更新 → index 遷移＋「読書計画を更新しました」
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：`update`。
```
$ sed -n '72,80p' app/Http/Controllers/ReadingPlanController.php
    public function update(ReadingPlanUpdateRequest $request, ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $plan->update(['target_date' => $request->validated('target_date')]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を更新しました');
    }
```

### F-3 book_id/status を混ぜても target_date 以外は変更されない
確認方法＝実機（直接リクエスト）。**未確認（手段: sail/docker＋実HTTP が必要）**。
静的採取：`update` は `$plan->update(['target_date' => $request->validated('target_date')])`（F-2 引用）。`ReadingPlanUpdateRequest::rules()` は target_date のみ。
```
$ sed -n '22,27p' app/Http/Requests/ReadingPlanUpdateRequest.php
    public function rules(): array
    {
        return [
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
```

### F-4 期日未入力・過去日の2ケースで back()＋$errors
確認方法＝実機（2ケース個別）。**未確認（手段: sail/docker＋実HTTP が必要）**。
静的採取：`ReadingPlanUpdateRequest::rules()`/`messages()`（F-3 引用＋下記）。
```
$ sed -n '34,40p' app/Http/Requests/ReadingPlanUpdateRequest.php
    public function messages(): array
    {
        return [
            'target_date.required' => '期日を入力してください',
            'target_date.after_or_equal' => '期日は本日以降の日付で指定してください',
        ];
    }
```

### F-5 完了の計画は一覧に編集ボタン非表示
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：E-2 引用の分岐（`@if($plan->status !== ...Completed)` 内に編集リンク）。

### F-6 期限切れの計画は期日変更でき、変更後も期限切れのまま維持
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：`update` は target_date のみ更新し status に触れない（F-2 引用）。

---

## G. 削除・認可の詳細

### G-1 「削除」で削除 → index 遷移＋「読書計画を削除しました」
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：`destroy`。
```
$ sed -n '101,109p' app/Http/Controllers/ReadingPlanController.php
    public function destroy(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を削除しました');
    }
```

### G-2 他ユーザーの GET edit / PUT update / POST complete / DELETE destroy が全て403（4件個別）
確認方法＝実機（4件個別）。**未確認（手段: sail/docker＋実HTTP が必要）**。
静的採取：各アクション先頭の authorize 呼び出し（edit/update=`update`、complete=`complete`、destroy=`delete`）。
```
$ grep -n "authorize(" app/Http/Controllers/ReadingPlanController.php
64:        $this->authorize('update', $plan);
74:        $this->authorize('update', $plan);
87:        $this->authorize('complete', $plan);
103:        $this->authorize('delete', $plan);
```
```
$ sed -n '14,25p' app/Policies/ReadingPlanPolicy.php
    public function update(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id;
    }

    public function delete(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id;
    }
```

---

## H. 書籍プルダウン・削除済み書籍

### H-1 作成画面の書籍選択肢に論理削除済み書籍が含まれない
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：`create` の書籍取得（`Book::orderBy('title')->get()`、SoftDelete 標準除外がそのまま働く／`withTrashed()` 呼び出しなし）。
```
$ sed -n '36,41p' app/Http/Controllers/ReadingPlanController.php
    public function create(): View
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }
```

### H-2 計画作成後に対象書籍が論理削除されても一覧でタイトル表示・画面が落ちない
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：`ReadingPlan::book()` の withTrashed。
```
$ sed -n '38,42p' app/Models/ReadingPlan.php
    public function book(): BelongsTo
    {
        // 論理削除済みの書籍に紐づく読書計画も一覧・編集で表示するため（CLAUDE.md §9-1）
        return $this->belongsTo(Book::class)->withTrashed();
    }
```

---

## I. 自動失効バッチ

### I-1 状態更新処理が毎日0:00にスケジュール定義され、お知らせ送信処理とは別スケジュール
確認方法＝schedule:list 出力。
```
$ php artisan schedule:list

  0 0 * * *  php artisan reading-plans:expire ................ Next Due: 11時間後
  0 7 * * *  php artisan reading-plans:send-reminders ........ Next Due: 18時間後
```
登録元：
```
$ sed -n '13,17p' app/Console/Kernel.php
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('reading-plans:expire')->dailyAt('00:00');
        $schedule->command('reading-plans:send-reminders')->dailyAt('07:00');
    }
```

### I-2〜I-8 バッチ挙動（境界・完了除外・冪等・全ユーザー）
確認方法＝実機／テスト実行。実 MySQL は docker 停止のため到達不能。実装の `reading-plans:expire` コマンドを、実 MySQL に触れずランタイム sqlite in-memory 接続へ切替え、`Carbon::setTestNow('2026-09-16 09:00:00')` 固定で実行して採取した（一時スクリプトは実行後削除。アプリ本体・実DBは無改変）。
実装の where 条件（採取対象のコマンド本体）：
```
$ sed -n '31,47p' app/Console/Commands/ExpireReadingPlans.php
    public function handle(): int
    {
        DB::transaction(function (): void {
            ReadingPlan::where('status', ReadingPlanStatus::InProgress)
                ->where('target_date', Carbon::today()->subDays(3))
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
```
sqlite 隔離実行の生出力：
```
=== BEFORE (today=2026-09-16) ===
I2_3daysago_inprog           target=2026-09-13 status=in_progress completed_at=null
I3_2daysago_inprog           target=2026-09-14 status=in_progress completed_at=null
I4_4daysago_inprog           target=2026-09-12 status=in_progress completed_at=null
I5_today_inprog              target=2026-09-16 status=in_progress completed_at=null
I5_future_inprog             target=2026-09-21 status=in_progress completed_at=null
I6_3daysago_completed        target=2026-09-13 status=completed completed_at=2026-09-15 00:00:00
I7_3daysago_expired          target=2026-09-13 status=expired completed_at=null
I8_otheruser_3daysago        target=2026-09-13 status=in_progress completed_at=null

=== RUN reading-plans:expire (1st) exit=0 ===

=== AFTER 1st run ===
I2_3daysago_inprog           target=2026-09-13 status=expired completed_at=null
I3_2daysago_inprog           target=2026-09-14 status=in_progress completed_at=null
I4_4daysago_inprog           target=2026-09-12 status=in_progress completed_at=null
I5_today_inprog              target=2026-09-16 status=in_progress completed_at=null
I5_future_inprog             target=2026-09-21 status=in_progress completed_at=null
I6_3daysago_completed        target=2026-09-13 status=completed completed_at=2026-09-15 00:00:00
I7_3daysago_expired          target=2026-09-13 status=expired completed_at=null
I8_otheruser_3daysago        target=2026-09-13 status=expired completed_at=null

=== RUN reading-plans:expire (2nd, idempotency I-7) exit=0 ===
I2_3daysago_inprog           target=2026-09-13 status=expired completed_at=null
I3_2daysago_inprog           target=2026-09-14 status=in_progress completed_at=null
I4_4daysago_inprog           target=2026-09-12 status=in_progress completed_at=null
I5_today_inprog              target=2026-09-16 status=in_progress completed_at=null
I5_future_inprog             target=2026-09-21 status=in_progress completed_at=null
I6_3daysago_completed        target=2026-09-13 status=completed completed_at=2026-09-15 00:00:00
I7_3daysago_expired          target=2026-09-13 status=expired completed_at=null
I8_otheruser_3daysago        target=2026-09-13 status=expired completed_at=null
```
対応：
- I-2（3日前 in_progress → expired）: `I2_3daysago_inprog` 1st run 後 status=expired。
- I-3（2日前は不変）: `I3_2daysago_inprog` in_progress のまま。
- I-4（4日前は不変）: `I4_4daysago_inprog` in_progress のまま。
- I-5（当日・未来は不変）: `I5_today_inprog`・`I5_future_inprog` in_progress のまま。
- I-6（3日前 completed は不変・completed_at 不変）: `I6_3daysago_completed` status=completed / completed_at=2026-09-15 00:00:00 のまま。
- I-7（既 expired に再実行で不変）: 2nd run 後も全行 1st run と同一。
- I-8（全ユーザー対象）: user_id=2 の `I8_otheruser_3daysago` も expired に変化。

### I-9 期限切れ計画も編集・読了・削除でき UI上でブロックされない
確認方法＝実機。**未確認（手段: sail/docker＋実DB が必要）**。
静的採取：index.blade.php の操作分岐は `status !== Completed`（E-2 引用）で、Expired には読了・編集・削除いずれのボタンも表示される。Policy update/delete は所有者判定のみ（G-2 引用）、complete は Completed 以外許可（E-3 引用）。

### I-10 該当0件でもバッチ実行が異常終了しない
確認方法＝コマンド実行結果。sqlite 隔離（空テーブル）で実行した生出力：
```
=== I-10: table empty, row count=0 ===
=== reading-plans:expire exit code = 0 ===
=== row count after = 0 ===
```

### I-11 1件失敗しても残りが継続し失敗がログ記録
確認方法＝ソースコード確認＋ログ出力。
静的採取：`each` 内の try-catch と `Log::error`（I-2〜I-8 のコマンド本体引用 38〜42行）。
```
$ sed -n '37,43p' app/Console/Commands/ExpireReadingPlans.php
                ->each(function (ReadingPlan $plan): void {
                    try {
                        $plan->update(['status' => ReadingPlanStatus::Expired]);
                    } catch (\Throwable $e) {
                        Log::error("読書計画#{$plan->id}の失効処理に失敗しました: {$e->getMessage()}");
                    }
                });
```
（実ログ出力の採取は例外注入が必要。**ログ実出力は未確認（手段: 失敗注入＋実行環境が必要）**。）

### I-12 一括更新が DB::transaction() で囲まれている
確認方法＝ソースコード確認。
```
$ sed -n '33,44p' app/Console/Commands/ExpireReadingPlans.php
        DB::transaction(function (): void {
            ReadingPlan::where('status', ReadingPlanStatus::InProgress)
                ->where('target_date', Carbon::today()->subDays(3))
                ->get()
                ->each(function (ReadingPlan $plan): void {
                    try {
                        $plan->update(['status' => ReadingPlanStatus::Expired]);
                    } catch (\Throwable $e) {
                        Log::error("読書計画#{$plan->id}の失効処理に失敗しました: {$e->getMessage()}");
                    }
                });
        });
```

### I-13 ★ReadingPlanSeeder 投入後にバッチ実行で target_date=本日3日前の計画が expired へ
確認方法＝実機／テスト実行。実 MySQL への seeder 投入は docker 停止のため到達不能。シーダーの計画#3 と同一条件（`target_date = Carbon::today()->subDays(3)`, status=in_progress）の単一行を sqlite 隔離で作り、実装コマンドを実行した生出力：
```
=== I-13 seeder plan#3 parity: before status=in_progress target=2026-09-13 ===
=== I-13 after: status=expired ===
```
シーダー計画#3 の定義（対象条件の根拠）：
```
$ sed -n '45,52p' database/seeders/ReadingPlanSeeder.php
            // 3. 本バッチで expired へ変わる（3日前）
            [
                'user_id' => $yamada->id,
                'book_id' => $books[2]->id,
                'target_date' => Carbon::today()->subDays(3),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
```
（実 MySQL＋実 `migrate:fresh --seed` での 6件投入確認は **未確認（手段: sail/docker 起動が必要）**。）

---

## J. コード品質・スコープ厳守

### J-1 ReadingPlanController・ExpireReadingPlans の全メソッドに型宣言
```
$ grep -n "public function\|protected function\|private function" app/Http/Controllers/ReadingPlanController.php app/Console/Commands/ExpireReadingPlans.php
app/Http/Controllers/ReadingPlanController.php:20:    public function index(Request $request): View
app/Http/Controllers/ReadingPlanController.php:36:    public function create(): View
app/Http/Controllers/ReadingPlanController.php:46:    public function store(ReadingPlanStoreRequest $request): RedirectResponse
app/Http/Controllers/ReadingPlanController.php:62:    public function edit(ReadingPlan $plan): View
app/Http/Controllers/ReadingPlanController.php:72:    public function update(ReadingPlanUpdateRequest $request, ReadingPlan $plan): RedirectResponse
app/Http/Controllers/ReadingPlanController.php:85:    public function complete(ReadingPlan $plan): RedirectResponse
app/Http/Controllers/ReadingPlanController.php:101:    public function destroy(ReadingPlan $plan): RedirectResponse
app/Console/Commands/ExpireReadingPlans.php:31:    public function handle(): int
```

### J-2 ReadingPlan::book() に ->withTrashed()
（H-2 引用のとおり）
```
$ grep -n "withTrashed" app/Models/ReadingPlan.php
41:        return $this->belongsTo(Book::class)->withTrashed();
```

### J-3 reading-plans 3 blade 以外の Blade が変更されていない
確認方法＝git diff 出力。走行⑪のコミットは c9dcf13（原実装）・2fe2f65（withTrashed）・294d5f9（新版追従）の3本。各コミットが触れた blade を列挙。
```
$ git show --name-only --format="" c9dcf13 | grep resources/views
resources/views/reading-plans/create.blade.php
resources/views/reading-plans/edit.blade.php
resources/views/reading-plans/index.blade.php
```
```
$ git show --name-only --format="" 2fe2f65 | grep resources/views
（出力なし）
```
```
$ git show --name-only --format="" 294d5f9 | grep resources/views
（出力なし）
```
参考（本ブランチ全体は 発注書12/13 のコミットも積んでいるため main 比較には ⑪外の blade が含まれる）：
```
$ git diff main --name-only -- resources/
resources/views/books/show.blade.php
resources/views/layouts/navigation.blade.php
resources/views/notifications/index.blade.php
resources/views/reading-plans/create.blade.php
resources/views/reading-plans/edit.blade.php
resources/views/reading-plans/index.blade.php
resources/views/reports/index.blade.php
```
```
$ git log --oneline main..HEAD
294d5f9 fix: 読書計画⑪の実装を新版発注書・検品表に追従
61388e0 docs: 応用フェーズ⑨〜⑬の検品証拠・再検品・正本乖離調査を記録
c8ecaca fix: show.blade を『削除済みバナー＋ISBN/出版日nullable表示』で確定（作業ツリー汚染の解消）
2dc339e refactor: 全既存Controller/Modelに型宣言を遡及適用 (発注書13)
9eb0d0d fix: three_days_after通知の対象をcompleted以外(in_progress+expired)に (発注書12/機能仕様§5)
78e739c feat: リマインダー通知＋7:00日次バッチ＋ナビ移入を実装 (発注書12)
2fe2f65 fix: ReadingPlan::book()にwithTrashedを追加し削除済み書籍を一覧表示 (発注書11/CLAUDE.md §9-1)
c9dcf13 feat: 読書計画CRUD＋自動失効バッチを実装 (発注書11)
949ea1a feat: 公開API書き込み系にSanctumトークン認証を追加 (発注書10)
38de136 feat: マイ読書レポート(/reports)と★シーダー更新を実装 (発注書09)
...
```

### J-4 layouts/navigation.blade.php が変更・追加されていない（走行⑪基準）
確認方法＝git diff 出力。navigation.blade.php を触れたコミット一覧と、走行⑪3コミットでの grep 結果。
```
$ git log --oneline -- resources/views/layouts/navigation.blade.php
78e739c feat: リマインダー通知＋7:00日次バッチ＋ナビ移入を実装 (発注書12)
6a346be chore: 走行①のBladeモック・docs・CLAUDE.md等を確定
```
```
$ for c in c9dcf13 2fe2f65 294d5f9; do git show --name-only --format="" $c | grep -c navigation.blade.php; done
0
0
0
```
（参考：main 比較には 78e739c=発注書12 由来の navigation 変更が含まれる。上記 J-3 の `git diff main --name-only -- resources/` に navigation.blade.php が現れるのはこのため。走行⑪の3コミットは navigation.blade.php を一切変更していない。）

### J-5 発注書に明記のない独自ロジックが追加されていない
確認方法＝ソースコード確認。走行⑪の各実装ファイルを全読了し、発注書 §1〜§9 の記述と対応させた。
- `ExpireReadingPlans::handle`：発注書 §8 の literal（`where(status, InProgress)->where(target_date, Carbon::today()->subDays(3))`＋DB::transaction＋each try-catch＋Log::error）と一致（I-2〜I-8/I-11/I-12 引用）。追加の where 条件・独自クロージャなし。
- `ReadingPlanController`：index の `when(filled($currentStatus), ...)`＝発注書 §6 の literal。create の `Book::orderBy('title')->get()`＝発注書 §257 の literal。store/update/complete/destroy＝発注書 §258〜262 の指定どおり。status の事前バリデーション等の追加なし（B-5 引用）。
```
$ grep -n "Validator\|validate(\|abort(\|Gate::\|when(\|filter(\|where(" app/Http/Controllers/ReadingPlanController.php
26:            ->when(filled($currentStatus), fn ($query) => $query->where('status', $currentStatus))
24:        $readingPlans = ReadingPlan::where('user_id', Auth::id())
```
- FormRequest：Store は §5-1 literal（rules/messages/withValidator）、Update は §5-2 literal（target_date のみ）。（C-2/D-1/F-3/F-4 引用）
- Policy：§4 literal（update/delete/complete の3メソッドのみ、viewAny/view/create 未定義）。（G-2/E-3 引用）
```
$ grep -n "public function" app/Policies/ReadingPlanPolicy.php
14:    public function update(User $user, ReadingPlan $plan): bool
20:    public function delete(User $user, ReadingPlan $plan): bool
30:    public function complete(User $user, ReadingPlan $plan): bool
```
- Model：§3 literal（fillable/casts/user/book、Book 側 readingPlans なし）。User に `readingPlans(): HasMany` 追加（§3 指定）。
```
$ grep -n "readingPlans\|public function" app/Models/User.php | sed -n '1,20p'
50:    public function books(): HasMany
55:    public function reviews(): HasMany
70:    public function readingPlans(): HasMany
```
（Enum `ReadingPlanStatus` に §2 指定どおり label()/badgeClass() のみ。追加ケース・追加メソッドなし。）

### J-6 通知（Notification）関連の実装が本走行に含まれていない
確認方法＝ソースコード確認。走行⑪の実装ファイル群に Notification/notify 参照がない。
```
$ grep -rln "Notification\|->notify(" app/Console/Commands/ExpireReadingPlans.php app/Http/Controllers/ReadingPlanController.php app/Models/ReadingPlan.php app/Policies/ReadingPlanPolicy.php app/Http/Requests/ReadingPlanStoreRequest.php app/Http/Requests/ReadingPlanUpdateRequest.php database/seeders/ReadingPlanSeeder.php
（出力なし）
```
（本ブランチ全体には 発注書12 の Notification 実装が別コミット 78e739c 以降で存在するが、走行⑪の3コミット c9dcf13/2fe2f65/294d5f9 のファイル一覧に Notification 関連は含まれない。）

---

## K. 走行完了条件

### K-1 QUESTIONS.md に走行⑪で追記された行の有無（裁定・解消状況）
確認方法＝QUESTIONS.md の内容。
```
$ grep -n "11_読書計画" QUESTIONS.md
14:| 2026-09-15 | 11_読書計画CRUD（§3 ReadingPlanモデル） | ... | (a) ...（今回採用...） (b) ... |
15:| 2026-09-15 | 11_読書計画CRUD（§9 ReadingPlanSeeder） | ... | (a) ...（今回採用...） (b) ... |
```
14行目（§3 casts）：L10 では `casts()` メソッド形式が効かないため、標準 `protected $casts` プロパティ形式を採用した旨の記録（解決案(a)採用と併記）。
15行目（§9 Seeder）：6件目所有者「鈴木花子(ID6想定)」が UserSeeder では ID2 実在のため、氏名どおり suzuki@example.com=ID2 を割当てた旨の記録（解決案(a)採用と併記）。
（該当2行は「消されず」残存。行内に採用解決案が明記されている。裁定済み表記の有無の判断はチャット側。）
実装との整合の生証拠：
```
$ grep -n "protected \$casts" app/Models/ReadingPlan.php
27:    protected $casts = [
```
```
$ grep -n "suzuki@example.com\|->addDays(5)" database/seeders/ReadingPlanSeeder.php
23:        $suzuki = User::where('email', 'suzuki@example.com')->first();
73:                'target_date' => Carbon::today()->addDays(5),
```

### K-2 A〜J 全行の集計
本表による集計行。判定はチャット側。

---

## 未確認・保留（手段が無く採取できなかった行）

- A-1：実HTTP到達不能（sail/docker＋ブラウザ/curl 必要）。ルート定義の静的採取のみ。
- B-1〜B-5, C-1, C-2, D-1〜D-3, E-1〜E-4, F-1〜F-6, G-1, G-2, H-1, H-2：いずれも「確認方法＝実機」。docker/sail 停止で実DB・実HTTP到達不能。各行ソース静的採取のみ。
- I-9：実機未確認（ソース静的採取のみ）。
- I-11：try-catch/Log::error の静的採取は済。実際のログ出力（例外注入）は未採取。
- I-13：sqlite 隔離での seeder 計画#3 相当行の失効は採取済。実 MySQL＋`migrate:fresh --seed` の6件投入は未採取（docker 必要）。
- I-2〜I-8, I-10：実 MySQL では未実行。sqlite in-memory 隔離での実コマンド実行結果を採取（実 MySQL・実DBは無改変）。

## worktree情報
- ブランチ名：fix/11-reading-plan-doc-sync（最終コミット 294d5f9）
- 本体へのマージ：未（走行⑪の全行判定がチャット側で確定後にメインCCがマージ）
- 破壊的操作は不使用。sqlite in-memory 隔離テスト用の一時スクリプトは実行後に削除済み（アプリ本体・実DB・実マイグレーションへの変更なし）。

## 判定はしない
本ファイルは YES/NO・PASS/FAIL の判定を含まない。判定は片倉／チャット側がこの証拠を見て行う。
