# 機能仕様書 — reading_plans（読書計画）

| 項目 | 内容 |
|---|---|
| 対象発注書 | 11_読書計画CRUD（日次バッチによる自動失効は 12_通知バッチ と連動。notifications.md も参照） |
| 正本 | 要件シート.xlsx シート5/7/8/9/10/11/12 ＋ Bladeモック `advanced`（frozen） |
| 適用範囲 | 応用要件。読書計画の一覧・作成・編集・削除・読了操作、状態遷移、期限管理、認可、reading_plans テーブル、ReadingPlanSeeder、テスト観点 |
| 完成条件 | 本書だけで発注書11が書ける（他ファイル参照不要。ただし日次バッチの通知発火は notifications.md がバッチ本体を持つ） |

読書計画は、ログインユーザーが「どの書籍を、いつまでに読むか」を登録・管理する機能。計画は3つの状態（進行中・完了・期限切れ）を持ち、利用者の操作（読了）と日次バッチの検知（期限切れ）で状態が変わる。

---

## 0. スコープ

**含む**: 読書計画の一覧表示・状態絞り込み・作成・編集・削除・読了操作、`ReadingPlanStatus` Enum、重複制御と編集制限、`ReadingPlanPolicy`、reading_plans テーブル、ReadingPlanSeeder、以上のテスト観点。

**含まない**: 日次バッチのうち通知発火の実装（notifications.md）／通知テーブル・通知一覧画面（notifications.md）／書籍そのもののCRUD（books.md）／マイ読書レポート（reports.md）。

日次バッチは「状態更新（0:00）」と「お知らせ送信（7:00）」の2本立て。本書は**状態更新バッチ（進行中→期限切れ）**の仕様を持つ。お知らせ送信バッチと通知レコードの生成は notifications.md が持つ。両者は同じ計画レコードを対象にするため、条件の定義は本書 §7 を唯一の基準とする。

---

## 1. ルーティング

`routes/web.php`。すべて認証必須。カッコ内は Blade が `route()` で参照している名前で、変更不可。

| # | メソッド | URI | route名 | Controller@Action | 認証 | 認可 |
|---|---|---|---|---|---|---|
| 1 | GET | `/reading-plans` | `reading-plans.index` | `ReadingPlanController@index` | 必須 | — |
| 2 | GET | `/reading-plans/create` | `reading-plans.create` | `ReadingPlanController@create` | 必須 | — |
| 3 | POST | `/reading-plans` | `reading-plans.store` | `ReadingPlanController@store` | 必須 | — |
| 4 | GET | `/reading-plans/{plan}/edit` | `reading-plans.edit` | `ReadingPlanController@edit` | 必須 | `ReadingPlanPolicy::update` |
| 5 | PUT | `/reading-plans/{plan}` | `reading-plans.update` | `ReadingPlanController@update` | 必須 | `ReadingPlanPolicy::update` |
| 6 | DELETE | `/reading-plans/{plan}` | `reading-plans.destroy` | `ReadingPlanController@destroy` | 必須 | `ReadingPlanPolicy::delete` |
| 7 | POST | `/reading-plans/{plan}/complete` | `reading-plans.complete` | `ReadingPlanController@complete` | 必須 | `ReadingPlanPolicy::complete` |

### 定義コード

```php
Route::middleware('auth')->group(function () {
    Route::get('/reading-plans', [ReadingPlanController::class, 'index'])->name('reading-plans.index');
    Route::get('/reading-plans/create', [ReadingPlanController::class, 'create'])->name('reading-plans.create');
    Route::post('/reading-plans', [ReadingPlanController::class, 'store'])->name('reading-plans.store');
    Route::get('/reading-plans/{plan}/edit', [ReadingPlanController::class, 'edit'])->name('reading-plans.edit');
    Route::put('/reading-plans/{plan}', [ReadingPlanController::class, 'update'])->name('reading-plans.update');
    Route::delete('/reading-plans/{plan}', [ReadingPlanController::class, 'destroy'])->name('reading-plans.destroy');
    Route::post('/reading-plans/{plan}/complete', [ReadingPlanController::class, 'complete'])->name('reading-plans.complete');
});
```

