# 機能仕様書 — public_api（公開API AP01〜AP05）

| 項目 | 内容 |
|---|---|
| 対象発注書 | 05_公開APIシーディング |
| 正本 | 要件シート.xlsx シート3/7/8/9/10/13 ＋ Bladeモック `basic`（frozen） |
| 適用範囲 | 基本要件のみ（**認証なし**）。★応用の Sanctum 後付け（AP06）は第6週に本書へ追記する |
| 完成条件 | 本書だけで発注書05が書ける（他ファイル参照不要） |
| 版 | v1（2026-09-01・面談②のSoftDelete確定を反映済み） |

---

## 0. スコープ

**含む**: 公開API 5本（AP01〜AP05）のルート・コントローラー・API Resource・バリデーション・レスポンス構造・ステータスコード、API専用のエラーハンドリング、DatabaseSeeder の実行順、公開APIのテスト観点。

**含まない**: Web画面側の実装（books.md ほか）／各Seederの中身（本書 §8 に所在を明記）。

**認可（Policy）は基本段階では存在しない**。5本すべて認証なしで動作するため、`$this->authorize()` は1箇所も呼ばない。認可が入るのは応用の Sanctum 後付け以降。

**アーキテクチャ（シート3）**: 本プロジェクトは Traditional Web（Blade + セッション認証）に加えて外部アプリ向けの公開API（JSON）を持つ。API は `routes/api.php` と `App\Http\Controllers\Api\V1` 名前空間を使う。基本段階は**認証なしのCRUD**として実装し、応用段階で Sanctum によるトークン認証を後付けする。

---

## 1. ルーティング

| ID | メソッド | URI | Controller@Action | 認証（基本） | 認証（★応用） |
|---|---|---|---|---|---|
| AP01 | GET | `/api/v1/books` | `Api\V1\BookController@index` | 不要 | 不要 |
| AP02 | GET | `/api/v1/books/{book}` | `Api\V1\BookController@show` | 不要 | 不要 |
| AP03 | POST | `/api/v1/books` | `Api\V1\BookController@store` | 不要 | ★ Sanctum 必須 |
| AP04 | PUT | `/api/v1/books/{book}` | `Api\V1\BookController@update` | 不要 | ★ Sanctum + BookPolicy（所有者のみ） |
| AP05 | DELETE | `/api/v1/books/{book}` | `Api\V1\BookController@destroy` | 不要 | ★ Sanctum + BookPolicy（所有者のみ） |

```php
// routes/api.php（'api' プレフィックスは RouteServiceProvider が自動付与）
Route::prefix('v1')->group(function () {
    Route::apiResource('books', \App\Http\Controllers\Api\V1\BookController::class);
});
```

`withTrashed()` は**どのルートにも付けない**。論理削除済みの書籍IDを指定した AP02 / AP04 / AP05 は 404 を返すのが確定仕様。

---

## 2. 共通仕様（要件シート シート13・確定版）

| 項目 | 確定内容 |
|---|---|
| ページネーション | 既定 `per_page=10`。クライアント指定時は 1〜100 の範囲で上書き可 |
| `average_rating` の丸め | 小数第1位（`round($value, 1)`）。レビュー0件のときは `0` |
| 日付形式（date型） | `Y-m-d`（例: `2012-06-23`） |
| 日時形式（datetime型） | ISO8601・JSTオフセット付き（例: `2026-08-01T10:00:00+09:00`）。`toIso8601String()` を使う |
| バリデーションエラー形式 | `{"message": string, "errors": {field: [string, ...]}}` |
| 存在しないIDのエラー形式 | `{"message": string}`（`errors` キーなし） |
| ジャンル指定（POST/PUT） | フィールド名は `genres`（配列）。Web版の `genres[]` と同一フィールド名・同一ルール |
| レスポンス整形 | 必ず API Resource クラスを使う |

JSTオフセットを出すには `config/app.php` の `timezone` を `Asia/Tokyo` にしておく必要がある（auth.md §5-2 で設定済み）。

### ステータスコード早見表

| コード | 意味 | 該当 |
|---|---|---|
| 200 | 取得・更新成功 | AP01, AP02, AP04 |
| 201 | 新規作成成功 | AP03 |
| 204 | 削除成功（本文なし） | AP05 |
| 404 | 対象が存在しない、または論理削除済み | AP02, AP04, AP05 |
| 422 | バリデーションエラー | AP01, AP03, AP04 |

