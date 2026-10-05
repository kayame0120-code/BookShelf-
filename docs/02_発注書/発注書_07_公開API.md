# 発注書 07 公開API

書籍の一覧・詳細・登録・更新・削除の公開API 5本、トークン認証、エラー時のJSON応答、呼び出し回数の上限を担当する。画面は持たない。

- 合格の基準: `docs/03_検品表/07_公開API.md`（この番号の全条件を1条件1行で並べた表）。この発注書に書かれていても検品表に無い作業は完成条件に含まれない
- 共通の規則: `docs/共通ルール.md` の1章（着手前の確認・書かれていないことをしない・定義が無い場面での止まり方・変更してよい範囲・コミット・完了報告）と3章（文書の書き方）
- 提供モック・要件シート: 学校から配布された資料。提供モックは画面のひな形（Blade）、要件シートは作るものを定めた表で、所在と、実装との関係は `docs/00_提供資料/提供資料の所在.md` にある
- 材料: 機能仕様書 7章（公開API）、全体仕様書 4章（ルート一覧）・8章（公開APIの一覧・エラー・呼び出し回数）、技術決定書 TD-07・TD-08・TD-11・TD-20

---

## 1. 前提

着手の前に、次が存在することを現物で確かめる。

| 前提 | 確かめる対象 |
|---|---|
| Laravel Sanctum 3.3.3 が入っている | `composer show laravel/sanctum` |
| `personal_access_tokens` テーブルがある | `SHOW CREATE TABLE personal_access_tokens`（テーブルの構造は 11 データの担当） |
| `books`・`book_genre`・`reviews` テーブルがある | 11 データの担当 |
| 権限判定クラス `App\Policies\BookPolicy` の `update`・`delete` | 01 書籍の担当。この番号は同じ判定を使う |
| `App\Models\Book` が `SoftDeletes` を使い、`published_date` を日付型に変換している | 01 書籍の担当 |

## 2. 担当するルート

`routes/api.php` に書く。

| No | API | メソッド | URI | 処理 | 認証 |
|---|---|---|---|---|---|
| 005 | AP01 | GET | `/api/v1/books` | `App\Http\Controllers\Api\V1\BookController@index` | 不要 |
| 007 | AP02 | GET | `/api/v1/books/{book}` | `App\Http\Controllers\Api\V1\BookController@show` | 不要 |
| 006 | AP03 | POST | `/api/v1/books` | `App\Http\Controllers\Api\V1\BookController@store` | `auth:sanctum` |
| 008 | AP04 | PUT | `/api/v1/books/{book}` | `App\Http\Controllers\Api\V1\BookController@update` | `auth:sanctum`・登録者本人 |
| 009 | AP05 | DELETE | `/api/v1/books/{book}` | `App\Http\Controllers\Api\V1\BookController@destroy` | `auth:sanctum`・登録者本人 |

- 書き込みの3本だけを `auth:sanctum` で囲み、参照の2本は囲まない
- トークンは `Authorization: Bearer <トークン>` で受け取る。トークンを発行する画面・処理はアプリに置かない（要件のAPIが5本で確定しており、提供モックにも発行の画面が無いため）。手動の確認ではコマンドでトークンを発行する
- `{book}` は削除済みの書籍を探さない。削除済みの書籍のIDは存在しないIDと同じ扱い（404）

No 049 `GET /sanctum/csrf-cookie`（ルート名 `sanctum.csrf-cookie`、処理 `Sanctum\CsrfCookieController@show`）は Sanctum が自動で登録する。アプリのコードから呼ばない。

## 3. 対象ファイル（この番号が持つファイル。変更してよいのは「担当する部分」の範囲）

| 対象 | 担当する部分 |
|---|---|
| `App\Http\Controllers\Api\V1\BookController` | 全体 |
| `App\Http\Requests\Api\V1\ApiFormRequest`・`IndexBookRequest`・`StoreApiBookRequest`・`UpdateApiBookRequest` | 全体 |
| `App\Http\Resources\BookResource`・`BookListResource`・`GenreResource`・`ReviewResource` | 全体 |
| `App\Providers\RouteServiceProvider` | 呼び出し回数の上限（`boot`）。定数 `HOME`（値 `/books`。06 会員登録・ログインが移動先として読む）は変更しない |
| `App\Exceptions\Handler` | JSONを返す側（`api/*` の401・403・404）。画面を返す側は 00 の担当 |
| `config/sanctum.php` | 全体 |
| `routes/api.php` | 全体 |

## 4. 作るもの

### 4-1. 共通のエラー応答

`/api/*` では、エラーを画面ではなくJSONで返す。`Accept` の指定が無くても JSON で返す。

