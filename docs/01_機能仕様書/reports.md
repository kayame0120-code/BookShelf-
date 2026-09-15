# 機能仕様書 — reports（マイ読書レポート）

| 項目 | 内容 |
|---|---|
| 対象発注書 | 09_読書レポート |
| 正本 | 要件シート.xlsx シート5/7/10/12 ＋ Bladeモック `advanced`（frozen） |
| 適用範囲 | 応用要件。ログインユーザーの読書統計（4種）の集計と表示、テスト観点 |
| 完成条件 | 本書だけで発注書09が書ける（他ファイル参照不要） |

マイ読書レポートは、ログインユーザー自身のレビュー活動を4種類の統計にまとめて表示する画面。書き込み操作はなく、集計と表示のみ。専用のテーブルは持たず、既存の reviews / books / genres / book_genre から集計する。

---

## 0. スコープ

**含む**: `/reports` 画面の表示、4種の統計（基本サマリー・評価分布・高評価書籍TOP5・ジャンル別評価傾向TOP5）の集計ロジック、削除済み書籍の扱い、以上のテスト観点。

**含まない**: レビューのCRUD（reviews_likes.md）／書籍のCRUD（books.md）／ランキング画面（ranking.md）。ランキングとレポートは削除済み書籍の扱いが意図的に異なる（§3-0）。

書き込み系の操作・フォーム・バリデーションは存在しない。認可も所有者判定は不要で、ログイン必須のみ。

---

## 1. ルーティング

`routes/web.php`。認証必須。

| # | メソッド | URI | route名 | Controller@Action | 認証 | 認可 |
|---|---|---|---|---|---|---|
| 1 | GET | `/reports` | `reports.index` | `ReportController@index` | 必須 | — |

```php
Route::middleware('auth')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
```

- 単一の GET のみ。`reports.*` でナビゲーションのアクティブ判定を行うため（`layouts/navigation.blade.php` の `request()->routeIs('reports.*')`）、route名は `reports.index` とする。

---

## 2. 画面契約（Bladeモック実測・改変禁止）

| 画面ID | Bladeファイル | 渡す変数 | Blade が要求する構造 |
|---|---|---|---|
| PG14 | `reports/index.blade.php` | `$stats`（配列） | 下表のとおり |

`$stats` は1つの配列にまとめて渡す。Blade は `$stats['summary']` `$stats['rating_distribution']` `$stats['top_rated_books']` `$stats['genre_ratings']` の4キーを読む。

| キー | Blade が要求する構造 | 要求される型・メソッド |
|---|---|---|
| `$stats['summary']['total_reviews']` | 総レビュー数 | 整数（そのまま表示） |
| `$stats['summary']['books_read']` | 読了冊数 | 整数 |
| `$stats['summary']['average_rating']` | 平均評価 | 数値。`> 0` の判定と `number_format($v, 1)` に渡す。0のとき「-」表示 |
| `$stats['rating_distribution']` | 評価分布 | **Collection**。`->max()` を呼ぶ。`@foreach ($stats['rating_distribution'] as $index => $count)` で回し、`$index + 1` を星数、`$count` を件数として使う。要素数5・インデックス0が星1〜インデックス4が星5 |
| `$stats['top_rated_books']` | 高評価書籍TOP5 | 配列/Collection。`count()` で件数判定。各要素は `$book['id']` `$book['title']` `$book['author']` `$book['rating']`。`rating` は `str_repeat('★', $book['rating'])` に渡す（**整数必須**） |
| `$stats['genre_ratings']` | ジャンル別評価傾向TOP5 | 配列/Collection。`count()` で件数判定。各要素は `$genre['id']` `$genre['name']` `$genre['count']` `$genre['average_rating']`。`average_rating` は `number_format($genre['average_rating'], 1)` に渡す |

**Blade の実測ポイント**

- 評価分布は各バーの幅を `($count / $maxCount) * 100` で計算する。`$maxCount = $stats['rating_distribution']->max() ?: 1`。**`rating_distribution` は Collection でなければ `->max()` が呼べない**。配列で渡すとエラーになるため、`collect()` で包んで渡す。
- 高評価書籍TOP5は各要素の `$book['rating']` を `str_repeat('★', $book['rating'])` と `str_repeat('☆', 5 - $book['rating'])` に渡す。**`rating` は整数**でなければ `str_repeat` の第2引数として使えない。平均値（小数）を渡してはならない（§3-3）。
- 高評価書籍TOP5から `route('books.show', $book['id'])` へ、ジャンル別TOP5から `route('genres.show', $genre['id'])` へリンクする。id を各要素に含める。
- 該当0件のとき、高評価書籍TOP5は「4星以上の書籍がありません」、ジャンル別TOP5は「ジャンルが設定された書籍のレビューがありません」を表示する（Blade側の空メッセージ。コントローラーは空配列を渡すだけ）。

**変数名の対応**: `$stats`（1つの配列）。4種の統計を個別変数で渡さない。

---

