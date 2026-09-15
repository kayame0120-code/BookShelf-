# 機能仕様書 — notifications（通知・リマインダー・日次バッチ）

| 項目 | 内容 |
|---|---|
| 対象発注書 | 12_通知バッチ |
| 正本 | 要件シート.xlsx シート5/7/9/10/12 ＋ Bladeモック `advanced`（frozen） |
| 適用範囲 | 応用要件。通知一覧の表示・既読化、読書計画リマインダー通知、お知らせ送信の日次バッチ、notifications テーブル、テスト観点 |
| 完成条件 | 本書だけで発注書12が書ける。ただし読書計画の状態モデル・状態更新バッチ（0:00）は reading_plans.md が持つ。本書はお知らせ送信バッチ（7:00）と通知本体を持つ |

通知は、読書計画の期日に応じてユーザーへリマインダーを送る機能。3種類のタイミング（3日前・当日・3日後）で通知を発火し、ユーザーは通知一覧で確認・既読化できる。ヘッダーのベルアイコンに未読件数が表示される。

---

## 0. スコープ

**含む**: 通知一覧画面の表示・既読化操作、ヘッダーの未読件数バッジ、`ReadingPlanReminder` 通知クラス、お知らせ送信の日次バッチ（毎日7:00）、notifications テーブル、以上のテスト観点。

**含まない**: 読書計画のCRUD・状態モデル・状態更新バッチ（進行中→期限切れ、0:00）は reading_plans.md が持つ。通知の発火条件が参照する「期日の3日前／当日／3日後」の定義も reading_plans.md §7 と本書 §5 で一致させる。

読書計画側の日次バッチ（状態更新・0:00）と本書の日次バッチ（お知らせ送信・7:00）は別スケジュール。両者は同じ計画レコードを対象とするが、実行時刻と処理内容が異なる。状態更新は reading_plans.md、お知らせ送信は本書が担当する。

---

## 1. ルーティング

`routes/web.php`。すべて認証必須。カッコ内は Blade が `route()` で参照している名前で、変更不可。

| # | メソッド | URI | route名 | Controller@Action | 認証 | 認可 |
|---|---|---|---|---|---|---|
| 1 | GET | `/notifications` | `notifications.index` | `NotificationController@index` | 必須 | — |
| 2 | POST | `/notifications/{id}/read` | `notifications.read` | `NotificationController@read` | 必須 | §4（コントローラー内で所有者判定） |

### 定義コード

```php
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
});
```

- `{id}` は通知の UUID（notifications テーブルの主キーは char(36) UUID）。ルートモデル紐付けは使わず、コントローラー内で `Auth::user()->notifications()->findOrFail($id)` の形で解決する（§4）。
- Blade は `route('notifications.read', $notification->id)` の形で参照する。パラメータ名は `{id}` に合わせる。

---

## 2. 画面契約（Bladeモック実測・改変禁止）

| 画面ID | Bladeファイル | 渡す変数 | Blade が要求する属性 | flash |
|---|---|---|---|---|
| PG18 | `notifications/index.blade.php` | `$notifications`（Collection） | `$notification->id`／`$notification->data['timing']`／`$notification->data['title']`／`$notification->data['body']`／`$notification->read_at`（null 判定）／`$notification->created_at->diffForHumans()` | `session('success')` あり |
| 共通 | `layouts/navigation.blade.php` | （なし。Blade内で `Auth::user()->unreadNotifications->count()` を直接取得） | `Auth::user()->unreadNotifications` | — |

**通知一覧（PG18）の実測ポイント**

- 各通知は `$notification->data['timing']` の値（`three_days_before` / `on_due_date` / `three_days_after`）でアイコンと色が切り替わる。data に含めるべきキーは `timing` / `title` / `body`（§3・§6）。
- 未読判定は `$notification->read_at === null`。未読には「未読」バッジ、左端の色付きバー、「既読にする」ボタンが表示される。既読には表示されない。
- 「既読にする」は `route('notifications.read', $notification->id)` への POST フォーム（`novalidate`）。
- 通知が0件のとき「通知はありません。」を表示する。
- 各通知の時刻表示は `$notification->created_at->diffForHumans()`。

**ヘッダーの未読件数（navigation.blade.php の実測ポイント）**

- `@auth` ブロック冒頭で `$unreadNotificationCount = Auth::user()->unreadNotifications->count()` を取得している。
- ベルアイコンに `$unreadNotificationCount > 0` のときだけ赤い件数バッジを表示する。
- `unreadNotifications` は Laravel の `Notifiable` トレイトが提供する標準リレーション。User モデルに `Notifiable` を含めれば自動で使える（§7）。追加実装は不要。

