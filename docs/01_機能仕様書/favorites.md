# 機能仕様書 — favorites（お気に入りトグル・一覧）

| 項目 | 内容 |
|---|---|
| 対象発注書 | 03_レビュー・お気に入り・いいね（お気に入りの部分） |
| 正本 | 要件シート.xlsx シート5/7/9/10/11/12 ＋ Bladeモック `basic`（frozen） |
| 適用範囲 | 基本要件のみ |
| 完成条件 | 本書だけで発注書03のお気に入り部分が書ける（他ファイル参照不要） |
| 版 | v1（2026-09-01・面談②のSoftDelete確定を反映済み） |

---

## 0. スコープ

**含む**: お気に入りのトグル（追加／解除）、お気に入り一覧画面、favorites テーブル、FavoriteSeeder、お気に入りのテスト観点。

**含まない**: レビュー・いいね（reviews_likes.md）／書籍詳細ページ本体（books.md）／全テーブルのmigration統括（auth.md）。

**バリデーションは存在しない**。トグル操作に入力フォームがないため、FormRequest は作らない。

**コントローラーの割り当て（面談①確定）**: お気に入りは `/favorites` という独立した一覧画面を持ち、機能として自立しているため専用の `FavoriteController` を作る。一覧を持たないレビューのいいねは `ReviewController@like` に統合する。判断基準は「独立した一覧画面を持つかどうか」。

---

## 1. ルーティング

| # | メソッド | URI | route名 | Controller@Action | 認証 | 認可 |
|---|---|---|---|---|---|---|
| 1 | GET | `/favorites` | `favorites.index` | `FavoriteController@index` | 必須 | — |
| 2 | POST | `/books/{book}/favorites` | `favorites.toggle` | `FavoriteController@toggle` | 必須 | — |

```php
Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});
```

`favorites.toggle` の `{book}` には `withTrashed()` を**付けない**。削除済み書籍へ直接POSTした場合はルートモデル紐付けが 404 を返し、要件シート シート7 の「削除済みの書籍ではボタン自体が非表示のため操作は発生しない」を構造的に保証する。

Policy は不要（所有者概念がなく、ログイン必須のみ）。

---

## 2. コントローラー仕様（`App\Http\Controllers\FavoriteController`）

| アクション | 処理 |
|---|---|
| `index` | `Auth::user()->favoriteBooks()->latest('books.created_at')->paginate(10)` を `$books` として `favorites.index` ビューへ渡す |
| `toggle` | `Auth::user()->favoriteBooks()->toggle($book)` → `back()`（flashなし） |

- `toggle()` 1メソッドで追加と解除の両方を担う。未登録なら `favorites` に行が作られ、登録済みなら行が削除される。
- `favoriteBooks()` は `Book` を関連先に持ち、`Book` は SoftDeletes を使うため、一覧から論理削除済みの書籍は自動的に除外される。追加の除外処理は不要。

---

## 3. 画面契約（Bladeモック実測・改変禁止部分）

### PG10 お気に入り一覧（`favorites/index.blade.php`）

| 要素 | 実測仕様 |
|---|---|
| 渡す変数 | `$books`（Paginator・10件） |
| 分岐 | `$books->count() > 0` |
| 0件時の表示 | 「お気に入りに登録された書籍はありません。」＋ `books.index` への「書籍一覧を見る」リンク |
| 各カードの表示 | `$book->image_url`（無い場合は "No Image" のプレースホルダ）／`$book->title`（`books.show` へのリンク）／`$book->author`／`ISBN: {{ $book->isbn }}` |
| 解除ボタン | 各カードに `favorites.toggle` への POST フォーム（赤いハートアイコン） |
| ページネーション | `{{ $books->links() }}` |
| flashスロット | **なし** |

一覧に `genres` は表示されないため、`with('genres')` は不要。

### PG02 書籍詳細（`books/show.blade.php`）内のお気に入りボタン

| 要素 | 実測仕様 |
|---|---|
| 判定 | `Auth::user()->favoriteBooks->contains($book->id)` |
| 登録済み | 赤の塗りつぶしハート。`title="お気に入りから削除"` |
| 未登録 | グレーの線画ハート。`title="お気に入りに追加"` |
| 未認証時 | `@else` 側でログイン画面へのリンクアイコンに切り替わる |
| 送信先 | `route('favorites.toggle', $book)` へ POST |

**必須リレーション名（Blade実測・変更不可）**: `Auth::user()->favoriteBooks`。

---

## 4. 画面遷移・フラッシュ文言（要件シート シート7・確定版）

| 操作 | 成功時の遷移先 | フラッシュ文言 | 失敗時 | 認可失敗時 |
|---|---|---|---|---|
| 「お気に入り」ボタンを押す | `back()`（元画面のまま） | **flashなし** | 業務失敗なし（トグル処理） | 未認証: `/login` へリダイレクト |
| お気に入り一覧を表示 | 当該画面 | — | — | 未認証: `/login` へリダイレクト |
| 一覧から書籍タイトルを押す | `books.show` | — | — | — |