## 3. コントローラー仕様（`App\Http\Controllers\ReportController`）

| アクション | 処理 |
|---|---|
| `index` | ログインユーザーのレビューから4種の統計を集計し、`$stats` 配列として `reports.index` ビューへ渡す |

集計はすべて**ログインユーザー自身のレビュー**（`reviews.user_id = Auth::id()`）を対象とする。他ユーザーのレビューは一切含めない。

### 3-0. 削除済み書籍の扱い（本書の中核・ランキングとの相違）

**マイ読書レポートは、削除済み書籍に対する自分のレビューも4種の統計すべてに含める**（要件シート シート7 行49・確定）。ランキング画面（PG11）が削除済み書籍を集計から除外するのと**意図的に異なる**。レポートは「自分がこれまで何を読み、どう評価してきたか」の記録であり、後から書籍が削除されても自分の読書実績は消えないという設計。

- レビューから書籍をたどるすべての集計クエリで、書籍を `withTrashed()` で解決する（CLAUDE.md 9-1 は `top_rated_books` を明記するが、削除済みを含める方針は4種の統計全体に適用する。ジャンル別集計で book_genre をたどる場合も同様）。
- レビュー自体は物理削除されるテーブルのため、`reviews.user_id = Auth::id()` で取得したレビューはすべて生きているレビュー。除外は不要。除外するのは「書籍が削除済みかどうか」で、それを**除外しない**のが本機能の要件。

### 3-1. 基本サマリー（`summary`）

| 項目 | 定義 | 実装 |
|---|---|---|
| `total_reviews` | 自分の総レビュー数 | `Review::where('user_id', Auth::id())->count()` |
| `books_read` | 読了冊数＝レビューしたユニーク書籍数 | `Review::where('user_id', Auth::id())->distinct('book_id')->count('book_id')` |
| `average_rating` | 自分のレビューの平均評価 | `Review::where('user_id', Auth::id())->avg('rating')`。レビュー0件なら null が返るので `0` に丸める |

- `average_rating` はレビュー0件のとき 0 とする。Blade が `> 0` で判定し、0なら「-」を表示する。
- `books_read` は書籍が削除済みかどうかを問わない（reviews に紐づく book_id の種類数を数えるだけで、books テーブルを参照しないため、SoftDelete の影響を受けない）。

### 3-2. 評価分布（`rating_distribution`）

自分のレビューを評価値（1〜5）ごとに件数集計する。

- 結果は**要素数5の Collection**。インデックス0が星1の件数、インデックス4が星5の件数。
- 該当する評価が0件でも、その星の件数は0として要素を必ず持たせる（5要素固定）。歯抜けにしない。
- 実装例: 1〜5の各値について件数を数え、`collect([$c1, $c2, $c3, $c4, $c5])` の形で渡す。または `groupBy('rating')` の結果を1〜5で埋め直して Collection にする。
- Blade が `->max()` を呼ぶため、必ず Collection で渡す（配列不可）。
- 書籍の削除状態は影響しない（rating の集計はレビューのみを見るため）。

### 3-3. 高評価書籍TOP5（`top_rated_books`）

自分が4以上を付けた書籍を、評価の高い順に最大5件返す。

| 要素キー | 内容 |
|---|---|
| `id` | 書籍ID（`route('books.show', ...)` 用） |
| `title` | 書籍タイトル |
| `author` | 著者 |
| `rating` | **自分がその書籍に付けた評価値（整数）**。`str_repeat('★', $rating)` に渡すため整数 |

- 対象は「自分のレビューのうち `rating >= 4`」。書籍ごとに評価の高い順に並べ、上位5件。
- 書籍は `withTrashed()` で解決する。削除済み書籍への自分の高評価レビューも TOP5 に含める（§3-0）。
- `rating` は平均値ではなく、そのレビューの評価値そのもの（整数）。同一書籍に自分の複数レビューがある場合は、最も高い評価を採用する（`str_repeat` に渡す都合上、単一の整数に定める必要があるため）。
- 同点の場合の順序は書籍ID昇順で安定させる。
- 4以上のレビューが1件もなければ空配列を渡す。Blade が「4星以上の書籍がありません」を表示する。
- Collection メソッド（`filter` / `sortByDesc` / `take` / `map`）で組み立てる。`foreach` の手続き的ループは避ける（CLAUDE.md 応用フェーズ追加ルール「Collectionメソッド活用」・マイ読書レポートで特に徹底）。

### 3-4. ジャンル別評価傾向TOP5（`genre_ratings`）

自分のレビューを、書籍のジャンルごとに平均評価と件数で集計し、平均の高い順に最大5件返す。

| 要素キー | 内容 |
|---|---|
| `id` | ジャンルID（`route('genres.show', ...)` 用） |
| `name` | ジャンル名 |
| `count` | そのジャンルに属する書籍への自分のレビュー件数 |
| `average_rating` | そのジャンルの平均評価（`number_format($v, 1)` に渡す。小数で可） |

