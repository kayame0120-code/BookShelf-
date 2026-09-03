status: confirmed complete（デザインUI優先例外の影響なし）

# 発注書 05 — 公開API・シーディング

**対象走行**: 走行⑤（第4週）
**出典**: `docs/01_機能仕様書/public_api.md`（API本体） ＋ `books.md`§11／`genres.md`§10／`reviews_likes.md`§11／`favorites.md`§9／`auth.md`§10（各Seederの中身）。この発注書は上記だけで書けている。
**対の検品表**: `docs/03_検品表/05_公開APIシーディング.md`
**前提**: 走行①〜④が合格・確定済み。全7テーブルとモデルは完成済みとして扱う。**シーディングは採点直結。指定データは1文字も変えないこと。**

---

## 0. スコープ

### やること

1. `Api\V1\BookController`（index / show / store / update / destroy の5アクション）
2. `routes/api.php` へのルート追加（5本）
3. `IndexBookRequest` / `StoreApiBookRequest` / `UpdateApiBookRequest`（`App\Http\Requests\Api\V1`名前空間）
4. API Resourceクラス4本（`BookListResource` / `BookResource` / `GenreResource` / `ReviewResource`）
5. API専用のエラーハンドリング（422の日本語化・404の日本語化）
6. 6本のSeeder（`UserSeeder` / `GenreSeeder` / `BookSeeder` / `ReviewSeeder` / `FavoriteSeeder` / `ReviewLikeSeeder`）と `DatabaseSeeder`

### やらないこと（他の走行のスコープ。手を出すな）

| やらないこと | 担当走行 |
|---|---|
| Web画面側の実装（books.md ほか） | 走行②〜④（完了済み） |
| テストコード（Unit/Feature） | 走行⑥ |
| ★Sanctum認証（AP06） | 走行⑩（応用） |

**認可（Policy）は基本段階では存在しない。** 5本すべて認証なしで動作するため、`$this->authorize()` は1箇所も呼ばない。

### この発注書の完成条件

`docs/03_検品表/05_公開APIシーディング.md` の全行がYESになること。公開API 5本が確定仕様のレスポンス構造・ステータスコードで動作し、`sail artisan db:seed` の実行結果が要件シート シート9の指定データと完全一致すること。

---

## 1. ルーティング（`routes/api.php` へ追加）

| ID | メソッド | URI | Controller@Action | 認証 |
|---|---|---|---|---|
| AP01 | GET | `/api/v1/books` | `Api\V1\BookController@index` | 不要 |
| AP02 | GET | `/api/v1/books/{book}` | `Api\V1\BookController@show` | 不要 |
| AP03 | POST | `/api/v1/books` | `Api\V1\BookController@store` | 不要 |
| AP04 | PUT | `/api/v1/books/{book}` | `Api\V1\BookController@update` | 不要 |
| AP05 | DELETE | `/api/v1/books/{book}` | `Api\V1\BookController@destroy` | 不要 |

```php
// routes/api.php（'api'プレフィックスはRouteServiceProviderが自動付与。'v1'は自分で付ける）
Route::prefix('v1')->group(function () {
    Route::apiResource('books', \App\Http\Controllers\Api\V1\BookController::class);
});
```

`withTrashed()`は**どのルートにも付けない**。論理削除済みの書籍IDを指定したAP02/AP04/AP05は404を返すのが確定仕様。

名前空間は `App\Http\Controllers\Api\V1\BookController`。Web版の `App\Http\Controllers\BookController` と**同名クラスが2つ存在する**ので、`use`文の取り違えに注意すること。

---

## 2. 共通仕様

| 項目 | 確定内容 |
|---|---|
| ページネーション | 既定`per_page=10`。クライアント指定時は1〜100の範囲で上書き可 |
| `average_rating`の丸め | 小数第1位（`round($value, 1)`）。レビュー0件のときは`0` |
| 日付形式（date型） | `Y-m-d`（例: `2012-06-23`） |
| 日時形式（datetime型） | ISO8601・JSTオフセット付き（例: `2026-08-01T10:00:00+09:00`）。`toIso8601String()`を使う |
| バリデーションエラー形式 | `{"message": string, "errors": {field: [string, ...]}}` |
| 存在しないIDのエラー形式 | `{"message": string}`（`errors`キーなし） |
| ジャンル指定（POST/PUT） | フィールド名は`genres`（配列）。Web版の`genres[]`と同一フィールド名・同一ルール |
| レスポンス整形 | 必ずAPI Resourceクラスを使う |