| 場面 | ステータス | 本文 | 返す部品 |
|---|---|---|---|
| 入力エラー | 422 | `{"message": "入力内容に誤りがあります。", "errors": {"項目": ["文言"]}}` | `App\Http\Requests\Api\V1\ApiFormRequest@failedValidation`。API の入力チェック3本はこのクラスを継承する |
| トークンが無い・無効 | 401 | `{"message": "認証が必要です。"}` | `App\Exceptions\Handler@register` |
| 登録者本人以外の更新・削除 | 403 | `{"message": "この操作を実行する権限がありません。"}` | `App\Exceptions\Handler@register` |
| 存在しないID・削除済みの書籍のID | 404 | `{"message": "指定された書籍が見つかりません。"}` | `App\Exceptions\Handler@register` |
| 呼び出し回数の上限を超えた | 429 | — | 4-6 |

### 4-2. 書籍1件の形

| キー | 内容 | 部品 |
|---|---|---|
| `id` `title` `author` `isbn` `image_url` | 書籍の値そのまま | `App\Http\Resources\BookResource@toArray` |
| `description` | 書籍の値そのまま。一覧には含めない | 同上 |
| `published_date` | `YYYY-MM-DD`。未登録は `null` | 同上 |
| `average_rating` | レビュー平均を小数1桁に丸めた数。レビューが無ければ `0` | 同上 |
| `reviews_count` | レビュー件数 | 同上 |
| `genres` | `[{"id": 1, "name": "小説"}]` の形 | `App\Http\Resources\GenreResource@toArray` |
| `reviews` | 詳細・登録・更新の応答だけ。`[{"user_name": "山田太郎", "rating": 5, "comment": "…" または null, "created_at": "ISO 8601形式"}]` | `App\Http\Resources\ReviewResource@toArray` |

一覧の各書籍は `App\Http\Resources\BookListResource` で、`description` と `reviews` を含まない9個のキーにする。

### 4-3. AP01 書籍一覧

| 項目 | 確定値 |
|---|---|
| 対象 | 削除済みの書籍を除く |
| 並び | 新しい順。並び替えの指定は受け付けない |
| 応答 | 200。`data`（書籍の配列）と、ページ情報 `links`・`meta` |

| パラメータ | ルール | エラー文言 |
|---|---|---|
| `keyword` | 任意・255文字以内。タイトルまたは著者名の部分一致 | 「検索キーワードは255文字以内で入力してください」 |
| `genre_id` | 任意・整数・存在するジャンル | 「指定されたジャンルが存在しません」 |
| `page` | 任意・1以上の整数 | 「ページ番号は1以上の整数で指定してください」 |
| `per_page` | 任意・1〜100の整数。既定10 | 「取得件数は1〜100の範囲で指定してください」 |

入力チェックは `App\Http\Requests\Api\V1\IndexBookRequest`。

### 4-4. AP02 書籍詳細・AP03 登録・AP04 更新・AP05 削除

| API | 内容 | 成功時 |
|---|---|---|
| AP02 | レビューを含む書籍1件 | 200 |
| AP03 | 登録者はトークンの持ち主。書籍の保存とジャンルの紐付けを行う | 201。登録した書籍 |
| AP04 | 登録者本人のみ（`App\Policies\BookPolicy@update`） | 200。更新後の書籍 |
| AP05 | 登録者本人のみ（`App\Policies\BookPolicy@delete`）。論理削除 | 204。本文なし |

AP03・AP04 の入力（登録は `App\Http\Requests\Api\V1\StoreApiBookRequest`、更新は `App\Http\Requests\Api\V1\UpdateApiBookRequest`。更新では ISBN の重複チェックから自分自身を除く）

| 項目 | ルール | エラー文言 |
|---|---|---|
| `title` | 必須・文字列・255文字以内 | 「タイトルを入力してください」「タイトルは255文字以内で入力してください」 |
| `author` | 必須・文字列・255文字以内 | 「著者名を入力してください」「著者名は255文字以内で入力してください」 |
| `isbn` | **必須**・文字列・数字13桁・未登録 | 「ISBNを入力してください」「ISBNは13桁の数字で入力してください」「このISBNは既に登録されています」 |
| `published_date` | **必須**・日付 | 「出版日を入力してください」「出版日は正しい日付形式で入力してください」 |
| `description` | 任意・文字列・1000文字以内 | 「説明は1000文字以内で入力してください」 |
| `image_url` | 任意・URL形式・255文字以内 | 「画像URLの形式が正しくありません」「画像URLは255文字以内で入力してください」 |
| `genres` | 必須・配列・1つ以上。各IDが存在すること | 「ジャンルを1つ以上選択してください」「選択されたジャンルが存在しません」 |

画面の書籍登録と違い、API では ISBN と出版日が必須である。

### 4-5. 削除済みの書籍

一覧から除き、詳細・更新・削除では404にする。

### 4-6. 呼び出し回数の上限

`App\Providers\RouteServiceProvider@boot` の `RateLimiter::for('api', …)` に置く。

| 項目 | 確定値 |
|---|---|
| 上限 | 1分に60回（`Limit::perMinute(60)`） |
| 数える単位 | ログイン中は会員のID、それ以外はIP（`by(` に渡す） |
| 超えたとき | 429 |

## 5. 完成条件

`docs/03_検品表/07_公開API.md` の全行が YES であること。

完了報告は `docs/共通ルール.md` 1-6 の項目だけでよい。この番号に固有の報告は無い。