---

## 3. エンドポイント仕様

### AP01: 書籍一覧API — `GET /api/v1/books`

キーワード検索・ジャンル絞り込み・ページネーションに対応する。各書籍にジャンル情報・平均評価・レビュー件数を含める。

**リクエストパラメータ**

| パラメータ | ルール | エラー文言 |
|---|---|---|
| `keyword` | `nullable, string, max:255` | 検索キーワードは255文字以内で入力してください |
| `genre_id` | `nullable, integer, exists:genres,id` | 指定されたジャンルが存在しません |
| `page` | `nullable, integer, min:1` | ページ番号は1以上の整数で指定してください |
| `per_page` | `nullable, integer, min:1, max:100`（既定10） | 取得件数は1〜100の範囲で指定してください |

例: `GET /api/v1/books?genre_id=3&page=1`

**レスポンス（200・genre_id=3 で絞り込んだ実データ例）**

```json
{
  "data": [
    {"id": 3, "title": "リーダブルコード", "author": "Dustin Boswell", "isbn": "9784873115658", "published_date": "2012-06-23", "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=3", "average_rating": 4.5, "reviews_count": 3, "genres": [{"id": 3, "name": "技術書"}]},
    {"id": 7, "title": "Clean Code", "author": "Robert C. Martin", "isbn": "9784048930598", "published_date": "2017-12-18", "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=7", "average_rating": 4.0, "reviews_count": 2, "genres": [{"id": 3, "name": "技術書"}]}
  ],
  "links": {"first": "...?genre_id=3&page=1", "last": "...?genre_id=3&page=1", "prev": null, "next": null},
  "meta": {"current_page": 1, "last_page": 1, "per_page": 10, "total": 2}
}
```

- **`description` は一覧に含めない**（詳細APIのみで返す。一覧ペイロードを軽くするための確定仕様）。
- `links` / `meta` は Laravel の `AnonymousResourceCollection` が `paginate()` から自動生成する構造をそのまま使う（URLは実行環境の絶対URLになる）。
- 検索条件をページ送りに引き継ぐため `->withQueryString()` を付ける。

**レスポンス（422・パラメータ不正例）**

```json
{"message": "入力内容に誤りがあります。", "errors": {"per_page": ["取得件数は1〜100の範囲で指定してください"]}}
```

**ステータスコード**: 200（該当0件でも `data: []` で200）／422。

---

### AP02: 書籍詳細API — `GET /api/v1/books/{book}`

ジャンル情報とレビュー（投稿者名・評価・コメント・投稿日時）を含める。

**レスポンス（200・id=3）**

```json
{
  "data": {
    "id": 3, "title": "リーダブルコード", "author": "Dustin Boswell", "isbn": "9784873115658", "published_date": "2012-06-23",
    "description": "より良いコードを書くためのシンプルで実践的なテクニックを解説する一冊。",
    "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=3", "average_rating": 4.5, "reviews_count": 3,
    "genres": [{"id": 3, "name": "技術書"}],
    "reviews": [
      {"user_name": "佐藤美咲", "rating": 5, "comment": "変数名の付け方だけでも読む価値がありました。", "created_at": "2026-08-01T10:00:00+09:00"},
      {"user_name": "高橋健太", "rating": 4, "comment": null, "created_at": "2026-08-05T12:30:00+09:00"},
      {"user_name": "田中一郎", "rating": 5, "comment": "新人研修の課題図書にしたいレベル。", "created_at": "2026-08-10T09:15:00+09:00"}
    ]
  }
}
```

- `comment` の null 許容は DB設計（`reviews.comment` NULL許可）と一致する。
- `user_name` は `reviews.user` の eager load から取る（`books/show.blade.php` と同じリレーション経路）。
- description 本文とレビュー comment 本文は例示テキスト。数値・ID・タイトルという構造化された項目のみ実データ準拠。

**レスポンス（404・存在しないID／論理削除済みIDも同様）**

```json
{"message": "指定された書籍が見つかりません。"}
```

**ステータスコード**: 200／404（存在しない、または論理削除済み）。

---

### AP03: 書籍登録API — `POST /api/v1/books`

**バリデーション**（Web版 `books.store` と同一ルール ＋ `user_id` 追加）