- ルートパラメータ名は `{plan}` とし、Bladeが `route('reading-plans.edit', $plan)` の形で参照している名前に合わせる。ルートモデル紐付けの変数名も `$plan` にする。
- `show` ルートは存在しない（Bladeに読書計画の単一詳細画面がない）。作らない。
- `create` を `{plan}/edit` より前に定義する必要はない（`create` は静的パス、`{plan}` は数値IDで衝突しないが、可読性のため上記の順で並べる）。

---

## 2. 状態モデル（`App\Enums\ReadingPlanStatus`）

status カラムは PHP の backed enum で管理する。3つの状態を持つ。

| 値 | `label()` | `badgeClass()` | 意味 |
|---|---|---|---|
| `in_progress` | 進行中 | `bg-blue-100 text-blue-800` | 読んでいる最中。期日に向けて進行中 |
| `completed` | 完了 | `bg-green-100 text-green-800` | 読了操作が行われ、完了日時が記録された |
| `expired` | 期限切れ | `bg-red-100 text-red-800` | 期日から3日経過し、日次バッチが自動で切り替えた |

### Enum の実装

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
            self::Completed  => '完了',
            self::Expired    => '期限切れ',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::InProgress => 'bg-blue-100 text-blue-800',
            self::Completed  => 'bg-green-100 text-green-800',
            self::Expired    => 'bg-red-100 text-red-800',
        };
    }
}
```

- `cases()` の並び順は宣言順、すなわち `in_progress` → `completed` → `expired`。一覧画面の状態絞り込みプルダウンがこの順で選択肢を生成する。順序を変えない。
- `label()` と `badgeClass()` は Blade（`reading-plans/index.blade.php`・`edit.blade.php`）が直接呼ぶため、メソッド名・戻り値を上記のとおり実装する。

### 状態遷移

```
                     読了操作（complete）
  in_progress ─────────────────────────────► completed
      │                                          （終端。以後は削除のみ）
      │ 期日から3日経過を状態更新バッチが検知
      ▼
   expired ──────────────────────────────────► completed
                     読了操作（complete）
