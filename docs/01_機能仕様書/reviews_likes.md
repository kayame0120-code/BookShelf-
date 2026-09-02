# 機能仕様書 — reviews_likes（レビュー投稿・編集・削除／いいねトグル）

| 項目 | 内容 |
|---|---|
| 対象発注書 | 03_レビュー・お気に入り・いいね（レビューといいねの部分） |
| 正本 | 要件シート.xlsx シート5/7/8/9/10/11/12 ＋ Bladeモック `basic`（frozen） |
| 適用範囲 | 基本要件のみ |
| 完成条件 | 本書だけで発注書03のレビュー・いいね部分が書ける（他ファイル参照不要） |
| 版 | v1（2026-09-01・面談②のSoftDelete確定を反映済み） |

---

## 0. スコープ

**含む**: レビューの投稿・編集・更新・削除、レビューへのいいねトグル、ReviewPolicy、reviews / review_likes テーブル、ReviewSeeder / ReviewLikeSeeder、レビューといいねのテスト観点。

**含まない**: お気に入りトグル（favorites.md）／書籍詳細ページ本体の実装（books.md）／全テーブルのmigration統括（auth.md）。

**コントローラーの割り当て（面談①確定）**: レビューのいいねは独立した一覧画面を持たず、レビューの付随操作としてしか存在しないため、専用の LikeController は作らず `ReviewController@like` に統合する。判断基準は「独立した一覧画面を持つかどうか」で、一覧を持つお気に入りだけが専用コントローラーになる。

---

## 1. ルーティング

| # | メソッド | URI | route名 | Controller@Action | 認証 | 認可 |
|---|---|---|---|---|---|---|
| 1 | POST | `/books/{book}/reviews` | `reviews.store` | `ReviewController@store` | 必須 | — |
| 2 | GET | `/reviews/{review}/edit` | `reviews.edit` | `ReviewController@edit` | 必須 | `ReviewPolicy::update` |
| 3 | PUT | `/reviews/{review}` | `reviews.update` | `ReviewController@update` | 必須 | `ReviewPolicy::update` |
| 4 | DELETE | `/reviews/{review}` | `reviews.destroy` | `ReviewController@destroy` | 必須 | `ReviewPolicy::delete` |
| 5 | POST | `/reviews/{review}/like` | `reviews.like` | `ReviewController@like` | 必須 | — |

```php
Route::middleware('auth')->group(function () {
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])->name('reviews.like');
});
```

`reviews.store` の `{book}` には `withTrashed()` を**付けない**。削除済み書籍へ直接POSTした場合はルートモデル紐付けが 404 を返し、要件シート シート7 の「削除済みの書籍では投稿フォーム自体が非表示のため到達不能」を構造的に保証する。

`reviews.edit` / `update` / `destroy` / `like` は `{review}` で解決するため、参照先の書籍が削除済みでも常に到達可能。これは要件どおり（本の状態と無関係な操作は残す）。

`index` / `show` / `create` は作らない。レビュー投稿フォームは `books/show.blade.php` に埋め込まれており、レビュー単独表示画面は基本13画面に存在しない。

---

## 2. コントローラー仕様（`App\Http\Controllers\ReviewController`）

| アクション | 処理 |
|---|---|
| `store` | `StoreReviewRequest` で検証 → `$book->reviews()->create(validated + user_id: Auth::id())` → `redirect()->route('books.show', $book)->with('success', 'レビューを投稿しました')` |
| `edit` | `$this->authorize('update', $review)` → `$review->load('book')` を `$review` として `reviews.edit` ビューへ渡す |
| `update` | `$this->authorize('update', $review)` → `UpdateReviewRequest` で検証 → `$review->update($validated)` → `redirect()->route('books.show', $review->book)->with('success', 'レビューを更新しました')` |
| `destroy` | `$this->authorize('delete', $review)` → **削除前に `$book = $review->book;` を退避** → `$review->delete()`（物理削除。`review_likes` は cascade で連動削除） → `redirect()->route('books.show', $book)->with('success', 'レビューを削除しました')` |
| `like` | `Auth::user()->likedReviews()->toggle($review)` → `back()`（flashなし） |