| フィールド | ルール | メッセージ |
|---|---|---|
| `title` | `required, string, max:255` | タイトルを入力してください／タイトルは255文字以内で入力してください |
| `author` | `required, string, max:255` | 著者名を入力してください／著者名は255文字以内で入力してください |
| `isbn` | `required, string, regex:/^[0-9]{13}$/, unique:books,isbn` | ISBNを入力してください／ISBNは13桁の数字で入力してください／このISBNは既に登録されています |
| `published_date` | `required, date` | 出版日を入力してください／出版日は正しい日付形式で入力してください |
| `description` | `nullable, string, max:1000` | 説明は1000文字以内で入力してください |
| `image_url` | `nullable, url, max:255` | 画像URLの形式が正しくありません／画像URLは255文字以内で入力してください |
| `genres` | `required, array, min:1` | ジャンルを1つ以上選択してください |
| `genres.*` | `exists:genres,id` | 選択されたジャンルが存在しません |
| `user_id` | `required, integer, exists:users,id` | 登録者IDを指定してください／指定された登録者が存在しません |

`user_id` が必須なのは、基本段階は認証なしで `Auth::id()` が使えないため。応用段階で Sanctum を導入したら `Auth::id()` 取得方式に切り替える。

**リクエスト例**

```json
{"title": "プログラマー脳", "author": "Felienne Hermans", "isbn": "9784798068718", "published_date": "2023-01-01", "description": "コードを読み書きする際に脳内で何が起きているかを認知科学の観点から解説する。", "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=12", "genres": [3], "user_id": 3}
```

**レスポンス（201）**: AP02 の `data` 形式から `reviews` キーを除いた形。**`user_id` はレスポンスに含めない**。

```json
{"data": {"id": 12, "title": "プログラマー脳", "author": "Felienne Hermans", "isbn": "9784798068718", "published_date": "2023-01-01", "description": "コードを読み書きする際に脳内で何が起きているかを認知科学の観点から解説する。", "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=12", "average_rating": 0, "reviews_count": 0, "genres": [{"id": 3, "name": "技術書"}]}}
```

**レスポンス（422）**

```json
{"message": "入力内容に誤りがあります。", "errors": {"title": ["タイトルを入力してください"], "isbn": ["このISBNは既に登録されています"]}}
```

**ステータスコード**: 201／422。

---

### AP04: 書籍更新API — `PUT /api/v1/books/{book}`

AP03 の全項目を同一ルールで適用する。差分は `isbn` のみ。

| フィールド | ルール | メッセージ |
|---|---|---|
| `isbn` | `required, string, regex:/^[0-9]{13}$/, unique:books,isbn,{book},id`（自身のレコードを除外） | ISBNは13桁の数字で入力してください／このISBNは既に登録されています |

`user_id` も AP03 と同一ルールでバリデーション対象。リクエスト例は AP03 と同一形式。

**レスポンス**: 200 は AP02 の `data` 形式（`reviews` キーを除く）／404 は `{"message": "指定された書籍が見つかりません。"}`／422 は AP03 と同一形式。

**ステータスコード**: 200／404／422。

---

### AP05: 書籍削除API — `DELETE /api/v1/books/{book}`

**レスポンス**: 204（本文なし）／404 は `{"message": "指定された書籍が見つかりません。"}`。

**ステータスコード**: 204／404。

**関連データの扱い（面談②反映・確定）**: 書籍は物理削除ではなく論理削除される。`$book->delete()` が `deleted_at` をセットする。`reviews` / `favorites` / `book_genre` のレコードは削除されず保持される（`favorites` / `book_genre` 起点の `cascadeOnDelete()` は論理削除下では発火しない）。204/404 というレスポンス形状は cascade 方式でも SoftDelete 方式でも変わらないため、外部から見た挙動に差はない。

---

## 4. コントローラー仕様（`App\Http\Controllers\Api\V1\BookController`）