**変数名の対応**: 一覧は `$notifications`。ナビゲーションは Blade が自前で取得するため、コントローラーから渡す必要はない。

---

## 3. 通知データの構造（Laravel標準通知・DatabaseChannel）

通知は Laravel 標準の Notification facade を使い、DatabaseChannel（`database`）に保存する（CLAUDE.md 応用フェーズ追加ルール）。notifications テーブルの `data` カラムに JSON で格納される。

`data` に含めるキーは4つ。

| キー | 型 | 内容 |
|---|---|---|
| `timing` | string | `three_days_before` / `on_due_date` / `three_days_after` のいずれか。Bladeのアイコン・色分岐に使う |
| `title` | string | 通知タイトル（§6 の確定文言） |
| `body` | string | 通知本文（§6 の確定文言。書籍タイトルを埋め込む） |
| `reading_plan_id` | int | 発火元の読書計画ID。通知と計画を紐付けるために保持する |

Bladeが読むのは `timing` / `title` / `body` の3キー。`reading_plan_id` は表示に使わないが、テスト・追跡のために保持する（要件シート シート12 notifications.data の記載「timing／title／body／reading_plan_id を含める」に一致）。

---

## 4. コントローラー仕様（`App\Http\Controllers\NotificationController`）

| アクション | 処理 |
|---|---|
| `index` | `$notifications = Auth::user()->notifications` を `notifications.index` ビューへ渡す（§4-1） |
| `read` | 通知を所有者スコープで解決 → 既読化 → `back()`（§4-2） |

### 4-1. index

```php
public function index(): View
{
    $notifications = Auth::user()->notifications;

    return view('notifications.index', compact('notifications'));
}
```

- `Auth::user()->notifications` は `Notifiable` トレイトが提供する標準リレーション。**新しい順（`created_at` 降順）で返る**（Notifiable の `notifications()` リレーションが `orderBy('created_at', 'desc')` を標準で持つため）。Bladeが期待する「新しい順」と一致する。追加のソート指定は不要。
- ログインユーザー自身の通知のみが対象。`notifiable_id = Auth::id()` の絞り込みはリレーションが自動で行う。
- ページネーションは行わない（Blade に `->links()` がない）。

### 4-2. read（既読化）

```php
public function read(string $id): RedirectResponse
{
    $notification = DatabaseNotification::findOrFail($id);

    abort_unless(
        $notification->notifiable_id === Auth::id()
        && $notification->notifiable_type === User::class,
        403
    );

    $notification->markAsRead();

    return back();
}
```

- `Illuminate\Notifications\DatabaseNotification` を `findOrFail($id)` で解決し、`notifiable_id !== Auth::id()`（または `notifiable_type` が User でない）のとき `abort(403)` する。要件シート シート7 が「他人の通知IDを直打ちされた場合は 403」と定めているため、所有者スコープの `findOrFail`（この方式だと該当なしで 404 になる）ではなく、レコードを取得してから明示的に 403 を返す方式にする。
- `markAsRead()` は `read_at` に現在日時をセットする Laravel 標準メソッド。既読化後、未読バッジと既読化ボタンは Blade 側の `read_at === null` 判定で自動的に消える。
- 成功時は `back()` で元画面（通知一覧）に戻る。flash は出さない（Bladeの未読バッジが消えることで状態を表現する。要件シート シート7 確定「flashなし」）。

### 4-3. flash を使わない理由

通知既読化は他のトグル系操作（お気に入り・いいね）とflash方針を統一し、flash を出さない。ただし通知一覧Bladeには `session('success')` スロットがある（削除など将来の拡張余地として）。本機能では既読化で success flash を設定しないため、このスロットは通常空のまま。

---

## 5. お知らせ送信バッチ（毎日7:00）

読書計画の期日に応じてリマインダー通知を発火する日次バッチ。状態更新バッチ（0:00・reading_plans.md §7）とは別スケジュール。

### 実装形態

- Console Command として実装する（CLAUDE.md 応用フェーズ追加ルール「Schedule + Console Command」）。コマンド名は `reading-plans:remind`（例。お知らせ送信であることが分かる名前にする）。
- スケジュール登録は `Schedule::command('reading-plans:remind')->dailyAt('07:00')`。状態更新（0:00）とは別のスケジュールとして登録する。

### 発火条件と通知種別

判定はすべて日付単位（時刻を見ない）。対象は `status = in_progress` の計画のみ。全ユーザーが対象で、特定ユーザーに限定しない。