### flash を使わない理由（確定・変更禁止）

`favorites/index.blade.php` に `session()` を読むコードが存在しないため、この画面に戻る操作で flash を出しても描画されない。書籍詳細（PG02）には success スロットがあるが、同じトグル操作が2つの画面から呼ばれる以上、片方でだけメッセージが出るのは挙動が揃わない。したがって **お気に入りトグルは flash 不使用に統一**し、状態はハートアイコンの色の変化だけで表現する。レビューのいいねも同じ方針。

`back()` で戻るため、書籍詳細から押せば書籍詳細に、お気に入り一覧から押せばお気に入り一覧に戻る。

---

## 5. テーブル仕様（要件シート シート12）

### favorites（中間・複合主キー）

| カラム | 型 | PK | NOT NULL | FK | 補足 |
|---|---|---|---|---|---|
| user_id | bigint unsigned | ○（複合） | ○ | users.id | `restrictOnDelete()` |
| book_id | bigint unsigned | ○（複合） | ○ | books.id | `cascadeOnDelete()`。論理削除下では発火しない設定だが害はないため残す |

`$table->primary(['user_id', 'book_id'])`。timestamps は持たせない。

複合主キー方式を採用した理由は、Blade が紐付け行そのもの（favorites の id）を直接参照していないため。サロゲートキー + unique 制約は不要。

---

## 6. モデル

`Favorite` モデルは作らない。中間テーブルは `belongsToMany` のピボットとしてのみ扱う。

```php
// App\Models\User
public function favoriteBooks() { return $this->belongsToMany(Book::class, 'favorites'); }

// App\Models\Book
public function favoritedByUsers() { return $this->belongsToMany(User::class, 'favorites'); }
```

- `User` と `Book` の組み合わせだと Laravel の既定ピボット名は `book_user` になるため、**第2引数 `'favorites'` の明示が必須**。
- 中間テーブルは timestamps を持たないので `withTimestamps()` は付けない。
- `favoriteBooks()` の関連先 `Book` が SoftDeletes を持つので、論理削除済みの書籍は自動で除外される。

---

## 7. 削除済み書籍との関係（面談②確定）

| 場面 | 挙動 |
|---|---|
| お気に入り一覧 | 論理削除済みの書籍は表示されない（SoftDeletes の標準除外） |
| 書籍詳細のお気に入りボタン | 削除済みなら非表示（Blade追記。books.md §10-1 の管轄） |
| `favorites` レコード | 書籍を論理削除しても**削除されず保持される**。書籍を復元すればお気に入り一覧に再び現れる |

`favorites.book_id` の `cascadeOnDelete()` は設定として残すが、物理DELETEが発行されないため実際には発火しない死んだ設定になる。害がないため設定自体は変更しない。

---

## 8. 実装上の注意（CC向け）

- 一覧の並び順は `books.created_at` の降順（新しい順）。要件シート・Bladeモックとも指定がないため、書籍一覧（PG01）の「最新順」に揃えている。`latest()` ではなく `latest('books.created_at')` と**テーブル名込みで指定**すること（ピボットとの結合クエリになるため）。
- Blade は改変不要。お気に入り機能はモックの契約どおりに実装できる。
- `toggle()` は追加・解除のどちらでも例外を投げない。業務エラーの分岐を書く必要はない。

---

## 9. シーディング（FavoriteSeeder・要件シート シート9／採点直結・改変禁止）

favorites テーブルにお気に入りデータを投入する。**各ユーザーに 3〜5 冊**のお気に入りを設定する。`syncWithoutDetaching` を使用する。

実行順は UserSeeder → GenreSeeder → BookSeeder → ReviewSeeder → **FavoriteSeeder** → ReviewLikeSeeder。

---

## 10. テスト観点（要件シート シート10）

**全体要件（共通）**: 全テスト通過。`sail artisan test --coverage` で基本機能のみ60%超を目標。

### 単体テスト `tests/Unit/FavoriteTest.php`

| # | 検証観点 |
|---|---|
| U-F1 | お気に入りのリレーション（`User` → `Book` の `belongsToMany`、favorites 経由 = `favoriteBooks`）でトグルが正しく動作する |

### 機能テスト `tests/Feature/FavoriteTest.php`

| # | 検証観点 |
|---|---|
| F-F1 | トグルON — 未登録の書籍でボタンを押すと `favorites` にレコードが作成される |
| F-F2 | トグルOFF — 登録済みの書籍で再度ボタンを押すと `favorites` からレコードが削除される |
| F-F3 | flash非使用 — お気に入り操作で flash メッセージが表示されない |
| F-F4 | 認可 — 未ログインで `/favorites` にアクセスすると `/login` へリダイレクトされる |
| F-F5 | 一覧の範囲 — お気に入り一覧画面に自分が登録した書籍のみが表示される（他ユーザーのお気に入りは出ない） |