```

- `in_progress` → `completed`: 利用者が「読了する」を押す。即時遷移。`completed_at` に現在日時を記録する。
- `in_progress` → `expired`: 状態更新バッチ（毎日0:00）が「期日が本日の3日前」の計画を検知して自動遷移する（§7）。
- `expired` → `completed`: 期限切れの計画でも「読了する」「編集」は操作できる。読了で完了へ遷移する。
- `completed` は終端状態。完了した計画に対する状態変更・編集・読了操作は行えない（UI上ボタンを出さない。§4-2）。完了した計画は削除のみ可能。
- `completed` → `expired` の遷移は起きない。状態更新バッチは `in_progress` のみを対象にするため（§7）、完了済みの計画は期日を過ぎても完了のまま。

---

## 3. 画面契約（Bladeモック実測・改変禁止）

| 画面ID | Bladeファイル | 渡す変数 | Blade が要求する属性・メソッド | flash |
|---|---|---|---|---|
| PG15 | `reading-plans/index.blade.php` | `$readingPlans`（Collection）／`$currentStatus`（string または null） | `$plan->book->title`／`$plan->target_date->format('Y-m-d')`／`$plan->completed_at?->format('Y-m-d')`／`$plan->status`（Enum。`->label()` `->badgeClass()` `!== ReadingPlanStatus::Completed`）／`ReadingPlanStatus::cases()` | `session('success')` あり |
| PG16 | `reading-plans/create.blade.php` | `$books`（Collection） | `$book->id`／`$book->title`／`$book->author`／`old('book_id')`／`old('target_date')`／`@error('book_id')` `@error('target_date')` | なし（`@error` のみ） |
| PG17 | `reading-plans/edit.blade.php` | `$readingPlan`（Model） | `$readingPlan->book->title`／`$readingPlan->status`（`->label()` `->badgeClass()`）／`$readingPlan->target_date->format('Y-m-d')`／`old('target_date', ...)`／`@error('target_date')` | なし |

**一覧画面（PG15）の実測ポイント**

- 状態絞り込みは `<select name="status" onchange="this.form.submit()">`。GET フォームで再送信する。選択肢は「すべて」（value 空）＋ `ReadingPlanStatus::cases()` の各値。現在選択中の判定に `$currentStatus === $statusOption->value` を使う。
- 各行の操作列は、`$plan->status !== ReadingPlanStatus::Completed` のときだけ「読了する」（`reading-plans.complete` への POST フォーム）と「編集」（`reading-plans.edit` へのリンク）を表示する。「削除」（`reading-plans.destroy` への DELETE フォーム）は状態に関わらず常に表示する。
- 計画が0件のとき「該当する読書計画はありません。」を表示する。
- 完了日は `$plan->completed_at?->format('Y-m-d') ?? '-'`。未完了なら「-」。
- Bladeの操作フォームには `novalidate` が付いており、削除フォームには `onsubmit="return confirm(...)"` の確認ダイアログがある。これらは改変しない。

**変数名の対応**: 一覧は `$readingPlans` と `$currentStatus`、作成は `$books`、編集は `$readingPlan`。Bladeが使う変数名に合わせる（変えるとビューが変数未定義で落ちる）。

---

## 4. コントローラー仕様（`App\Http\Controllers\ReadingPlanController`）

すべて「リクエスト受付とレスポンス返却」に専念し、クエリは Eloquent のみで書く。

| アクション | 処理 |
|---|---|
| `index` | §4-1 の一覧・絞り込み仕様に従い `$readingPlans` と `$currentStatus` を `reading-plans.index` ビューへ渡す |
| `create` | 書籍プルダウン用に `$books = Book::orderBy('title')->get()` を `reading-plans.create` ビューへ渡す（§4-3） |
| `store` | `StoreReadingPlanRequest` で検証 → `ReadingPlan::create(book_id, user_id: Auth::id(), target_date, status: InProgress)` → `redirect()->route('reading-plans.index')->with('success', '読書計画を登録しました')` |
| `edit` | `$this->authorize('update', $plan)` → `$plan` を `$readingPlan` として `reading-plans.edit` ビューへ渡す |
| `update` | `$this->authorize('update', $plan)` → `UpdateReadingPlanRequest` で検証 → `$plan->update(['target_date' => $validated['target_date']])`（target_date のみ更新。§4-4） → `redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました')` |
| `destroy` | `$this->authorize('delete', $plan)` → `$plan->delete()` → `redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました')` |
| `complete` | `$this->authorize('complete', $plan)` → `$plan->update(['status' => ReadingPlanStatus::Completed, 'completed_at' => now()])` → `redirect()->route('reading-plans.index')->with('success', '読書計画を完了しました')` |

### 4-1. index の一覧・絞り込み仕様

```php
$currentStatus = $request->query('status');

$readingPlans = ReadingPlan::query()
    ->where('user_id', Auth::id())
    ->with('book')
    ->when(
        filled($currentStatus),
        fn ($q) => $q->where('status', $currentStatus)
    )
    ->latest()
    ->get();
```

- 一覧は**ログインユーザー自身の計画のみ**。`where('user_id', Auth::id())` を必ず付ける。他人の計画が混ざってはならない。
- 絞り込みは `status` クエリに値が入っているときだけ `where('status', $currentStatus)` を適用する。空文字・未指定のときは全件表示。**未定義の値が来た場合はバリデーションエラーにせず、どの状態にも一致しない条件として0件を返す**（要件シート シート7 確定）。有効値かどうかの事前判定は挟まない。判定を挟むと無効値のとき where が付かず全件表示になり、要件（0件表示）と食い違うため。無効値はそのまま `where('status', '不正値')` となり、一致するレコードがないので自然に0件になる。
- `with('book')` で N+1 を回避する。
- ページネーションは行わない（Bladeに `->links()` がない。`get()` で全件）。
- 並び順は登録日の新しい順（`latest()`）。

### 4-2. 読了・編集ボタンの表示制御は Blade 側で完結する