| timing | 対象条件 | 通知タイトル | 通知本文 |
|---|---|---|---|
| `three_days_before` | `target_date` が「本日の3日後」（`today()->addDays(3)`） | 読書計画のリマインダー | 『{書籍タイトル}』の期日まで残り3日です。 |
| `on_due_date` | `target_date` が「本日」（`today()`） | 読書計画の期日です | 『{書籍タイトル}』の期日は本日です。 |
| `three_days_after` | `target_date` が「本日の3日前」（`today()->subDays(3)`） | 読書計画の期日が過ぎました | 『{書籍タイトル}』の期日から3日が経過したため、計画は自動的に「期限切れ」になりました。 |

- `{書籍タイトル}` は当該計画の書籍のタイトル（`$plan->book->title`）を埋め込む。

**timing 別の対象 status（確定）**

| timing | 対象 status | 対象条件（status ＋ 日付） |
|---|---|---|
| `three_days_before` | `in_progress` のみ | まだ読んでいる最中の計画にリマインドする |
| `on_due_date` | `in_progress` のみ | 同上 |
| `three_days_after` | `completed` 以外（`in_progress` と `expired`） | 「期限切れになりました」を送るため |

- `three_days_after` を `completed` 以外にする理由: このバッチ（7:00）が動く時点では、同じ計画は 0:00 の状態更新バッチ（reading_plans.md §7）によって既に `in_progress` から `expired` へ切り替わっている。`three_days_after` を `in_progress` 限定にすると、切り替え済みの計画が対象から漏れて通知が送られない。そのため対象を「`target_date` が本日の3日前 かつ `status != completed`」とし、`expired` になった計画にも通知が届くようにする。`completed`（読了済み）の計画には「期限切れ」通知を送らないため除外する。

### 処理と確定事項

- 3種類の判定を1回の実行で行い、該当する計画それぞれに通知を送る。同一計画が複数timingに該当することは日付条件上ありえない（3日前・当日・3日前後は別日）。
- 通知の送信は `$user->notify(new ReadingPlanReminder($plan, $timing))` の形で行う。宛先は計画の所有者（`$plan->user`）。作成された通知の `notifiable_id` は当該計画の所有者になり、他ユーザーには届かない。
- 複数レコードの一括処理は `DB::transaction()` で囲む（CLAUDE.md 応用フェーズ追加ルール「Database Transaction」）。
- 個別レコードの処理に失敗した場合はログに記録し、他レコードの処理を継続する（要件シート シート7 確定）。状態更新・お知らせ送信はそれぞれ独立して失敗しうる。
- 対象0件でも異常終了しない。
- 作成される通知の `read_at` は NULL（未読状態）で作られる。

### 通知クラス（`App\Notifications\ReadingPlanReminder`）

```php
class ReadingPlanReminder extends Notification
{
    public function __construct(
        private ReadingPlan $plan,
        private string $timing,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'timing'          => $this->timing,
            'title'           => $this->titleFor($this->timing),
            'body'            => $this->bodyFor($this->timing),
            'reading_plan_id' => $this->plan->id,
        ];
    }
    // titleFor / bodyFor は §5 発火条件表の確定文言を timing で分岐して返す
}
```

- `via()` は `['database']` のみ（DatabaseChannel）。メール等は使わない。
- `toArray()` の戻り値が notifications.data に JSON で保存される。§3 の4キーを返す。
- `notifications` テーブルの `type` カラムには通知クラス名（`App\Notifications\ReadingPlanReminder`）が Laravel によって自動で入る。

---

## 6. 確定文言（要件シート シート7・変更禁止）

| timing | タイトル | 本文 |
|---|---|---|
| `three_days_before` | 読書計画のリマインダー | 『{book.title}』の期日まで残り3日です。 |
| `on_due_date` | 読書計画の期日です | 『{book.title}』の期日は本日です。 |
| `three_days_after` | 読書計画の期日が過ぎました | 『{book.title}』の期日から3日が経過したため、計画は自動的に「期限切れ」になりました。 |

`{book.title}` は当該計画の書籍タイトルに置換する。かぎ括弧『』は文言の一部として含める。

---

## 7. モデル・テーブル

### User モデル

```php
class User extends Authenticatable
{
    use Notifiable; // 通知の受信・unreadNotifications・notifications リレーションを提供
    // ...
}
```

- `Illuminate\Notifications\Notifiable` トレイトを含める。これにより `$user->notify(...)`、`$user->notifications`、`$user->unreadNotifications` が使える。Blade（navigation・通知一覧）とコントローラーがこれらを前提にしている。

### notifications テーブル（要件シート シート12・Laravel標準マイグレーション）