- `destroy` は `$review->book` を削除の**前**に取得すること。削除後に参照するとリダイレクト先が解決できない。
- レビューは1ユーザーが同一書籍に複数投稿できる（重複投稿制限なし）。Bladeに重複チェックUIが存在せず、既存動作を壊さない最も単純な実装がこれになるため。
- `like` はトグル。`toggle()` が追加と解除の両方を担う。

---

## 3. 画面契約（Bladeモック実測・改変禁止部分）

### PG02 書籍詳細（`books/show.blade.php`）内のレビュー領域

| 要素 | 実測仕様 |
|---|---|
| 投稿フォーム | `@auth` 内。未認証時は「レビューを投稿するには**ログイン**してください」のリンク文に切り替わる |
| 評価入力 | `<select name="rating">`。先頭に空の「選択してください」、以降 5→1 の降順で `★★★★★ (5)` 形式のラベル |
| コメント入力 | `<textarea name="comment" rows="3">`。placeholder「この書籍の感想を書いてください」 |
| 送信ボタン | 「投稿する」 |
| レビュー一覧 | `$book->reviews->count() > 0` で分岐。0件時は「まだレビューはありません。」 |
| 各レビューの表示 | `$review->user->name` ／ `str_repeat('★', $review->rating)` ／ `$review->created_at->format('Y/m/d')` ／ `@if($review->comment)` でガードした本文 |
| いいねボタン | `Auth::user()->likedReviews->contains($review->id)` で「いいね済み (n)」／「いいね (n)」を切替。件数は `$review->likedByUsers->count()`。未認証時はログインへのリンク |
| 編集・削除 | `@can('update', $review)` / `@can('delete', $review)` で分岐 |
| エラー表示 | `@error('rating')` / `@error('comment')` がフォーム直下にある |

**必須リレーション名（Blade実測・変更不可）**: `Auth::user()->likedReviews` ／ `$review->likedByUsers` ／ `$review->user` ／ `$review->book`。

### PG09 レビュー編集（`reviews/edit.blade.php`）

| 要素 | 実測仕様 |
|---|---|
| 渡す変数 | `$review` |
| ヘッダ表示 | `書籍: {{ $review->book->title }}` ← **`$review->book` が null だと落ちる（§9参照）** |
| 評価入力 | `<input type="radio" name="rating">` を1〜5で5個。`old('rating', $review->rating)` で選択状態を復元。**`required` 属性付き** |
| コメント入力 | `<textarea name="comment" rows="4">`。`old('comment', $review->comment)` |
| キャンセルリンク | `route('books.show', $review->book)` |
| 送信ボタン | 「更新する」 |
| flashスロット | なし |

投稿画面（PG02内フォーム）は select、編集画面はラジオボタンと入力UIが異なるが、送るフィールド名は両方 `rating` / `comment` で同一。

---

## 4. バリデーション（要件シート シート8・確定版）

### `App\Http\Requests\StoreReviewRequest`（reviews.store）

| フィールド | ルール | メッセージ |
|---|---|---|
| `rating` | `required, integer, between:1,5` | 評価を選択してください／評価は1〜5の範囲で選択してください |
| `comment` | `nullable, string, max:1000` | コメントは1000文字以内で入力してください |

### `App\Http\Requests\UpdateReviewRequest`（reviews.update）

StoreReviewRequest と**完全同一のルール・完全同一の文言**（差分なし）。シート8で明示的に「差分なし」と確定しているため、片方だけ変更しないこと。

両クラスとも `authorize()` は `true`（認可は Policy と `$this->authorize()` で行う）。

`rating` の 1〜5 範囲チェックは FormRequest 側だけで行い、DBに CHECK 制約は設けない。

---

## 5. 認可（`App\Policies\ReviewPolicy`）

| メソッド | 判定 |
|---|---|
| `update(User $user, Review $review)` | `$user->id === $review->user_id` |
| `delete(User $user, Review $review)` | `$user->id === $review->user_id` |