「読了する」「編集」を出すかどうかは Blade が `$plan->status !== ReadingPlanStatus::Completed` で判定する（§3）。コントローラー側で計画を状態別に振り分けて渡す必要はない。ただし、Bladeの表示制御をすり抜けて直接エンドポイントを叩かれた場合の防御は必要（§4-5・§5）。

### 4-3. create の書籍プルダウン（`$books` の母集合）

`Book::orderBy('title')->get()` を渡す。

- Book モデルが SoftDeletes を持つため、`get()` は論理削除済みの書籍を標準除外する。**削除済みの書籍はプルダウンに現れない**（要件シート シート7 「作成画面の書籍選択肢に論理削除済みの書籍が含まれない」）。`withTrashed()` を付けないことが正しい。
- 既に進行中の計画がある書籍もプルダウンには表示する。重複の防止はプルダウンからの除外ではなく、送信後のバリデーション（§6 重複制御）で行う。プルダウン側で除外しない理由は、完了・期限切れの計画しか持たない書籍は再登録できるべきで、プルダウン側で機械的に除くと再登録できる書籍まで消えるため。
- 並び順はタイトル昇順。

### 4-4. update は target_date のみを変更する

編集フォームには期日入力欄しかない（§3 PG17）。`update` では `target_date` だけを更新する。

- `UpdateReadingPlanRequest` の `validated()` は `target_date` のみを返すようルールを1項目に絞る。
- 更新対象は `$plan->update(['target_date' => $validated['target_date']])` と1カラムを明示して限定する。これにより、リクエストボディに `book_id` や `status` が混入しても `target_date` 以外は変更されない。
- 状態は update では変えない。期限切れの計画を編集しても期限切れのまま（読了操作でのみ完了へ遷移する。§2）。

### 4-5. complete の前提

- `complete` は POST。認可は `ReadingPlanPolicy::complete`（所有者判定）。
- 完了済みの計画に対する `complete` は、Blade側でボタンが出ないため通常は到達しない。直接POSTされた場合の防御として、Policy の `complete` に「完了済みでないこと」を含める（§5）。これにより完了済みの計画への二重完了は 403 になる。

---

## 5. 認可（`App\Policies\ReadingPlanPolicy`）

コントローラーで `$this->authorize()` を呼ぶ。

| メソッド | 判定 |
|---|---|
| `update(User $user, ReadingPlan $plan)` | `$user->id === $plan->user_id` |
| `delete(User $user, ReadingPlan $plan)` | `$user->id === $plan->user_id` |
| `complete(User $user, ReadingPlan $plan)` | `$user->id === $plan->user_id && $plan->status !== ReadingPlanStatus::Completed` |

- `viewAny` / `view` / `create` は定義しない（一覧は自分の分のみをコントローラーで絞り込み、作成は所有者概念なしでログイン必須のみ）。
- 他ユーザーの計画に対する edit・update・destroy・complete は所有者判定で 403 になる。要件シート シート9 の ReadingPlanSeeder は、山田太郎（ID1〜5）と鈴木花子（ID6）を用意し、山田ログイン中に `/reading-plans/6/edit` を直打ちして 403 を確認するシナリオを想定している。
- `complete` に完了済み除外を入れることで、完了済みの計画への読了POST直打ちを 403 で弾く。

---

## 6. バリデーション（要件シート シート8・確定版）

FormRequest に必ず分離する。`messages()` に下表の文言をそのまま実装する。

### `App\Http\Requests\StoreReadingPlanRequest`（reading-plans.store）

| フィールド | ルール | メッセージ |
|---|---|---|
| `book_id` | `required, exists:books,id` | 書籍を選択してください／選択された書籍が存在しません |
| `target_date` | `required, date, after_or_equal:today` | 期日を入力してください／期日は本日以降の日付で指定してください |

**重複制御**（同一ユーザーが同一書籍に対して `in_progress` の計画を重複して持てない）

- 対象ユーザー（`Auth::id()`）× `book_id` で `status = in_progress` の既存レコードがあればエラーにする。
- 実装は FormRequest の `withValidator()` に追加ルールとして書く。