`config/app.php`の`timezone`が`Asia/Tokyo`になっていること（走行①で設定済み）を前提とする。

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

**リクエストパラメータ**

| パラメータ | ルール | エラー文言 |
|---|---|---|
| `keyword` | `nullable, string, max:255` | 検索キーワードは255文字以内で入力してください |
| `genre_id` | `nullable, integer, exists:genres,id` | 指定されたジャンルが存在しません |
| `page` | `nullable, integer, min:1` | ページ番号は1以上の整数で指定してください |
| `per_page` | `nullable, integer, min:1, max:100`（既定10） | 取得件数は1〜100の範囲で指定してください |

**レスポンス（200・genre_id=3で絞り込んだ実データ例）**

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

`description`は一覧に含めない（詳細APIのみ。一覧ペイロードを軽くするための確定仕様）。`links`/`meta`はLaravelの`AnonymousResourceCollection`が`paginate()`から自動生成する構造をそのまま使う。検索条件をページ送りに引き継ぐため`->withQueryString()`を付ける。

**レスポンス（422）**: `{"message": "入力内容に誤りがあります。", "errors": {"per_page": ["取得件数は1〜100の範囲で指定してください"]}}`

**ステータスコード**: 200（該当0件でも`data: []`で200）／422。

`keyword`はWeb画面側の検索フォームでは★応用扱いだが、シート8・13でAP01のリクエストパラメータとして基本段階から確定しているため、**API側は基本段階で実装する**。

---

### AP02: 書籍詳細API — `GET /api/v1/books/{book}`

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

`comment`のnull許容はDB設計と一致する。`user_name`は`reviews.user`のeager loadから取る。description本文・comment本文は例示テキストで、数値・ID・タイトルという構造化項目のみ実データ準拠。

**レスポンス（404・存在しないID／論理削除済みIDも同様）**: `{"message": "指定された書籍が見つかりません。"}`

**ステータスコード**: 200／404（存在しない、または論理削除済み）。

---

### AP03: 書籍登録API — `POST /api/v1/books`

**バリデーション**（Web版`books.store`と同一ルール＋`user_id`追加）

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

`user_id`が必須なのは、基本段階は認証なしで`Auth::id()`が使えないため（応用のSanctum導入後に切替）。

**リクエスト例**: `{"title": "プログラマー脳", "author": "Felienne Hermans", "isbn": "9784798068718", "published_date": "2023-01-01", "description": "...", "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=12", "genres": [3], "user_id": 3}`

**レスポンス（201）**: AP02の`data`形式から`reviews`キーを除いた形。**`user_id`はレスポンスに含めない。**

**ステータスコード**: 201／422。

---

### AP04: 書籍更新API — `PUT /api/v1/books/{book}`

AP03の全項目を同一ルールで適用。差分は`isbn`のみ: `required, string, regex:/^[0-9]{13}$/, unique:books,isbn,{book},id`（自身除外）。

**レスポンス**: 200はAP02の`data`形式（`reviews`キーを除く）／404は`{"message": "指定された書籍が見つかりません。"}`／422はAP03と同一形式。

**ステータスコード**: 200／404／422。

---

### AP05: 書籍削除API — `DELETE /api/v1/books/{book}`

**レスポンス**: 204（本文なし）／404は`{"message": "指定された書籍が見つかりません。"}`。

**ステータスコード**: 204／404。

**関連データの扱い（面談②反映・確定）**: `$book->delete()`が`deleted_at`をセットする論理削除。`reviews`/`favorites`/`book_genre`のレコードは削除されず保持される。204/404というレスポンス形状はcascade方式でもSoftDelete方式でも変わらないため、外部から見た挙動に差はない。

---

## 4. コントローラー仕様（`App\Http\Controllers\Api\V1\BookController`）

| アクション | 処理 |
|---|---|
| `index` | `IndexBookRequest`で検証 → `Book::with('genres')->withAvg('reviews','rating')->withCount('reviews')`に`keyword`（title/authorの部分一致）と`genre_id`（`whereHas('genres', ...)`）を条件付与 → `latest()->paginate($perPage)->withQueryString()` → `BookListResource::collection(...)` |
| `show` | `$book->load(['genres', 'reviews.user'])->loadAvg('reviews','rating')->loadCount('reviews')` → `new BookResource($book)` |
| `store` | `StoreApiBookRequest`で検証 → `DB::transaction()`内で`Book::create(...)` → `genres()->sync($request->genres)` → `new BookResource(...)`を201で返す |
| `update` | `UpdateApiBookRequest`で検証 → `DB::transaction()`内で`$book->update(...)` → `genres()->sync(...)` → `new BookResource(...)`を200で返す |
| `destroy` | `$book->delete()` → `response()->noContent()`（204） |