**書籍の削除状態は判定に含めない。** 参照先の書籍が論理削除されていても、投稿者本人はレビューを編集・削除できる（要件シート シート7 確定）。BookPolicy が `! $book->trashed()` を条件に含めているのと対照的なので、混同しないこと。

いいね（`like`）に Policy は不要。所有者概念がなく、ログイン必須のみ。

---

## 6. 画面遷移・フラッシュ文言（要件シート シート7・確定版）

| 操作 | 成功時の遷移先 | フラッシュ文言 | 失敗時 | 認可失敗時 |
|---|---|---|---|---|
| レビュー投稿（詳細画面のフォーム） | `books.show($book)` | `レビューを投稿しました` | `back()` + `$errors`（自動） | 未認証: `/login` へリダイレクト |
| 「編集」ボタン | `reviews.edit` | — | — | 403 |
| 更新フォーム送信 | `books.show($review->book)` | `レビューを更新しました` | `back()` + `$errors`（自動） | 403 |
| 「削除」ボタン | `books.show($review->book)` | `レビューを削除しました` | 業務エラーなし（`review_likes` は cascade） | 403 |
| 「いいね」ボタン | `back()`（元画面のまま） | **flashなし** | 業務失敗なし | 未認証: `/login` へリダイレクト |

いいねに flash を使わないのは、トグル操作を一律 flash 不使用に統一しているため（お気に入りも同じ）。状態はボタンの見た目と件数の変化で表現される。

---

## 7. 削除済み書籍下でのレビュー・いいねの扱い（面談②確定）

| 操作 | 参照先の書籍が削除済みのときの挙動 |
|---|---|
| レビュー・評価の閲覧 | そのまま表示（本人以外の分も含めて全部読める） |
| 新規レビュー投稿 | フォーム自体が非表示。案内文「削除済みの書籍にはレビューを投稿できません」に差し替え。直POSTは 404 |
| レビューの「いいね」 | **操作可能**（変更なし） |
| 投稿者本人によるレビュー編集・削除 | **操作可能**（変更なし） |

書籍の論理削除では `reviews` レコードは1件も物理削除されない。`reviews.book_id` は NOT NULL のまま（nullable 化しない）。`books` 起点の `cascadeOnDelete()` は設定として残っているが、物理DELETEが発行されないため実際には発火しない。

---

## 8. テーブル仕様（要件シート シート12）

### reviews

| カラム | 型 | PK | NOT NULL | FK | 補足 |
|---|---|---|---|---|---|
| id | bigint unsigned | ○ | ○ | | `$table->id()` |
| user_id | bigint unsigned | | ○ | users.id | `restrictOnDelete()` |
| book_id | bigint unsigned | | ○ | books.id | `cascadeOnDelete()`。books 側は SoftDelete 採用のため実際には発火しない |
| rating | tinyint unsigned | | ○ | | 1〜5範囲は FormRequest で検証。CHECK制約は設けない |
| comment | text | | | | NULL許可（`show.blade.php` の `@if` ガード実測に基づく） |
| created_at / updated_at | timestamp | | | | `$table->timestamps()` |

### review_likes（中間・複合主キー）

| カラム | 型 | PK | NOT NULL | FK | 補足 |
|---|---|---|---|---|---|
| user_id | bigint unsigned | ○（複合） | ○ | users.id | `restrictOnDelete()` |
| review_id | bigint unsigned | ○（複合） | ○ | reviews.id | `cascadeOnDelete()`。reviews は物理削除され得るため従来通り機能する |

`$table->primary(['user_id', 'review_id'])`。timestamps は持たせない。

---

## 9. モデル（`App\Models\Review`）

```php
class Review extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'book_id', 'rating', 'comment'];
    protected $casts = ['rating' => 'integer'];

    public function book()          { return $this->belongsTo(Book::class)->withTrashed(); }
    public function user()          { return $this->belongsTo(User::class); }
    public function likedByUsers()  { return $this->belongsToMany(User::class, 'review_likes'); }
}
```