```php
public function withValidator(Validator $validator): void
{
    $validator->after(function ($validator) {
        $exists = ReadingPlan::where('user_id', Auth::id())
            ->where('book_id', $this->book_id)
            ->where('status', ReadingPlanStatus::InProgress)
            ->exists();
        if ($exists) {
            $validator->errors()->add('book_id', 'この書籍は既に読書計画に登録されています');
        }
    });
}
```

- 既存の計画が `completed` または `expired` のみであれば、同じ書籍で新規作成できる（`in_progress` のみを重複判定の対象にするため）。
- 他ユーザーが同じ書籍に `in_progress` の計画を持っていても、自分の新規作成には影響しない（`user_id` で絞っているため）。

`authorize()` は `true`（ログイン必須は `auth` ミドルウェアが担保。所有者概念なし）。

### `App\Http\Requests\UpdateReadingPlanRequest`（reading-plans.update）

| フィールド | ルール | メッセージ |
|---|---|---|
| `target_date` | `required, date, after_or_equal:today` | 期日を入力してください／期日は本日以降の日付で指定してください |

- 編集で変更できるのは `target_date` のみ（§4-4）。`book_id` の再検証・重複再チェックは行わない（書籍は変更できないため）。
- `authorize()` は `true`（所有者判定は Policy が担当。コントローラーの `$this->authorize('update', $plan)` で行う）。

---

## 7. 状態更新バッチ（進行中→期限切れ・毎日0:00）

日次バッチのうち、状態を自動で切り替える処理。通知の発火（お知らせ送信・7:00）は notifications.md が持つ。両バッチは同じ判定条件を共有するため、条件の唯一の基準を本書に置く。

### 実装形態

- Console Command として実装する（CLAUDE.md 応用フェーズ追加ルール「Schedule + Console Command」）。コマンド名は `reading-plans:expire`（例。任意の分かりやすい名前でよいが、状態更新であることが分かる名前にする）。
- `app/Console/Kernel.php`（または Laravel 11+ の `routes/console.php`）で `Schedule::command('reading-plans:expire')->dailyAt('00:00')` として登録する。お知らせ送信（7:00）とは別のスケジュールとして登録する。

### 対象と処理

| 項目 | 内容 |
|---|---|
| 対象 | 全ユーザーの `status = in_progress` の計画のうち、`target_date` が「本日の3日前」に一致するもの |
| 判定 | 日付単位で比較する（`target_date` = `Carbon::today()->subDays(3)`）。時刻は見ない |
| 処理 | 対象計画の `status` を `expired` に更新する |
| 対象範囲 | 特定ユーザーに限定しない。全ユーザーの進行中の計画が対象 |

### 実装上の確定事項

- 複数レコードの一括更新は `DB::transaction()` で囲む（CLAUDE.md 応用フェーズ追加ルール「Database Transaction」）。途中で例外が発生しても中途半端な更新が残らないようにする。
- 個別レコードの処理に失敗した場合はログに記録し、他レコードの処理を継続する（要件シート シート7 確定）。
- 対象が0件でも異常終了しない。
- `completed` の計画は対象外。期日が3日以上過ぎていても完了のまま変わらず、`completed_at` を書き換えない。
- 既に `expired` の計画を再度実行しても状態は変わらない（対象が `in_progress` のみのため、二重適用は自然に回避される）。
- **同じ「本日の3日前」という条件を、7:00のお知らせ送信バッチが `three_days_after` 通知の発火条件としても使う**（notifications.md §）。0:00で状態を切り替え、7:00で通知を送る。実行時刻とスケジュールは分離するが、対象となる計画レコードは同一。判定が日付単位のため、0:00と7:00の間で日付をまたがない限り拾い漏れは起きない。

---

## 8. テーブル仕様（要件シート シート12）

### reading_plans

