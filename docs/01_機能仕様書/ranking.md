# 機能仕様書 — ranking（評価ランキング TOP10）

| 項目 | 内容 |
|---|---|
| 対象発注書 | 04_ジャンルランキング（ランキング部分） |
| 正本 | 要件シート.xlsx シート5/7/10/12 ＋ Bladeモック `basic`（frozen） |
| 適用範囲 | 基本要件のみ |
| 完成条件 | 本書だけで発注書04のランキング部分が書ける（他ファイル参照不要） |
| 版 | v1（2026-09-01・面談②のSoftDelete確定を反映済み） |

---

## 0. スコープ

**含む**: ランキング画面（PG11）の表示と集計仕様、RankingController、ランキングのテスト観点。

**含まない**: 書籍CRUD（books.md）／ジャンルCRUD（genres.md）／レビュー投稿（reviews_likes.md）。

この機能は **読み取り専用**。テーブルの新規追加、バリデーション、Policy、フラッシュ、シーディングはいずれも存在しない。

---

## 1. ルーティング

| # | メソッド | URI | route名 | Controller@Action | 認証 | 認可 |
|---|---|---|---|---|---|---|
| 1 | GET | `/ranking` | `ranking.index` | `RankingController@index` | **不要（公開ページ）** | — |

```php
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
```

`auth` ミドルウェアを付けないこと。ゲストもアクセスできる公開ページである（要件シート シート5・PG11）。ヘッダーナビゲーションの「ランキング」リンクは `route('ranking.index')` を参照しており、名前は変更不可。

`RankingController` は単一アクション。`index` だけを持つ。

---

## 2. 集計仕様（本書の中核）

### 確定ルール

| 項目 | 確定内容 | 出所 |
|---|---|---|
| 並び順 | レビュー平均評価の**降順** | 要件シート シート5・7 |
| 件数 | 上位 **10件** | 要件シート シート5「レビュー平均評価TOP10」 |
| レビュー0件の書籍 | ランキング対象外（表示しない） | 要件シート シート7「レビューがない書籍は表示されない」 |
| 論理削除済みの書籍 | 集計・表示ともに対象外 | 面談②確定（SoftDeletes の標準除外） |
| 同点時の順序 | `id` 昇順を第2ソートキーにする | 正本に定義なし。実行ごとに順位が入れ替わらないよう決定的な順序を与える |

### クエリ（確定）

```php
$rankedBooks = Book::withAvg('reviews', 'rating')
    ->withCount('reviews')
    ->whereHas('reviews')
    ->orderByDesc('reviews_avg_rating')
    ->orderBy('id')
    ->limit(10)
    ->get();
```

- `withAvg('reviews', 'rating')` が `reviews_avg_rating`、`withCount('reviews')` が `reviews_count` を供給する。**どちらも Blade が直接読む属性名**なので、別名を付けてはならない。
- レビュー0件の除外は `whereHas('reviews')` で行う。集計エイリアスに対する `HAVING` は使わない（エイリアス解決がDBに依存するため、最も単純で確実な方法を採る）。
- `Book` が SoftDeletes を持つため、論理削除済みの書籍は自動的に除外される。`withTrashed()` は付けない。
- 平均評価をDBの列として持つことはしない。都度集計する。

---

## 3. 画面契約（Bladeモック実測・改変禁止）

### PG11 ランキング（`ranking/index.blade.php`）

| 要素 | 実測仕様 |
|---|---|
| 渡す変数 | **`$rankedBooks`**（Collection。Paginator ではない） |
| ヘッダ | 「評価ランキング TOP 10」 |
| 0件時の表示 | 「まだレビューが投稿された書籍がありません。」 |
| ループ | `@foreach($rankedBooks as $index => $book)`。順位は `$index + 1` |
| 上位3件の強調 | `$index < 3` で枠色を変更。1位=金、2位=銀、3位=銅のバッジ色分岐あり |
| 各行の表示 | `$book->image_url`（無ければ "No Image"）／`$book->title`（`books.show` へのリンク）／`$book->author` |
| 星表示 | `round($book->reviews_avg_rating)` の値で ★ を5つ塗り分け |
| 平均評価（本文） | `number_format($book->reviews_avg_rating, 2)` — **小数第2位** |
| レビュー件数 | `({{ $book->reviews_count }}件のレビュー)` |
| 平均評価（右バッジ） | `number_format($book->reviews_avg_rating, 1)` — **小数第1位** |
| ページネーション | なし |
| flashスロット | なし |

**変数名は `$books` ではなく `$rankedBooks`。** ここだけ他画面と命名が異なるので取り違えないこと。

同一ページ内で平均評価が小数第2位と第1位の2箇所に出るのは Blade の実装どおり。コントローラー側で丸めてはならない（生の平均値を渡し、丸めは Blade に任せる）。

---

## 4. 画面遷移・フラッシュ文言（要件シート シート7・確定版）

| 操作 | 成功時の遷移先 | フラッシュ文言 | 失敗時 | 認可失敗時 |
|---|---|---|---|---|
| ページを表示する | 当該画面（公開ページ） | — | — | — |
| 書籍タイトルを押す | `books.show` | — | — | — |

flash は発生しない。この画面に `session()` を読むコードは存在しない。

---

## 5. テーブル仕様

**この機能に専用テーブルはない。** 参照するのは既存の books と reviews の2表のみ。

| テーブル | 参照するカラム | 用途 |
|---|---|---|
| books | id / title / author / image_url / deleted_at | 表示と論理削除の除外 |
| reviews | book_id / rating | `reviews_avg_rating` と `reviews_count` の集計元 |

FK・インデックスの追加は不要。

---

## 6. モデル

新規モデルなし。`Book::reviews()`（`hasMany`）と `Book` の `SoftDeletes` トレイトに依存する。定義は books.md にある。

---

## 7. 実装上の注意（CC向け）

- N+1 は発生しない構造（`withAvg` / `withCount` はサブクエリ1回で解決する）。`with('reviews')` を追加してはならない。全レビュー行をメモリに載せることになり、シート3の Eloquent 要件に反する。
- 書籍を論理削除するとランキングから即座に消え、復元すると再び現れる。この往復が要件どおりに動くことを実機で確認すること。
- `limit(10)` は `take(10)` でも可。どちらでも `$rankedBooks` は Collection になる。
- Blade は改変不要。ランキング機能はモックの契約どおりに実装できる。

---

## 8. テスト観点（要件シート シート10）

**全体要件（共通）**: 全テスト通過。`sail artisan test --coverage` で基本機能のみ60%超を目標。

### 機能テスト `tests/Feature/RankingTest.php`

| # | 検証観点 |
|---|---|
| F-K1 | 順位算出 — 平均評価（`reviews_avg_rating`）の降順で TOP10 が表示される |
| F-K2 | レビュー0件書籍の扱い — レビューが1件もない書籍はランキング対象外として扱われる |
| F-K3 | 削除済み書籍の除外 — 論理削除済みの書籍がランキングの集計・表示対象から除外される |
| F-K4 | 公開ページ — 未ログインでも `/ranking` を閲覧できる（画面アクセステスト側と重複可） |