### `book()` の `withTrashed()` は必須（実装上の最重要注意点）

付け忘れると、参照先の書籍が論理削除された瞬間に `$review->book` が null になり、以下が同時に壊れる。

- `reviews/edit.blade.php` の `{{ $review->book->title }}` が致命的エラーになる
- 同 Blade のキャンセルリンク `route('books.show', $review->book)` が解決できない
- `ReviewController@update` / `@destroy` のリダイレクト先が解決できない

要件シート シート7 の「参照先の書籍が削除済みでも投稿者本人による編集・削除は引き続き操作可能」は、この1行がないと成立しない。

なお `User` 側には `likedReviews()`（`belongsToMany(Review::class, 'review_likes')`）が必要。定義は auth.md にある。

---

## 10. 実装上の注意（CC向け）

- 書籍詳細のレビュー一覧は `created_at` の降順（新しい順）。要件シート・Bladeモックとも並び順の指定がないため、書籍一覧の「最新順」に揃えている。
- N+1 回避のため、書籍詳細では `reviews.user` と `reviews.likedByUsers` を eager load する（`BookController@show`。books.md §2）。
- `Auth::user()->likedReviews` は Blade がレビュー1件ごとに `contains()` を呼ぶ形なので、ユーザー側のリレーションを1回ロードすれば追加クエリは発生しない。
- 中間テーブルは timestamps を持たないので `withTimestamps()` は付けない。
- Blade は改変不要。レビュー・いいね機能はモックの契約どおりに実装できる（削除済み書籍まわりの Blade 追記は books.md §10-1 の管轄）。

---

## 11. シーディング（要件シート シート9／採点直結・改変禁止）

### ReviewSeeder

reviews テーブルに **32件**。5人のユーザーが11冊の書籍に対して投稿する。`rating` は 3〜5 の範囲。各書籍に 2〜4 件を配分。具体的なコメント内容を設定する。`create` を使用。

### ReviewLikeSeeder

review_likes テーブルにいいねデータを投入する。各レビューに 0〜3 人のユーザーがいいねする（**自分のレビューは除く**）。`syncWithoutDetaching` を使用。

実行順は UserSeeder → GenreSeeder → BookSeeder → **ReviewSeeder** → FavoriteSeeder → **ReviewLikeSeeder**。

---

## 12. テスト観点（要件シート シート10）

**全体要件（共通）**: 全テスト通過。`sail artisan test --coverage` で基本機能のみ60%超を目標。

### 単体テスト `tests/Unit/ReviewTest.php`

| # | 検証観点 |
|---|---|
| U-R1 | `Review` の `belongsTo Book` が正しく取得できる |
| U-R2 | `Review` の `belongsTo User` が正しく取得できる |
| U-R3 | `Review` の `belongsToMany User`（review_likes 経由 = `likedByUsers`）が正しく取得できる |
| U-R4 | `Review::book()` が `withTrashed()` を持ち、書籍が論理削除された後も `$review->book` が取得できる |

### 機能テスト `tests/Feature/ReviewTest.php`

| # | 検証観点 |
|---|---|
| F-R1 | 投稿 — `rating` 必須・`comment` 任意で投稿でき、`books.show` へ遷移し「レビューを投稿しました」が表示される |
| F-R2 | 編集の認可 — 投稿者本人は編集でき、それ以外は403になる |
| F-R3 | 削除の認可 — 投稿者本人は削除でき、それ以外は403になる |
| F-R4 | いいねの cascade — レビュー削除時、紐づく `review_likes` も連動して削除される |
| F-R5 | 削除済み書籍下でのレビュー操作 — 参照先の書籍が削除済みでも、投稿者本人による編集・削除が引き続き操作できる |

### 機能テスト `tests/Feature/ReviewLikeTest.php`

| # | 検証観点 |
|---|---|
| F-L1 | トグルON/OFF — 「いいね」ボタンでON/OFFが `review_likes` に正しく反映される |
| F-L2 | 削除済み書籍下でのいいね — 参照先の書籍が削除済みのレビューでも、いいね操作が引き続き可能である |