| カラム | 型 | PK | NOT NULL | FK | 補足 |
|---|---|---|---|---|---|
| id | bigint unsigned | ○ | ○ | | `$table->id()` |
| user_id | bigint unsigned | | ○ | users.id | `restrictOnDelete()` |
| book_id | bigint unsigned | | ○ | books.id | `cascadeOnDelete()`。books は SoftDelete のため物理削除されず、実際には発火しない |
| target_date | date | | ○ | | 計画の期日 |
| status | varchar(20) | | ○ | | `in_progress` / `completed` / `expired` の3値。`App\Enums\ReadingPlanStatus` で管理し、モデルの `casts()` で Enum にキャストする |
| completed_at | timestamp | | | | NULL許可。完了時に現在日時を記録 |
| created_at / updated_at | timestamp | | | | `$table->timestamps()` |

`status` にはDBのデフォルト値を設定してよい（`->default('in_progress')`）が、`store` で明示的に `InProgress` を入れるため必須ではない。

---

## 9. モデル（`App\Models\ReadingPlan`）

```php
class ReadingPlan extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'book_id', 'target_date', 'status', 'completed_at'];

    protected $casts = [
        'target_date'  => 'date',
        'completed_at' => 'datetime',
        'status'       => ReadingPlanStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }
}
```

- `status` を `ReadingPlanStatus::class` にキャストすることで、Blade が `$plan->status->label()` を呼べる。DBには文字列で保存される。
- `target_date` を `date` キャストすることで、Blade の `$plan->target_date->format('Y-m-d')` が動く。
- `completed_at` を `datetime` キャストすることで、Blade の `$plan->completed_at?->format('Y-m-d')` が動く。NULL のときは `?->` により「-」表示になる。
- `book()` に `->withTrashed()` を付ける（CLAUDE.md 9-1）。計画作成後に対象書籍が論理削除されても、一覧で `$plan->book->title` が null にならず、画面が落ちない。
- リレーションメソッドには戻り値型 `BelongsTo` を宣言する（CLAUDE.md 応用フェーズ追加ルール）。
- User モデルに `readingPlans(): HasMany`（`hasMany(ReadingPlan::class)`）を追加する。Book モデルには `readingPlans` リレーションを追加しない（書籍側から計画をたどる集計は本機能に存在しないため）。

---

## 10. シーディング（ReadingPlanSeeder・要件シート シート9／改変禁止）

reading_plans テーブルに6件投入する。採点者がいつ実行しても同じ挙動になるよう、`Carbon::today()` 起点で動的に `target_date` を設定する。動作確認の効率を考え、主要シナリオは山田太郎（ID 1〜5）に集約する。`create` を使う。

| # | 所有者 | target_date | status | completed_at | 意図するシナリオ |
|---|---|---|---|---|---|
| 1 | 山田太郎 | `today()->addDays(3)` | in_progress | — | 3日前リマインダー対象 |
| 2 | 山田太郎 | `today()` | in_progress | — | 当日リマインダー対象 |
| 3 | 山田太郎 | `today()->subDays(3)` | in_progress | — | 状態更新バッチで期限切れ化 ＋ 3日後再エンゲージメント対象（二重シナリオ） |
| 4 | 山田太郎 | `today()->addDays(7)` | in_progress | — | リマインダー対象外 |
| 5 | 山田太郎 | `today()->subDays(10)` | completed | `today()->subDays(5)` | 完了済み |
| 6 | 鈴木花子 | `today()->addDays(5)` | in_progress | — | 山田太郎ログイン中に `/reading-plans/6/edit` 直打ちで 403 確認用 |

- `book_id` は計画ごとに異なる書籍を割り当てる（同一ユーザーの `in_progress` 重複制御に抵触しないようにするため）。
- DatabaseSeeder の実行順では、BookSeeder より後に呼ぶ（book_id が必要なため）。UserSeeder → GenreSeeder → BookSeeder → ReviewSeeder → FavoriteSeeder → ReviewLikeSeeder → ReadingPlanSeeder の順。
- #3 は状態更新バッチを実行すると `expired` に変わると同時に、7:00のお知らせ送信バッチで `three_days_after` 通知の対象にもなる（二重シナリオ）。この再現を §12 のバッチテストで確認する。

---

## 11. 画面遷移・フラッシュ文言（要件シート シート7・確定版）