`Book`のSoftDeletesにより`index`は論理削除済みを自動除外する。`show`/`update`/`destroy`はルートモデル紐付けが論理削除済みを解決できず404になる。追加の分岐は不要。

---

## 5. API Resourceクラス

| クラス | 用途 | 含めるキー |
|---|---|---|
| `BookListResource` | AP01 | id, title, author, isbn, published_date, image_url, average_rating, reviews_count, genres |
| `BookResource` | AP02/AP03/AP04 | 上記＋description、および`reviews`（`whenLoaded('reviews')`） |
| `GenreResource` | genresの要素 | id, name |
| `ReviewResource` | AP02のreviewsの要素 | user_name, rating, comment, created_at |

```php
'published_date' => $this->published_date->format('Y-m-d'),
'average_rating' => round((float) $this->reviews_avg_rating, 1),
'reviews_count'  => $this->reviews_count,
'genres'         => GenreResource::collection($this->whenLoaded('genres')),
'reviews'        => ReviewResource::collection($this->whenLoaded('reviews')),
// ReviewResource側
'user_name'  => $this->user->name,
'created_at' => $this->created_at->toIso8601String(),
```

`reviews`を`whenLoaded()`にすることで、AP03/AP04のレスポンスからキーごと消える。`average_rating`はレビュー0件のとき`reviews_avg_rating`がnullになるので`(float)`キャストで`0`になる。

---

## 6. バリデーション（FormRequest）

`App\Http\Requests\Api\V1`名前空間に置く。

| クラス | 対象 | ルール |
|---|---|---|
| `IndexBookRequest` | AP01 | §3 AP01の表 |
| `StoreApiBookRequest` | AP03 | §3 AP03の表 |
| `UpdateApiBookRequest` | AP04 | AP03と同一。`isbn`のみ`->ignore($this->route('book'))` |

### 422レスポンスの日本語化（必須）

共通の基底クラスを1本作り、上記3クラスはこれを継承する。

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

## 7. 404レスポンスの日本語化（必須）

`App\Exceptions\Handler`の`register()`でAPIリクエストのときだけ差し替える。

```php
$this->renderable(function (NotFoundHttpException $e, Request $request) {
    if ($request->is('api/*')) {
        return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
    }
});
```

`errors`キーは付けない。

---

## 8. シーディング（要件シート シート9／採点直結・改変禁止）

**指定データは1文字も変えないこと。忘れると再提出、または大幅減点の明記あり。**

### DatabaseSeederの実行順

1. `UserSeeder` — users 5件
2. `GenreSeeder` — genres 10件
3. `BookSeeder` — books 11件
4. `ReviewSeeder` — reviews 32件
5. `FavoriteSeeder` — favorites
6. `ReviewLikeSeeder` — review_likes

`sail artisan db:seed` でまとめて投入できるようにする。

### 8-1. UserSeeder

users テーブルに初期ユーザーを5件登録する。`firstOrCreate`を使用しemailの重複を防ぐ。パスワードは`Hash::make()`でハッシュ化する。

| name | email | password |
|---|---|---|
| 山田太郎 | yamada@example.com | password |
| 鈴木花子 | suzuki@example.com | password |
| 田中一郎 | tanaka@example.com | password |
| 佐藤美咲 | sato@example.com | password |
| 高橋健太 | takahashi@example.com | password |

### 8-2. GenreSeeder

genres テーブルにジャンルを固定で10件投入する。`firstOrCreate`を使用しnameの重複を防ぐ。

小説 / ビジネス / 技術書 / 自己啓発 / エッセイ / 歴史 / 科学 / 芸術 / 料理 / 旅行

投入順がそのままid 1〜10になる（`技術書`はid=3。上記AP01/AP02のレスポンス例が`genre_id=3`を技術書として使っている）。

### 8-3. BookSeeder

books テーブルに書籍データを11件投入する。登録者は`User::first()`（山田太郎）。`firstOrCreate`（ISBN重複防止）と`genres()->sync()`を使用。