- 自分のレビュー → 書籍 → book_genre → ジャンル とたどり、ジャンル単位で集計する。1冊が複数ジャンルに属する場合、そのレビューは各ジャンルの集計に計上される（book_genre が多対多のため、ジャンルごとに重複カウントされる）。
- 書籍は `withTrashed()` で解決する。削除済み書籍への自分のレビューもジャンル集計に含める（§3-0）。
- `average_rating` はそのジャンルに属する書籍への自分のレビューの平均。件数で割る。
- 平均の高い順に上位5件。同点はジャンルID昇順で安定させる。
- ジャンルが設定された書籍へのレビューが1件もなければ空配列を渡す。Blade が「ジャンルが設定された書籍のレビューがありません」を表示する。
- Collection メソッドで組み立てる（`groupBy` / `map` / `sortByDesc` / `take`）。

### 3-5. $stats の組み立て

```php
public function index(): View
{
    $userId = Auth::id();

    $stats = [
        'summary'             => $this->buildSummary($userId),
        'rating_distribution' => $this->buildRatingDistribution($userId), // Collection（5要素）
        'top_rated_books'     => $this->buildTopRatedBooks($userId),      // 配列/Collection（最大5・rating整数）
        'genre_ratings'       => $this->buildGenreRatings($userId),       // 配列/Collection（最大5）
    ];

    return view('reports.index', compact('stats'));
}
```

- 集計ロジックはコントローラーが肥大化するようであればサービスクラスに切り出してよい（CLAUDE.md コントローラーの責務）。切り出す場合も返す構造は上記のとおり。
- 書籍をたどる各メソッドは `Book::withTrashed()` あるいはリレーションに `->withTrashed()` を効かせて、削除済みを含める。

---

## 4. モデル・テーブル

専用テーブルは持たない。既存の reviews / books / genres / book_genre を集計する。

- Book モデルは SoftDeletes を持つため、レビューから書籍をたどる際は明示的に `withTrashed()` を効かせないと削除済み書籍が除外される（§3-0）。`Review::book()` リレーションに `->withTrashed()` が付いていること（books.md §10-3・reviews_likes.md）を前提にできるが、レポートの集計クエリでは念のため書籍解決箇所で `withTrashed()` を明示する。
- 集計に必要なリレーション（`Review::user()` `Review::book()` `Book::genres()`）は既存のものを使う。本書で新規リレーションは定義しない。

---

## 5. 画面遷移・フラッシュ文言（要件シート シート7・確定版）

| 操作 | 成功時の遷移先 | フラッシュ文言 | 失敗時 | 認可失敗時 |
|---|---|---|---|---|
| レポートを表示 | 当該画面 | — | レビュー0件でも0件として基本統計を表示。TOP5・ジャンル別TOP5は該当がなければBladeの空メッセージを表示 | 未認証: `/login` へ |

書き込み操作がないため、フラッシュ・バリデーションエラーは発生しない。

---

## 6. テスト観点（要件シート シート10「マイ読書レポート」）

**全体要件（共通）**: 全テスト通過。`sail artisan test --coverage` で応用機能込み80%以上を目標。

### 機能テスト `tests/Feature/ReportTest.php`

| # | 検証観点 |
|---|---|
| F-R1 | 認可 — 未ログインで `GET /reports` にアクセスすると `/login` へリダイレクトされる |
| F-R2 | 集計範囲 — ログインユーザー自身のレビューのみが集計対象であり、他ユーザーのレビューが混入しない |
| F-R3 | 基本統計 — 総レビュー数・読了冊数（レビューしたユニーク書籍数）・平均評価が自分のレビューから正しく算出される |
| F-R4 | レビュー0件時の挙動 — 総レビュー数0・読了冊数0で画面がエラーにならず、平均評価が0のため「-」が表示される |
| F-R5 | 評価分布 — 1〜5星それぞれの件数が返り、要素数5のコレクションとしてインデックス0が星1・インデックス4が星5に対応する |
| F-R6 | 高評価書籍TOP5 — 自分が4以上を付けた書籍のみが評価の高い順に最大5件返り、各要素が id・title・author・rating を持ち、rating が整数である |
| F-R7 | 高評価書籍の該当なし — 4以上のレビューが1件もない場合はTOP5が空になり「4星以上の書籍がありません」が表示される |
| F-R8 | 削除済み書籍の包含 — 論理削除済みの書籍に対する自分のレビューも集計・TOP5・リンクに含まれる（ランキング画面が削除済みを除外するのと意図的に異なる） |
| F-R9 | ジャンル別評価傾向TOP5 — ジャンルごとの平均評価と件数が平均の高い順に最大5件返り、各要素が id・name・count・average_rating を持つ |
| F-R10 | ジャンル別の該当なし — ジャンルが設定された書籍へのレビューが1件もない場合は空になり「ジャンルが設定された書籍のレビューがありません」が表示される |
| F-R11 | リンク — 高評価書籍TOP5から書籍詳細へ、ジャンル別TOP5からジャンル詳細へ遷移できる |