| 操作 | 成功時の遷移先 | フラッシュ文言 | 失敗時 | 認可失敗時 |
|---|---|---|---|---|
| 一覧を表示 | 当該画面 | — | — | 未認証: `/login` へ |
| 状態の絞り込み | 当該画面（絞り込み結果） | — | 未定義値は該当0件表示。バリデーションエラーにしない | 未認証: `/login` へ |
| 「読了する」ボタン | `reading-plans.index` | `読書計画を完了しました` | 業務エラーなし | 403（所有者以外・完了済み） |
| 「編集」ボタン | `reading-plans.edit` | — | — | 未認証: `/login` へ／403（所有者以外） |
| 「削除」ボタン | `reading-plans.index` | `読書計画を削除しました` | 業務エラーなし | 403（所有者以外） |
| 「新規計画作成」ボタン | `reading-plans.create` | — | — | 未認証: `/login` へ |
| 作成フォーム送信 | `reading-plans.index` | `読書計画を登録しました` | `back()` + `$errors`（自動） | 未認証: `/login` へ |
| 更新フォーム送信 | `reading-plans.index` | `読書計画を更新しました` | `back()` + `$errors`（自動） | 403（所有者以外） |

フラッシュは `reading-plans/index.blade.php` の `session('success')` スロットで描画される。成功系の遷移先がすべて index であることと一致している。

---

## 12. テスト観点（要件シート シート10）

**全体要件（共通）**: 全テスト通過。`sail artisan test --coverage` で応用機能込み80%以上を目標。

### 機能テスト `tests/Feature/ReadingPlanTest.php`（シート10「読書計画」）

| # | 検証観点 |
|---|---|
| F-RP1 | 認可・未認証 — 未ログインで `/reading-plans` および作成・更新・削除・読了の各エンドポイントにアクセスすると `/login` へリダイレクトされる |
| F-RP2 | 一覧の範囲 — ログインユーザー自身の計画のみが表示され、他ユーザーの計画が混入しない |
| F-RP3 | 一覧の空表示 — 該当する計画が0件のとき「該当する読書計画はありません。」が表示される |
| F-RP4 | 状態の絞り込み — `status=in_progress` / `completed` / `expired` それぞれで当該状態の計画のみが表示され、無指定ではすべてが表示される |
| F-RP5 | 不正な絞り込み値 — `status` に未定義の値を渡すとバリデーションエラーにならず0件表示になる |
| F-RP6 | 作成の正常系 — 書籍と期日を入力して登録すると計画が作成され、`reading-plans.index` へ遷移して「読書計画を登録しました」が表示される |
| F-RP7 | 作成のバリデーション — 書籍未選択、期日未入力、期日に過去日を指定した各ケースで `back()` + `$errors` によりエラーが表示される |
| F-RP8 | 重複制御 — 同一ユーザーが同一書籍に対して進行中の計画を2件目として作成しようとするとエラーになり、`book_id` 欄に「この書籍は既に読書計画に登録されています」が表示される |
| F-RP9 | 重複制御の境界 — 同一書籍でも既存の計画が完了または期限切れであれば新規作成できる |
| F-RP10 | 他ユーザーとの独立 — 別のユーザーが同じ書籍に対して進行中の計画を持っていても、自分の新規作成は成功する |
| F-RP11 | 読了操作 — 「読了する」を押すと状態が完了に変わり `completed_at` が記録され、`reading-plans.index` へ遷移して「読書計画を完了しました」が表示される |
| F-RP12 | 完了後の操作制限 — 状態が完了の計画には「読了する」「編集」ボタンが表示されず、削除のみ可能である。完了済みへの読了POST直打ちは403になる |
| F-RP13 | 期限切れからの操作 — 状態が期限切れの計画でも「読了する」「編集」が操作でき、完了へ遷移できる |
| F-RP14 | 削除 — 「削除」を押すと計画が削除され、`reading-plans.index` へ遷移して「読書計画を削除しました」が表示される |
| F-RP15 | 認可の詳細 — 他ユーザーの計画に対する GET edit・PUT update・POST complete・DELETE destroy がいずれも403になる（4件はHTTPメソッドごとに個別のリクエストで検証する） |
| F-RP16 | 書籍プルダウン — 作成画面の書籍選択肢に論理削除済みの書籍が含まれない |
| F-RP17 | 削除済み書籍を持つ計画の表示 — 計画作成後に対象書籍が論理削除されても、一覧で当該計画の書籍タイトルが表示され画面がエラーにならない |