| カラム | 型 | PK | NOT NULL | FK | 補足 |
|---|---|---|---|---|---|
| id | char(36) | ○ | ○ | | UUID。`$table->uuid('id')->primary()` |
| type | varchar(255) | | ○ | | 通知クラス名（`App\Notifications\ReadingPlanReminder`） |
| notifiable_type | varchar(255) | | ○ | | 常に `App\Models\User` |
| notifiable_id | bigint unsigned | | ○ | users.id（ポリモーフィック） | `$table->morphs('notifiable')` が type とこの列を同時に生成 |
| data | text | | ○ | | JSON文字列。`timing` / `title` / `body` / `reading_plan_id` を含める |
| read_at | timestamp | | | | NULL許可。既読化のタイミングで現在時刻を記録 |
| created_at / updated_at | timestamp | | | | `$table->timestamps()` |

- `php artisan notifications:table` の標準スキーマをそのまま使う。カラムを追加・変更しない。
- テーブル作成は応用段階（走行⑫）で行う。基本段階では作らない。

---

## 8. 画面遷移・フラッシュ文言（要件シート シート7・確定版）

| 操作 | 成功時の遷移先 | フラッシュ文言 | 失敗時 | 認可失敗時 |
|---|---|---|---|---|
| 通知一覧を表示 | 当該画面 | — | — | 未認証: `/login` へ |
| 「既読にする」ボタン | `notifications.index`（`back()` 相当。元画面のまま） | —（flashなし。未読バッジが消えることで状態を表現） | 業務エラーなし | 403（`notifiable_id !== Auth::id()` の場合） |

---

## 9. テスト観点（要件シート シート10「リマインダーバッチ」）

**全体要件（共通）**: 全テスト通過。`sail artisan test --coverage` で応用機能込み80%以上を目標。

通知一覧・既読化のテストと、お知らせ送信バッチのテストを含む。状態更新バッチ（0:00）のテストは reading_plans.md の ReadingPlanExpireBatchTest が持つ。

### 機能テスト `tests/Feature/NotificationReminderTest.php`

| # | 検証観点 |
|---|---|
| F-N1 | スケジュール登録 — お知らせ送信処理が毎日7:00に実行されるようスケジュール定義されている |
| F-N2 | 3日前通知 — 期日が本日の3日後で状態が進行中の計画に対し、`timing` が `three_days_before`・タイトルが「読書計画のリマインダー」・本文が「『{書籍タイトル}』の期日まで残り3日です。」の通知が1件作成される |
| F-N3 | 当日通知 — 期日が本日で状態が進行中の計画に対し、`timing` が `on_due_date`・タイトルが「読書計画の期日です」・本文が「『{書籍タイトル}』の期日は本日です。」の通知が作成される |
| F-N4 | 3日後通知 — 期日が本日の3日前の計画に対し、`timing` が `three_days_after`・タイトルが「読書計画の期日が過ぎました」・本文が「『{書籍タイトル}』の期日から3日が経過したため、計画は自動的に「期限切れ」になりました。」の通知が作成される |
| F-N5 | 対象外の日付 — 期日が本日の7日後の計画には通知が作成されない |
| F-N6 | 対象外の状態 — 状態が完了の計画には期日が該当していても通知が作成されない |
| F-N7 | 通知の宛先 — 作成された通知の `notifiable_id` が当該計画の所有者であり、他ユーザーには届かない |
| F-N8 | 保存形式 — 通知レコードの `data` に `timing`・`title`・`body`・`reading_plan_id` が含まれ、`read_at` が NULL で作成される |
| F-N9 | 対象0件 — 該当する計画が1件もない状態でバッチを実行しても異常終了せず、通知が作成されない |
| F-N10 | 個別失敗時の継続 — 1件の処理に失敗しても残りのレコードの処理が継続され、失敗がログに記録される |
| F-N11 | 通知一覧の表示 — `GET /notifications` で自分の通知が新しい順に表示され、未読には「未読」バッジと「既読にする」ボタンが表示される |
| F-N12 | 通知一覧の空表示 — 通知が0件のとき「通知はありません。」が表示される |
| F-N13 | 既読化 — 「既読にする」を押すと `read_at` が記録され、未読バッジと既読化ボタンが消える |
| F-N14 | 既読化の認可 — 他ユーザーの通知IDを直接POSTすると403が返る |
| F-N15 | ヘッダーの未読件数 — 未読がある間はヘッダーのベルアイコンに未読件数が表示され、すべて既読にすると表示が消える |

F-N2〜F-N4 は `Carbon::setTestNow()` 等で本日を固定し、`target_date` を相対日で用意して検証する。各通知種別を個別に確認し、いずれか1つで他を代表させない（CLAUDE.md 12-4 規則3）。