| アクション | 処理 |
|---|---|
| `index` | `IndexBookRequest` で検証 → `Book::with('genres')->withAvg('reviews','rating')->withCount('reviews')` に `keyword`（title または author の部分一致）と `genre_id`（`whereHas('genres', ...)`）を条件付与 → `latest()->paginate($perPage)->withQueryString()` → `BookListResource::collection(...)` |
| `show` | `$book->load(['genres', 'reviews.user'])->loadAvg('reviews','rating')->loadCount('reviews')` → `new BookResource($book)` |
| `store` | `StoreApiBookRequest` で検証 → `Book::create(...)` → `genres()->sync($request->genres)` → `new BookResource($book->load('genres')->loadAvg(...)->loadCount(...))` を 201 で返す |
| `update` | `UpdateApiBookRequest` で検証 → `$book->update(...)` → `genres()->sync(...)` → `new BookResource(...)` を 200 で返す |
| `destroy` | `$book->delete()` → `response()->noContent()`（204） |

- `keyword` は基本段階の要件シート シート7 では★応用扱いだが、シート8・シート13 で AP01 のリクエストパラメータとして基本段階から確定しているため、**API側は基本段階で実装する**（Web画面側の検索フォームは応用）。
- `store` / `update` は `DB::transaction()` で書籍保存とジャンル sync を1単位にする。
- `Book` の SoftDeletes により、`index` は論理削除済みを自動除外する。`show` / `update` / `destroy` はルートモデル紐付けが論理削除済みを解決できず 404 になる。追加の分岐は不要。

---

## 5. API Resource クラス

| クラス | 用途 | 含めるキー |
|---|---|---|
| `BookListResource` | AP01 | id, title, author, isbn, published_date, image_url, average_rating, reviews_count, genres |
| `BookResource` | AP02 / AP03 / AP04 | 上記 ＋ description、および `reviews`（`whenLoaded('reviews')`） |
| `GenreResource` | 上記の `genres` 要素 | id, name |
| `ReviewResource` | AP02 の `reviews` 要素 | user_name, rating, comment, created_at |

実装のポイント。

```php
'published_date' => $this->published_date->format('Y-m-d'),
'average_rating' => round((float) $this->reviews_avg_rating, 1),
'reviews_count'  => $this->reviews_count,
'genres'         => GenreResource::collection($this->whenLoaded('genres')),
'reviews'        => ReviewResource::collection($this->whenLoaded('reviews')),
// ReviewResource 側
'user_name'  => $this->user->name,
'created_at' => $this->created_at->toIso8601String(),
```

`reviews` を `whenLoaded()` にすることで、AP03 / AP04 のレスポンスからキーごと消える（要件シートの 201 レスポンス例に `reviews` が無いことと一致する）。

`average_rating` は数値で返す。レビュー0件のときは `reviews_avg_rating` が null になるので `(float)` キャストで `0` になる。JSON の数値表現上、`4.0` は `4` として出力され得るが、値は要件シートの例と一致する。

---

## 6. バリデーション（FormRequest）

すべて `App\Http\Requests\Api\V1` 名前空間に置き、FormRequest に分離する（シート3・シート8の要件）。

| クラス | 対象 | ルール |
|---|---|---|
| `IndexBookRequest` | AP01 | §3 AP01 の表 |
| `StoreApiBookRequest` | AP03 | §3 AP03 の表 |
| `UpdateApiBookRequest` | AP04 | AP03 と同一。`isbn` のみ `->ignore($this->route('book'))` |

### 422 レスポンスの日本語化（必須）

Laravel 標準の 422 は `message` が英語（`The given data was invalid.`）になる。確定仕様の `入力内容に誤りがあります。` にするため、共通の基底クラスを1本作り、上記3クラスはこれを継承する。

```php
abstract class ApiFormRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => '入力内容に誤りがあります。',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
```

---

## 7. 404 レスポンスの日本語化（必須）

ルートモデル紐付けが失敗すると Laravel は `ModelNotFoundException` → 404 を返すが、既定の `message` は `No query results for model [App\Models\Book] 3` になる。確定仕様の `指定された書籍が見つかりません。` にするため、`App\Exceptions\Handler` の `register()` で API リクエストのときだけ差し替える。

```php
$this->renderable(function (NotFoundHttpException $e, Request $request) {
    if ($request->is('api/*')) {
        return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
    }
});
```

`errors` キーは付けない（存在しないIDのエラー形式は `{"message": string}` のみと確定）。

---

## 8. シーディング（要件シート シート9／採点直結・改変禁止）

発注書05はシーディングを同梱スコープに持つ。**指定データは1文字も変えないこと**（忘れると再提出または大幅減点の明記あり）。

### DatabaseSeeder の実行順