### 機能テスト `tests/Feature/ReadingPlanUpdateTest.php`（シート10「読書計画（期限変更）」）

| # | 検証観点 |
|---|---|
| F-RU1 | 編集画面の初期表示 — 対象書籍のタイトルと現在の状態が表示専用として表示され、期日入力欄に既存の `target_date` が `Y-m-d` 形式で初期値として入る |
| F-RU2 | 更新の正常系 — 期日を変更して更新すると `target_date` が更新され、`reading-plans.index` へ遷移して「読書計画を更新しました」が表示される |
| F-RU3 | 更新対象の限定 — 更新リクエストに `book_id` や `status` を混ぜて送信しても、`target_date` 以外は変更されない |
| F-RU4 | 更新のバリデーション — 期日未入力、期日に過去日を指定した各ケースで `back()` + `$errors` によりエラーが表示される |
| F-RU5 | 完了済み計画の編集不可 — 状態が完了の計画は一覧に編集ボタンが表示されず、直接アクセスしても編集操作の対象にならない |
| F-RU6 | 期限切れ計画の編集 — 状態が期限切れの計画は期日を変更でき、変更後も状態は期限切れのまま維持される |
| F-RU7 | 認可 — 他ユーザーの計画に対する GET edit と PUT update がそれぞれ403になる |
| F-RU8 | 期日変更と通知の連動 — 期日を本日の3日後に変更した計画が、次回のリマインダーバッチで3日前通知の対象になる |
| F-RU9 | 期日変更と失効の連動 — 期日を本日の3日前に変更した計画が、次回の状態更新バッチで期限切れになる |

### 機能テスト `tests/Feature/ReadingPlanExpireBatchTest.php`（シート10「自動失効バッチ」）

| # | 検証観点 |
|---|---|
| F-EB1 | スケジュール登録 — 状態更新処理が毎日0:00に実行されるようスケジュール定義されており、お知らせ送信処理とは別のスケジュールとして登録されている |
| F-EB2 | 失効の正常系 — 期日が本日の3日前で状態が進行中の計画が、バッチ実行後に期限切れへ変わる |
| F-EB3 | 境界・失効しない側 — 期日が本日の2日前の計画はバッチ実行後も進行中のまま変わらない |
| F-EB4 | 境界・当日と未来 — 期日が本日および本日以降の計画はバッチ実行後も進行中のまま変わらない |
| F-EB5 | 対象外の状態 — 状態が完了の計画は期日が3日以上過ぎていても完了のまま変わらず、`completed_at` が書き換えられない |
| F-EB6 | 二重適用の回避 — 既に期限切れの計画に対して再度バッチを実行しても状態が変わらず、余分な更新が発生しない |
| F-EB7 | 対象範囲 — 全ユーザーの進行中の計画が対象であり、特定ユーザーに限定されていない |
| F-EB8 | 失効後の操作 — 期限切れになった計画が引き続き編集・読了・削除でき、UI上でブロックされない |
| F-EB9 | 対象0件 — 該当する計画が1件もない状態でバッチを実行しても異常終了しない |
| F-EB10 | 個別失敗時の継続 — 1件の処理に失敗しても残りのレコードの処理が継続され、失敗がログに記録される |
| F-EB11 | 原子性 — 複数レコードの一括更新が `DB::transaction()` で囲まれており、途中で例外が発生した場合に中途半端な更新が残らない |
| F-EB12 | シードシナリオの再現 — ReadingPlanSeeder 投入後にバッチを実行すると、期日が本日の3日前の計画が期限切れへ変わると同時に3日後通知の対象にもなる二重シナリオが再現される |

F-RP15 の認可4件、および各バッチテストは HTTPメソッド／実行単位ごとに個別に検証し、いずれか1つの結果で他を代表させない（CLAUDE.md 12-4 規則3）。