| # | タイトル | 著者 | ISBN | 出版日 | ジャンル |
|---|---|---|---|---|---|
| 1 | 吾輩は猫である | 夏目漱石 | 9784101010014 | 1905-01-01 | 小説 |
| 2 | 人を動かす | D・カーネギー | 9784422100524 | 1936-10-01 | ビジネス, 自己啓発 |
| 3 | リーダブルコード | Dustin Boswell | 9784873115658 | 2012-06-23 | 技術書 |
| 4 | 7つの習慣 | スティーブン・R・コヴィー | 9784863940246 | 2013-08-30 | ビジネス, 自己啓発 |
| 5 | 坊っちゃん | 夏目漱石 | 9784101010021 | 1906-04-01 | 小説 |
| 6 | サピエンス全史 | ユヴァル・ノア・ハラリ | 9784309226712 | 2016-09-08 | 歴史, 科学 |
| 7 | Clean Code | Robert C. Martin | 9784048930598 | 2017-12-18 | 技術書 |
| 8 | 嫌われる勇気 | 岸見一郎・古賀史健 | 9784478025819 | 2013-12-13 | 自己啓発 |
| 9 | 火花 | 又吉直樹 | 9784163902302 | 2015-03-11 | 小説 |
| 10 | FACTFULNESS | ハンス・ロスリング | 9784822289607 | 2019-01-11 | ビジネス, 科学 |
| 11 | コンテナ物語 | マルク・レビンソン | 9784822251468 | 2007-01-18 | ビジネス, 歴史 |

各書籍に`description`を設定する。`image_url`は`https://placehold.co/200x300/e2e8f0/475569?text={番号}`（`{番号}`は1〜11）で固定。

### 8-4. ReviewSeeder

reviews テーブルに32件。5人のユーザーが11冊の書籍に対して投稿する。`rating`は3〜5の範囲。各書籍に2〜4件を配分。具体的なコメント内容を設定する。`create`を使用。

### 8-5. FavoriteSeeder

favorites テーブルにお気に入りデータを投入する。各ユーザーに3〜5冊のお気に入りを設定。`syncWithoutDetaching`を使用。

### 8-6. ReviewLikeSeeder

review_likes テーブルにいいねデータを投入する。各レビューに0〜3人のユーザーがいいね（**自分のレビューを除く**）。`syncWithoutDetaching`を使用。

シーディング完了後、`genre_id=3`は「技術書」、`id=3`の書籍は「リーダブルコード」になる。本発注書§3のレスポンス例はこの前提で書かれている。

---

## 9. 実装上の注意（CC向け）

- `routes/api.php`に`v1`プレフィックスを付ける。`api`プレフィックスはRouteServiceProviderが自動で付けるので、`Route::prefix('api/v1')`と書くと`/api/api/v1`になる。
- 基本段階ではPolicyを一切適用しない。応用のSanctum後付けで初めて`BookPolicy`をAPIに接続する。
- 応用で`personal_access_tokens`テーブルを作る。基本段階では作らない。
- APIはレスポンスがすべてJSON。flash/リダイレクトの概念は存在しない。

---

## 10. 禁止事項

1. `resources/`配下のBlade/CSS/JSを1文字も変更しない。
2. 本発注書に明記されていないロジックを独自に追加しない（発注書の指定と異なる実装をする場合は、仮決めせず`QUESTIONS.md`に記録して停止する）。
3. §8のシーディングデータを1文字も変えない（採点直結）。
4. `migrate:fresh`を要するmigration変更をしない（`personal_access_tokens`含む）。
5. 「やらないこと」表に挙げた成果物を先取りして作らない。
6. `main`ブランチへ直接コミットしない。

---

## 11. 未定義事項に当たったとき

`QUESTIONS.md`へ以下4欄を記入し、該当作業を止めて次の作業に移る。

- 発生日 ／ 対象発注書（`02_発注書/05_公開APIシーディング.md`） ／ 止まった箇所 ／ CCの解釈候補（2案以上）

---

## 12. 完了時の報告

- 作業ブランチ名と最終コミットID
- `sail artisan route:list --path=api` の出力
- `sail artisan db:seed` の出力（エラーなく完了すること）
- `sail bin pint --test` の出力
- `QUESTIONS.md` に追記した行の有無

---

## 変更履歴（今回のまとめ出力時に確認したこと）

- APIレスポンスの`average_rating`（§3・§5）はAPI専用の集計処理（`Api\V1\BookController`独自の`withAvg`）に基づくものであり、Web画面の書籍一覧（PG01）から★評価表示を撤去した`決定記録_デザインUI優先例外.md`項目1の対象外。API側の`average_rating`は変更なく提供され続ける。
- §8-3のBookSeeder`description`は元々具体的な文言を指定しておらず自由記述欄であるため、実機検品での書籍説明文の書き直し（同記録項目7）はこの発注書の記述と抵触しない。よって本発注書は変更不要と確認した。