1. `UserSeeder` — users 5件（auth.md §10）
2. `GenreSeeder` — genres 10件（genres.md §10）
3. `BookSeeder` — books 11件（books.md §11）
4. `ReviewSeeder` — reviews 32件（reviews_likes.md §11）
5. `FavoriteSeeder` — favorites（favorites.md §9）
6. `ReviewLikeSeeder` — review_likes（reviews_likes.md §11）

`sail artisan db:seed` でまとめて投入できるようにする。

### 各Seederの要点（一覧）

| Seeder | 件数・内容 | 使用メソッド |
|---|---|---|
| UserSeeder | 山田太郎 / 鈴木花子 / 田中一郎 / 佐藤美咲 / 高橋健太 の5件。パスワードは全員 `password` | `firstOrCreate`（email重複防止）＋ `Hash::make()` |
| GenreSeeder | 小説・ビジネス・技術書・自己啓発・エッセイ・歴史・科学・芸術・料理・旅行 の10件 | `firstOrCreate`（name重複防止） |
| BookSeeder | 11件。登録者は `User::first()`。`image_url` は `https://placehold.co/200x300/e2e8f0/475569?text={番号}` | `firstOrCreate`（ISBN重複防止）＋ `genres()->sync()` |
| ReviewSeeder | 32件。5人が11冊にレビュー。`rating` は 3〜5。各書籍に2〜4件を配分 | `create` |
| FavoriteSeeder | 各ユーザーに3〜5冊 | `syncWithoutDetaching` |
| ReviewLikeSeeder | 各レビューに0〜3人（自分のレビューは除く） | `syncWithoutDetaching` |

シーディング完了後、`genre_id=3` は「技術書」、`id=3` の書籍は「リーダブルコード」になる。AP01 / AP02 のレスポンス例はこの前提で書かれている。

---

## 9. 実装上の注意（CC向け）

- 名前空間は `App\Http\Controllers\Api\V1\BookController`。Web版の `App\Http\Controllers\BookController` と**同名クラスが2つ存在する**ので、`use` 文の取り違えに注意する。
- `routes/api.php` に `v1` プレフィックスを付ける。`api` プレフィックスは RouteServiceProvider が自動で付けるので、`Route::prefix('api/v1')` と書くと `/api/api/v1` になる。
- 基本段階では Policy を一切適用しない（認証なしのため）。応用の Sanctum 後付けで初めて `BookPolicy` を API に接続する。
- 応用で `personal_access_tokens` テーブルを作る。基本段階では作らない。
- API はレスポンスがすべて JSON。flash / リダイレクトの概念は存在しない。

---

## 10. テスト観点（要件シート シート10）

**全体要件（共通）**: 全テスト通過。`sail artisan test --coverage` で基本機能のみ60%超を目標。

### 機能テスト `tests/Feature/Api/BookApiTest.php`

| # | 検証観点 |
|---|---|
| F-P1 | AP01 一覧・正常系 — パラメータなしで200が返り、`per_page=10` のページネーションが既定で適用される |
| F-P2 | AP01 一覧・異常系 — `keyword` / `genre_id` / `page` / `per_page` のいずれかが不正な場合 422 が返る |
| F-P3 | AP02 詳細・正常系 — 存在するIDで200が返り、`comment` が null のレビューを含んでも正しく返る |
| F-P4 | AP02 詳細・存在しないID — 404 が返る |
| F-P5 | AP02 詳細・削除済みID — 論理削除済みの書籍IDを指定すると 404 が返る |
| F-P6 | AP03 新規登録 — 正しいデータで 201、バリデーション違反で 422 が返る |
| F-P7 | AP04 更新・正常系 — 正しいデータで 200 が返り、ISBN一意性チェックが自身のレコードを除外する |
| F-P8 | AP04 更新・異常系 — 存在しないIDで 404、バリデーション違反で 422 が返る |
| F-P9 | AP05 削除 — 204（本文なし）が返り、対象書籍が物理削除ではなく論理削除扱いになる |
| F-P10 | AP05 存在しないID — 404 が返る |

### 機能テスト `tests/Feature/SeederTest.php`（推奨）

| # | 検証観点 |
|---|---|
| F-P11 | `db:seed` 実行後に users 5件・genres 10件・books 11件・reviews 32件が投入されている |
| F-P12 | `db:seed` を2回実行しても `firstOrCreate` により重複が発生しない |
