# 全体検品D：公開API（AP01〜AP06）・Sanctumトークン認証・API Resource・シート13 API仕様書全行

## やったこと（事実のみ）
- 要件シート.xlsx シート13（API仕様書・全行）を inline string のままシート名/行/セルアドレス付きで抽出した。
- 担当ドメイン（routes/api.php・Api/V1/BookController・Api/V1/*Request・Http/Resources/*・config/sanctum.php・personal_access_tokens migration・Book/User model・BookPolicy・tests/Feature/Api/BookApiTest）のコードを読み、要件記述と実装を1件ずつ並置した。
- route:list（middleware付き）と API feature テストを読み取り目的で実行し、生出力を貼った。

## 変更ファイル
`git diff --stat`（作業ツリー・追跡ファイル）の生出力：
```
（空。追跡ファイルの変更なし）
```
`git status --short`：
```
?? .claude/
?? "docs/03_検品表/要件シート_全数チェックリスト.md"
?? "docs/04_検品結果/2026-09-27_ReadingPlanPolicy完了済み編集不可_検品.md"
?? "docs/04_検品結果/2026-09-27_要件シートvs実装_ズレ調査.md"
?? "docs/04_検品結果/2026-09-27_要件シートvs実装_ズレ調査_第2弾.md"
?? "docs/04_検品結果/2026-09-27_要件シートvs実装_ズレ調査_第3弾.md"
?? "docs/04_検品結果/2026-09-28_要件シートチェックリスト検品.md"
```
（本セッションで実装コード・テストの変更なし。追加は docs/04 の本ファイルのみ）

## 抽出方法
python3 の zipfile + xml.etree.ElementTree で xlsx を開き、sharedStrings.xml が無いため各 worksheet の inline string（`<c>/<is>/<t>`）を直接読み、セルアドレス（例 D6）付きで出力した。

---

## 要件項目ごとの突き合わせ結果（要件原文 vs 実装の実際）

### 【エンドポイント一覧】シート13 D6〜H10

要件（原文）：
```
D6=GET   E6=/api/v1/books        G6=不要  H6=不要
D7=GET   E7=/api/v1/books/{book} G7=不要  H7=不要
D8=POST  E8=/api/v1/books        G8=不要  H8=★ Sanctum 必須
D9=PUT   E9=/api/v1/books/{book} G9=不要  H9=★ Sanctum + BookPolicy（所有者のみ）
D10=DELETE E10=/api/v1/books/{book} G10=不要 H10=★ Sanctum + BookPolicy（所有者のみ）
```

実装 routes/api.php 17-28：
```php
Route::prefix('v1')->group(function () {
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{book}', [BookController::class, 'show']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/books', [BookController::class, 'store']);
        Route::put('/books/{book}', [BookController::class, 'update']);
        Route::delete('/books/{book}', [BookController::class, 'destroy']);
    });
});
```

実行コマンド：
```
$ ./vendor/bin/sail artisan route:list --path=api -v
```
出力（原文）：
```
  GET|HEAD  api/v1/books ......................... Api\V1\BookController@index
            ⇂ api
  POST      api/v1/books ......................... Api\V1\BookController@store
            ⇂ api
            ⇂ App\Http\Middleware\Authenticate:sanctum
  GET|HEAD  api/v1/books/{book} ................... Api\V1\BookController@show
            ⇂ api
  PUT       api/v1/books/{book} ................. Api\V1\BookController@update
            ⇂ api
            ⇂ App\Http\Middleware\Authenticate:sanctum
  DELETE    api/v1/books/{book} ................ Api\V1\BookController@destroy
            ⇂ api
            ⇂ App\Http\Middleware\Authenticate:sanctum

                                                            Showing [5] routes
```
- GET 2本は `api` のみ。POST/PUT/DELETE の3本に `Authenticate:sanctum` が付与されている。

### 【AP01 書籍一覧API】シート13 C12/D13

要件（原文抜粋）：
- 「キーワード検索やジャンルでの絞り込み、ページネーションに対応」「各書籍にはジャンル情報、平均評価、レビュー件数を含める」「API Resourceを使用」
- per_page: デフォルト10、1〜100で上書き可 / average_rating: 小数第1位固定（例 4.5）/ date型: Y-m-d / datetime型: ISO8601 JSTオフセット付き
- keyword: nullable,string,max:255 → 「検索キーワードは255文字以内で入力してください」
- genre_id: nullable,integer,exists:genres,id → 「指定されたジャンルが存在しません」
- page: nullable,integer,min:1 → 「ページ番号は1以上の整数で指定してください」
- per_page: nullable,integer,min:1,max:100（デフォルト10）→ 「取得件数は1〜100の範囲で指定してください」
- レスポンス data[] 各要素キー: id,title,author,isbn,published_date,image_url,average_rating,reviews_count,genres[{id,name}]。※description は一覧に含めない
- links/meta（current_page,last_page,per_page,total）。該当0件でも data:[] で200。422でパラメータ不正。

実装 IndexBookRequest 12-39：
```php
'keyword'  => ['nullable', 'string', 'max:255'],
'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
'page'     => ['nullable', 'integer', 'min:1'],
'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
```
messages：
```php
'keyword.max'      => '検索キーワードは255文字以内で入力してください',
'genre_id.integer' => '指定されたジャンルが存在しません',
'genre_id.exists'  => '指定されたジャンルが存在しません',
'page.integer'     => 'ページ番号は1以上の整数で指定してください',
'page.min'         => 'ページ番号は1以上の整数で指定してください',
'per_page.integer' => '取得件数は1〜100の範囲で指定してください',
'per_page.min'     => '取得件数は1〜100の範囲で指定してください',
'per_page.max'     => '取得件数は1〜100の範囲で指定してください',
```

実装 BookController::index 23-44：
```php
$perPage = $request->input('per_page', 10);   // デフォルト10
Book::with('genres')->withAvg('reviews','rating')->withCount('reviews')
  ->when($request->keyword, ...title/author LIKE...)
  ->when($request->genre_id, fn... whereHas('genres', id=genre_id))
  ->latest()->paginate($perPage)->withQueryString();
return BookListResource::collection($books);
```

実装 BookListResource 15-28（一覧のキー構成。descriptionは無い）：
```php
'id','title','author','isbn',
'published_date' => $this->published_date?->format('Y-m-d'),
'image_url',
'average_rating' => round((float) $this->reviews_avg_rating, 1),
'reviews_count',
'genres' => GenreResource::collection($this->whenLoaded('genres')),
```
GenreResource 15-21：`'id','name'` のみ。

average_rating 丸めとJSON表現の実挙動：
```
$ ./vendor/bin/sail artisan tinker --execute='echo json_encode(round((float)4.5,1)); echo json_encode(round((float)4.0,1)); echo json_encode(round((float)0,1));'
round4.5: 4.5
round4.0: 4
round0: 0
```
（要件AP01例は `"average_rating": 4.0` / `4.5` と記載。実装は round(x,1) の float を JsonResource がそのまま json_encode するため、4.5→4.5、4.0→4、0→0 となる。整数値になる評価は末尾 .0 が落ちる。）

meta/links はページネータ既定。テスト test_index_is_public_returns_200 が `meta.total` を参照して200を確認：
```
$ ./vendor/bin/sail artisan test tests/Feature/Api/BookApiTest.php
  ✓ index is public returns 200 (assertJsonPath meta.total=3)
  ✓ index keyword filter (meta.total=1, hitのみ)
  ✓ index genre filter (meta.total=1, 対象ジャンルのみ)
```

### 【AP02 書籍詳細API】シート13 C15/D16

要件（原文抜粋）：
- 「ジャンル情報とレビュー（投稿者名・評価・コメント・投稿日時）を含める」「存在しないIDはエラーレスポンス」「API Resource使用」
- data キー: id,title,author,isbn,published_date,description,image_url,average_rating,reviews_count,genres[{id,name}],reviews[{user_name,rating,comment,created_at}]
- comment は null 許容。created_at 例 "2026-08-01T10:00:00+09:00"。
- 404 例：`{"message": "指定された書籍が見つかりません。"}`。存在しない/削除済みは404（SoftDelete標準除外）。

実装 BookController::show 49-56：
```php
$book->load(['genres','reviews.user'])->loadAvg('reviews','rating')->loadCount('reviews');
return new BookResource($book);
```

実装 BookResource 15-30（詳細。description と reviews を含む）：
```php
'id','title','author','isbn',
'published_date' => $this->published_date?->format('Y-m-d'),
'description',
'image_url',
'average_rating' => round((float) $this->reviews_avg_rating, 1),
'reviews_count',
'genres' => GenreResource::collection($this->whenLoaded('genres')),
'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
```

実装 ReviewResource 15-23：
```php
'user_name' => $this->user->name,
'rating' => $this->rating,
'comment' => $this->comment,
'created_at' => $this->created_at->toIso8601String(),
```

日時形式の実挙動（config/app.php timezone='Asia/Tokyo'）：
```
$ ./vendor/bin/sail artisan tinker --execute='echo \Carbon\Carbon::create(2026,8,1,10,0,0,"Asia/Tokyo")->toIso8601String();'
ISO8601: 2026-08-01T10:00:00+09:00
```
config/app.php 73：`'timezone' => 'Asia/Tokyo',`

404 の実装 app/Exceptions/Handler.php 35-39：
```php
$this->renderable(function (NotFoundHttpException $e, Request $request) {
    if ($request->is('api/*')) {
        return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
    }
});
```

テスト：
```
  ✓ show is public returns 200 (assertJsonPath data.id)
  ✓ show not found returns json 404 → assertJson message='指定された書籍が見つかりません。'
  ✓ show soft deleted returns 404 (delete後 GET が 404)
```

### 【AP03 書籍登録API】シート13 C18/D19

要件（原文抜粋）：
- 「バリデーションエラー時は日本語」「成功時のステータスを適切に」
- バリデーション（Web版books.storeと同一＋user_id追加）:
  title required,string,max:255 / author required,string,max:255 / isbn required,string,regex:/^[0-9]{13}$/,unique:books,isbn / published_date required,date / description nullable,string,max:1000 / image_url nullable,url,max:255 / genres required,array,min:1 ／ genres.* exists:genres,id / user_id required,integer,exists:users,id
- ※「user_idが必須なのは基本段階では認証なし。応用段階でSanctum導入後はAuth::id()取得方式に切替予定」
- 201成功（AP02のdata形式からreviewsキーを除いた形。user_idはレスポンスに含めない）。422バリデーションエラー。

チェックリスト（シート7）該当行 110/18（原文）：
```
D18=★ 公開API 書籍登録・編集 ／ G18=user_id: リクエストボディでの受領を廃止し、Auth::id(
```

実装 StoreApiBookRequest 14-23：
```php
'title' => ['required','string','max:255'],
'author' => ['required','string','max:255'],
'isbn' => ['required','string','regex:/^[0-9]{13}$/','unique:books,isbn'],
'published_date' => ['required','date'],
'description' => ['nullable','string','max:1000'],
'image_url' => ['nullable','url','max:255'],
'genres' => ['required','array','min:1'],
'genres.*' => ['exists:genres,id'],
```
（user_id ルールは無い）messages 33-49 は上記各文言と一致（title.required='タイトルを入力してください' 等、全項目シート原文と同文）。

git diff main（user_id 廃止の履歴）：
```
$ git diff main -- app/Http/Requests/Api/V1/StoreApiBookRequest.php
-            'user_id' => ['required', 'integer', 'exists:users,id'],
$ git diff main -- app/Http/Controllers/Api/V1/BookController.php
-            $book = Book::create($request->validated());
+            $book = Book::create($request->validated() + ['user_id' => Auth::id()]);
```

実装 BookController::store 61-73：
```php
$book = DB::transaction(function () use ($request) {
    $book = Book::create($request->validated() + ['user_id' => Auth::id()]);
    $book->genres()->sync($request->genres);
    return $book;
});
$book->load('genres')->loadAvg('reviews','rating')->loadCount('reviews');
return (new BookResource($book))->response()->setStatusCode(Response::HTTP_CREATED);
```
- 201 は `Response::HTTP_CREATED` で設定。
- レスポンスは BookResource。store では reviews をロードしないため `whenLoaded('reviews')` により reviews キーは出力されない。user_id は BookResource のキーに無いため含まれない。

422 の実装 ApiFormRequest 22-28：
```php
throw new HttpResponseException(response()->json([
    'message' => '入力内容に誤りがあります。',
    'errors' => $validator->errors(),
], 422));
```
（要件422例 message="入力内容に誤りがあります。" と一致。errors は field→[文言]。）

テスト：
```
  ✓ store success with sanctum → assertStatus(201), assertJsonPath data.title, assertDatabaseHas books(isbn,user_id)
  ✓ store validation error when authenticated → postJson([]) assertStatus(422) assertJsonStructure['message','errors']
```
（注：user_id をレスポンスに含めない／201本文が reviews キー無しであることをアサートするテストは存在しない。コード上 BookResource のキー定義から追える範囲は上記引用のとおり。実データのJSON本文キー集合の直接確認は未実施→未確認欄参照。）

### 【AP04 書籍更新API】シート13 C21/D22

要件（原文抜粋）：
- 「存在しないIDはエラー」「ルールは登録と同等、ただしISBN一意性で自身除外」
- isbn: required,string,regex:/^[0-9]{13}$/,unique:books,isbn,{book},id（自身除外）
- 「user_idもAP03と同一ルールでバリデーション対象」
- 200成功（AP02のdata形式・reviewsキーを除く）。404 `{"message":"指定された書籍が見つかりません。"}`。422はAP03同一形式。

実装 UpdateApiBookRequest 16-31：
```php
'title' => ['required','string','max:255'],
'author' => ['required','string','max:255'],
'isbn' => ['required','string','regex:/^[0-9]{13}$/',
           Rule::unique('books','isbn')->ignore($this->route('book'))],
'published_date' => ['required','date'],
'description' => ['nullable','string','max:1000'],
'image_url' => ['nullable','url','max:255'],
'genres' => ['required','array','min:1'],
'genres.*' => ['exists:genres,id'],
```
（user_id ルールは無い。要件D22の「user_idもAP03と同一ルールで対象」は、AP03がAuth::id()方式に切替（行18）済みのため、UpdateにもStore同様 user_id ルールは無い。）

実装 BookController::update 78-90：
```php
$this->authorize('update', $book);
DB::transaction(function () use ($request, $book) {
    $book->update($request->validated());
    $book->genres()->sync($request->genres);
});
$book->load('genres')->loadAvg('reviews','rating')->loadCount('reviews');
return new BookResource($book);
```
- 戻り値型 `BookResource`（HTTP 200 既定）。
- update では reviews をロードしないため BookResource の `whenLoaded('reviews')` により reviews キーは省かれる（コードで追える範囲）。
- 404：Route Model Binding が `Book`（SoftDeletesグローバルスコープ適用）で解決するため、存在しない/削除済みIDは ModelNotFound→NotFoundHttpException→Handler の api用404 JSON。

BookPolicy 13-17（所有者のみ）：
```php
public function update(User $user, Book $book): bool
{
    return $user->id === $book->user_id && ! $book->trashed();
}
```

テスト：
```
  ✓ update success with sanctum → putJson 所有者 assertOk data.title='更新後タイトル' assertDatabaseHas
  ✓ update other users book returns 403 → assertStatus(403) assertJson message='この操作を実行する権限がありません。'
```
（isbn自身除外・存在しないID404・user_idレスポンス非掲載・200本文のreviewsキー省略を直接アサートするテストは無い。コード引用の範囲まで。）

### 【AP05 書籍削除API】シート13 C24/D25

要件（原文抜粋）：
- 「関連データ（レビュー・お気に入り・ジャンル紐付け）も適切に処理」「成功時ステータスを適切に」
- 204成功（本文なし）。404 `{"message":"指定された書籍が見つかりません。"}`。
- 「書籍は論理削除（SoftDelete）。reviews/favorites/book_genre は削除されず保持。cascade設定はSoftDelete下で発火しない」

実装 BookController::destroy 95-102：
```php
$this->authorize('delete', $book);
$book->delete();          // SoftDelete
return response()->noContent();   // 204
```
BookPolicy delete 21-25：所有者のみ（update と同一判定）。
Book model 14：`use HasFactory, SoftDeletes;`（論理削除）。

テスト：
```
  ✓ destroy success with sanctum → assertStatus(204); assertSoftDeleted('books')
  ✓ destroy other users book returns 403 → assertJson message='この操作を実行する権限がありません。' + assertDatabaseHas deleted_at=null
```
404（存在しないIDのDELETE）を直接叩くテストは BookApiTest に無い（show の404テストのみ）→未確認欄参照。関連データ（favorites/book_genre）が削除後も保持されることを直接アサートするテストも無い。

### 【AP06 ★ Sanctum APIトークン認証（応用）】シート13 C27/D28

要件（原文）：
```
D28=公開APIの書き込み系エンドポイント（POST/PUT/DELETE）に Laravel Sanctum を導入し、認証＋認可を行う。
Bearer トークン（Authorization ヘッダ）による認証方式を採用すること。
未認証時・認可エラー時のHTTPステータスコードを適切に設定すること。
```

実装：
- routes/api.php 23：`Route::middleware('auth:sanctum')->group(...)` で POST/PUT/DELETE を包む（route:list で `Authenticate:sanctum` 確認済み）。
- app/Models/User.php 11,15：`use Laravel\Sanctum\HasApiTokens;` / `use HasApiTokens, HasFactory, Notifiable;`
- 未認証時 401：Handler 41-45
```php
$this->renderable(function (AuthenticationException $e, Request $request) {
    if ($request->is('api/*')) {
        return response()->json(['message' => '認証が必要です。'], 401);
    }
});
```
- 認可エラー 403：Handler 47-51
```php
$this->renderable(function (AccessDeniedHttpException $e, Request $request) {
    if ($request->is('api/*')) {
        return response()->json(['message' => 'この操作を実行する権限がありません。'], 403);
    }
});
```

テスト（未認証401・他人403、メソッドごと個別）：
```
  ✓ store unauthenticated returns 401   (postJson 401 message='認証が必要です。')
  ✓ update unauthenticated returns 401  (putJson  401 同文)
  ✓ destroy unauthenticated returns 401 (deleteJson 401 同文)
  ✓ update other users book returns 403 (putJson  403)
  ✓ destroy other users book returns 403 (deleteJson 403)
```
（Bearer/Authorizationヘッダによる実トークン認証の実挙動は、テストが `Sanctum::actingAs()` で認証状態を注入して確認。実際の personal_access_tokens 発行→Bearer送信の経路を通した確認は未実施→未確認欄参照。トークン発行経路のコード（createToken 呼び出し）はアプリ内に存在しない：`grep -rln createToken tests/` の結果は BookApiTest に createToken 記述なし。）

### 【personal_access_tokens テーブル】シート11/12 行228/230/231

要件（原文）：
```
228/70 D70=personal_access_tokensテーブル（Laravel ...）
230/72 E72=tokenable_type varchar(255) 本アプリでは常に 'App\Models\User'
231/73 E73=tokenable_id  bigint unsigned users.id（ポリモーフィック）
```
CLAUDE.md §16：`abilities` / `expires_at` は Sanctum標準のまま残す（未使用でも削除しない）。

実装 database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php：
```php
Schema::create('personal_access_tokens', function (Blueprint $table) {
    $table->id();
    $table->morphs('tokenable');        // tokenable_type varchar(255) + tokenable_id bigint unsigned
    $table->string('name');
    $table->string('token', 64)->unique();
    $table->text('abilities')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});
```
- `abilities`・`expires_at` 残置。`morphs('tokenable')` で tokenable_type/tokenable_id を生成。

### 【config/sanctum.php】

要件・CLAUDE.md：Sanctum標準設定を残す。実装 config/sanctum.php：`guard=['web']`、`expiration=null`、標準 middleware エントリを保持（削除・改変なし）。

---

## スコープ厳守（発注書に無い独自ロジックが足されていないか、該当ソースを実読）

- ApiFormRequest 9-28：`authorize(): true` と `failedValidation()` で422 JSON整形のみ。独自の追加バリデーション・クロージャは無い（全文引用済み）。
- BookController の各メソッド：index=検索/絞り込み/paginate、show=load、store=create+sync+201、update=authorize+update+sync、destroy=authorize+delete+204。要件記載外のロジック（独自認証・独自Validator・追加副作用）は見当たらない（全文引用済み）。
- Resource 4種：キー写像のみ。要件外キーの追加は無い（average_rating の round(x,1) は要件「小数第1位固定」に対応）。
- BookPolicy：update/delete/restore とも `Auth::id() 相当（$user->id === $book->user_id）` + trashed 判定のみ（CLAUDE.md §8 準拠）。

---

## 未確認・保留（具体理由付き）

- AP01/AP02 の average_rating が要件例 `4.0`（末尾.0あり）に対し、実装は round(float,1) の JSON化で整数評価時 `4` になる点：tinker で `json_encode(round((float)4.0,1))=4` を確認済み（上記）。実APIレスポンス本文での average_rating の表記（4 か 4.0 か）を HTTP で取得して直接照合する確認は、実データseed投入を伴うため本セッションでは未実施（migrate/seed 禁止のため）。要件例との差異の有無はこの生挙動を根拠に判定側で判断されたい。
- AP03 201本文で user_id が含まれないこと／reviews キーが省かれること、AP04 200本文で reviews キーが省かれること：BookResource の `whenLoaded('reviews')` とキー定義から追える範囲は引用済み。実レスポンス本文のキー集合を assertJsonMissing 等で直接確認するテストは存在せず、HTTP本文の直接ダンプも未実施（seed禁止・実データ不在）。
- AP05 の「存在しないID DELETE→404」を直接叩くテストは BookApiTest に無い（show の404テストのみ）。ルーティングは同一 Route Model Binding のため show と同経路だが、DELETE メソッド固有の404実挙動の直接証拠は未取得。
- AP05 関連データ保持（favorites/book_genre が削除後も残る）を直接アサートするテストは無い。CLAUDE.md §9-4 のとおり SoftDelete 下で cascade 非発火という設計記述に対応するが、実DBでの保持確認は未実施。
- AP06 の Bearer トークン実発行→Authorization ヘッダ送信の end-to-end 経路：テストは `Sanctum::actingAs()` による認証注入で確認しており、実 personal_access_tokens 発行→Bearer 送信の疎通は未実施。アプリ内にトークン発行経路（createToken 呼び出し）のコードは見当たらない（発行はコーチレビュー/手動運用想定か、範囲外の可能性）。
- 401メッセージ「認証が必要です。」・403「この操作を実行する権限がありません。」の日本語文言は、シート13/シート7いずれのセル原文にも明記が見当たらず（抽出結果に該当文言なし）。要件上の確定文言か未確定文言かの区分は判定側で確認されたい。

## worktree情報
- ブランチ名：fix/book-search-and-isbn-ui
- 本体へのマージ：未（main未マージ）。本セッションで実装コード・テストの変更なし（`git diff --stat` 追跡ファイル空）。次工程：判定側が本証拠と要件シート・チェックリストを突き合わせ、YES/NO を確定する。

## 判定はしない
本ファイルは YES/NO・PASS/FAIL の判定を含まない。判定は片倉／チャット側がこの証拠を見て行う。
