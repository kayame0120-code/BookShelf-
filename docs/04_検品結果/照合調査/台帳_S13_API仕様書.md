# 照合台帳 S13 シート13 API仕様書

- 対象xlsx: docs/00_正本/ の要件シート
- シート名: シート13 API仕様書
- セル数: 45 ／ 行数（L）: 154
- 記入規則: docs/02_発注書/99_要件シート照合調査.md の §6
- このファイルのブロック・L行の削除、並べ替え、L行本文の編集は禁止。「事実:」行の記入と追加のみ行う。

---
### S13!B2
- L1: API仕様書
  - 事実: N/A-記述（シートの表題）

### S13!C3
- L1: 本模擬案件で実装する公開APIの仕様です。
  - 事実: N/A-記述（シートの導入文）
- L2: 基礎の段階では 認証なし で実装します。応用段階で Sanctum APIトークン認証を追加 し、書き込み系（POST/PUT/DELETE）を認証必須化します（赤字★マーク）。
  - 事実: routes/api.php:19 Route::get('/books', [BookController::class, 'index']);
  - 事実: routes/api.php:20 Route::get('/books/{book}', [BookController::class, 'show']);
  - 事実: routes/api.php:23 Route::middleware('auth:sanctum')->group(function () {
  - 事実: routes/api.php:24-26 Route::post('/books', …) ／ Route::put('/books/{book}', …) ／ Route::delete('/books/{book}', …)
  - 事実: 実行結果.md R12 POST            api/v1/books ................... Api\V1\BookController@store ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: 実行結果.md R12 PUT             api/v1/books/{book} ........... Api\V1\BookController@update ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: 実行結果.md R12 DELETE          api/v1/books/{book} .......... Api\V1\BookController@destroy ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: 実行結果.md R245 # POST /api/v1/books（トークンなし・Acceptなし）→ HTTP 401 ／ # PUT /api/v1/books/3（トークンなし）→ HTTP 401 ／ # DELETE /api/v1/books/3（トークンなし）→ HTTP 401
- L3: リクエストパラメータやレスポンスJSONの詳細構造は自分で設計してください。設計した内容は面談内でコーチにレビューしてもらいましょう。
  - 事実: N/A-記述（受講者への作業指示）

### S13!C4
- L1: エンドポイント一覧
  - 事実: N/A-記述（見出し）

### S13!D5
- L1: HTTPメソッド
  - 事実: N/A-記述（表の列名）

### S13!E5
- L1: URI
  - 事実: N/A-記述（表の列名）

### S13!F5
- L1: 説明
  - 事実: N/A-記述（表の列名）

### S13!G5
- L1: 認証
  - 事実: N/A-記述（表の列名）

### S13!H5
- L1: 認証（応用）
  - 事実: N/A-記述（表の列名）

### S13!D6
- L1: GET
  - 事実: 実行結果.md R12 GET|HEAD        api/v1/books ................... Api\V1\BookController@index ／ ⇂ api

### S13!E6
- L1: /api/v1/books
  - 事実: 実行結果.md R12 GET|HEAD        api/v1/books ................... Api\V1\BookController@index ／ ⇂ api
  - 事実: app/Providers/RouteServiceProvider.php:33 ->prefix('api')
  - 事実: routes/api.php:17 Route::prefix('v1')->group(function () {
  - 事実: routes/api.php:19 Route::get('/books', [BookController::class, 'index']);

### S13!F6
- L1: 書籍一覧を取得する
  - 事実: app/Http/Controllers/Api/V1/BookController.php:23 public function index(IndexBookRequest $request): AnonymousResourceCollection
  - 事実: app/Http/Controllers/Api/V1/BookController.php:43 return BookListResource::collection($books);

### S13!G6
- L1: 不要
  - 事実: 実行結果.md R12 GET|HEAD        api/v1/books ................... Api\V1\BookController@index ／ ⇂ api（Authenticate の行なし）
  - 事実: 実行結果.md R22 GET /api/v1/books -> 200

### S13!H6
- L1: 不要
  - 事実: 同上 S13!G6 L1

### S13!D7
- L1: GET
  - 事実: 実行結果.md R12 GET|HEAD        api/v1/books/{book} ............. Api\V1\BookController@show ／ ⇂ api

### S13!E7
- L1: /api/v1/books/{book}
  - 事実: 実行結果.md R12 GET|HEAD        api/v1/books/{book} ............. Api\V1\BookController@show ／ ⇂ api
  - 事実: app/Providers/RouteServiceProvider.php:33 ->prefix('api')
  - 事実: routes/api.php:17 Route::prefix('v1')->group(function () {
  - 事実: routes/api.php:20 Route::get('/books/{book}', [BookController::class, 'show']);

### S13!F7
- L1: 書籍詳細を取得する
  - 事実: app/Http/Controllers/Api/V1/BookController.php:49 public function show(Book $book): BookResource
  - 事実: app/Http/Controllers/Api/V1/BookController.php:55 return new BookResource($book);

### S13!G7
- L1: 不要
  - 事実: 実行結果.md R12 GET|HEAD        api/v1/books/{book} ............. Api\V1\BookController@show ／ ⇂ api（Authenticate の行なし）
  - 事実: 実行結果.md R22 GET /api/v1/books/1 -> 200

### S13!H7
- L1: 不要
  - 事実: 同上 S13!G7 L1

### S13!D8
- L1: POST
  - 事実: 実行結果.md R12 POST            api/v1/books ................... Api\V1\BookController@store ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum

### S13!E8
- L1: /api/v1/books
  - 事実: 実行結果.md R12 POST            api/v1/books ................... Api\V1\BookController@store ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: app/Providers/RouteServiceProvider.php:33 ->prefix('api')
  - 事実: routes/api.php:17 Route::prefix('v1')->group(function () {
  - 事実: routes/api.php:24 Route::post('/books', [BookController::class, 'store']);

### S13!F8
- L1: 書籍を新規登録する
  - 事実: app/Http/Controllers/Api/V1/BookController.php:61 public function store(StoreApiBookRequest $request): JsonResponse
  - 事実: app/Http/Controllers/Api/V1/BookController.php:64 $book = Book::create($request->validated() + ['user_id' => Auth::id()]);

### S13!G8
- L1: 不要
  - 事実: routes/api.php:23 Route::middleware('auth:sanctum')->group(function () {
  - 事実: 実行結果.md R12 POST            api/v1/books ................... Api\V1\BookController@store ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: 実行結果.md R245 # POST /api/v1/books（トークンなし・Acceptなし）→ {"message":"\u8a8d\u8a3c\u304c\u5fc5\u8981\u3067\u3059\u3002"} HTTP 401 ／ デコード {'message': '認証が必要です。'}

### S13!H8
- L1: ★ Sanctum 必須
  - 事実: 同上 S13!G8 L1
  - 事実: app/Models/User.php:15 use HasApiTokens, HasFactory, Notifiable;

### S13!D9
- L1: PUT
  - 事実: 実行結果.md R12 PUT             api/v1/books/{book} ........... Api\V1\BookController@update ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum

### S13!E9
- L1: /api/v1/books/{book}
  - 事実: 実行結果.md R12 PUT             api/v1/books/{book} ........... Api\V1\BookController@update ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: app/Providers/RouteServiceProvider.php:33 ->prefix('api')
  - 事実: routes/api.php:17 Route::prefix('v1')->group(function () {
  - 事実: routes/api.php:25 Route::put('/books/{book}', [BookController::class, 'update']);

### S13!F9
- L1: 書籍を更新する
  - 事実: app/Http/Controllers/Api/V1/BookController.php:78 public function update(UpdateApiBookRequest $request, Book $book): BookResource
  - 事実: app/Http/Controllers/Api/V1/BookController.php:83 $book->update($request->validated());

### S13!G9
- L1: 不要
  - 事実: 実行結果.md R12 PUT             api/v1/books/{book} ........... Api\V1\BookController@update ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: 実行結果.md R245 # PUT /api/v1/books/3（トークンなし）→ {"message":"\u8a8d\u8a3c\u304c\u5fc5\u8981\u3067\u3059\u3002"} HTTP 401

### S13!H9
- L1: ★ Sanctum + BookPolicy（所有者のみ）
  - 事実: routes/api.php:23 Route::middleware('auth:sanctum')->group(function () {
  - 事実: app/Http/Controllers/Api/V1/BookController.php:80 $this->authorize('update', $book);
  - 事実: app/Policies/BookPolicy.php:15 return $user->id === $book->user_id && ! $book->trashed();
  - 事実: tests/Feature/Api/BookApiTest.php:184-196 test_update_other_users_book_returns_403 … ->assertStatus(403) ->assertJson(['message' => 'この操作を実行する権限がありません。']); 実行結果は 実行結果.md R91 参照

### S13!D10
- L1: DELETE
  - 事実: 実行結果.md R12 DELETE          api/v1/books/{book} .......... Api\V1\BookController@destroy ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum

### S13!E10
- L1: /api/v1/books/{book}
  - 事実: 実行結果.md R12 DELETE          api/v1/books/{book} .......... Api\V1\BookController@destroy ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: app/Providers/RouteServiceProvider.php:33 ->prefix('api')
  - 事実: routes/api.php:17 Route::prefix('v1')->group(function () {
  - 事実: routes/api.php:26 Route::delete('/books/{book}', [BookController::class, 'destroy']);

### S13!F10
- L1: 書籍を削除する
  - 事実: app/Http/Controllers/Api/V1/BookController.php:95 public function destroy(Book $book): Response
  - 事実: app/Http/Controllers/Api/V1/BookController.php:99 $book->delete();

### S13!G10
- L1: 不要
  - 事実: 実行結果.md R12 DELETE          api/v1/books/{book} .......... Api\V1\BookController@destroy ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: 実行結果.md R245 # DELETE /api/v1/books/3（トークンなし）→ {"message":"\u8a8d\u8a3c\u304c\u5fc5\u8981\u3067\u3059\u3002"} HTTP 401

### S13!H10
- L1: ★ Sanctum + BookPolicy（所有者のみ）
  - 事実: routes/api.php:23 Route::middleware('auth:sanctum')->group(function () {
  - 事実: app/Http/Controllers/Api/V1/BookController.php:97 $this->authorize('delete', $book);
  - 事実: app/Policies/BookPolicy.php:23 return $user->id === $book->user_id && ! $book->trashed();
  - 事実: tests/Feature/Api/BookApiTest.php:199-212 test_destroy_other_users_book_returns_403 … ->assertStatus(403) ->assertJson(['message' => 'この操作を実行する権限がありません。']); 実行結果は 実行結果.md R91 参照

### S13!C12
- L1: AP01: 書籍一覧API
  - 事実: 実行結果.md R12 GET|HEAD        api/v1/books ................... Api\V1\BookController@index ／ ⇂ api

### S13!D13
- L1: 書籍一覧を取得するAPIエンドポイント。
  - 事実: 実行結果.md R12 GET|HEAD        api/v1/books ................... Api\V1\BookController@index ／ ⇂ api
  - 事実: app/Http/Controllers/Api/V1/BookController.php:23 public function index(IndexBookRequest $request): AnonymousResourceCollection
- L2: キーワード検索やジャンルでの絞り込み、ページネーションに対応すること。
  - 事実: app/Http/Controllers/Api/V1/BookController.php:30-34 ->when($request->keyword, function ($query, $keyword) { $query->where(function ($q) use ($keyword) { $q->where('title', 'like', "%{$keyword}%") ->orWhere('author', 'like', "%{$keyword}%");
  - 事実: app/Http/Controllers/Api/V1/BookController.php:36-37 ->when($request->genre_id, function ($query, $genreId) { $query->whereHas('genres', fn ($q) => $q->where('genres.id', $genreId));
  - 事実: app/Http/Controllers/Api/V1/BookController.php:40-41 ->paginate($perPage) ->withQueryString();
  - 事実: 実行結果.md R244 # keyword=Code → [(7, 'Clean Code')]
  - 事実: 実行結果.md R240 GET /api/v1/books?genre_id=3&page=1 → data の id 3, 7 ／ "genres": [{"id": 3, "name": "技術書"}]
  - 事実: 実行結果.md R244 # per_page=3&page=2 → [4, 5, 6] 3 2 4
- L3: 各書籍にはジャンル情報、平均評価、レビュー件数を含めること。
  - 事実: app/Http/Controllers/Api/V1/BookController.php:27-29 $books = Book::with('genres') ->withAvg('reviews', 'rating') ->withCount('reviews')
  - 事実: app/Http/Resources/BookListResource.php:24 'average_rating' => round((float) $this->reviews_avg_rating, 1),
  - 事実: app/Http/Resources/BookListResource.php:25 'reviews_count' => $this->reviews_count,
  - 事実: app/Http/Resources/BookListResource.php:26 'genres' => GenreResource::collection($this->whenLoaded('genres')),
  - 事実: 実行結果.md R244 data[0]のキー ['id', 'title', 'author', 'isbn', 'published_date', 'image_url', 'average_rating', 'reviews_count', 'genres']
- L4: レスポンス形式はAPI Resourceを使用して整形すること。
  - 事実: app/Http/Controllers/Api/V1/BookController.php:43 return BookListResource::collection($books);
  - 事実: app/Http/Resources/BookListResource.php:8 class BookListResource extends JsonResource
- L5: 【共通仕様】
  - 事実: N/A-記述（見出し）
- L6: ページネーション: デフォルトper_page=10、クライアント指定時は1〜100の範囲で上書き可（バリデーションmd確定版「公開API 書籍一覧」のper_pageルールに基づく）
  - 事実: app/Http/Controllers/Api/V1/BookController.php:25 $perPage = $request->input('per_page', 10);
  - 事実: app/Http/Requests/Api/V1/IndexBookRequest.php:18 'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
  - 事実: 実行結果.md R244 # 既定 per_page → data件数 10 meta.per_page 10 meta.total 11 meta.last_page 2
  - 事実: 実行結果.md R243 per_page=100 → HTTP 200 {"data件数": 11, "meta.per_page": 100} ／ per_page=1 → HTTP 200 {"data件数": 1, "meta.per_page": 1} ／ per_page=0 → HTTP 422 ／ per_page=101 → HTTP 422
- L7: average_rating丸め: 小数第1位固定（例: 4.5。reports/index.blade.phpのnumber_format($genre['average_rating'], 1)との一貫性）
  - 事実: app/Http/Resources/BookListResource.php:24 'average_rating' => round((float) $this->reviews_avg_rating, 1),
  - 事実: app/Http/Resources/BookResource.php:25 'average_rating' => round((float) $this->reviews_avg_rating, 1),
  - 事実: 実行結果.md R240 出力の生JSON "average_rating":4 （id=3）／ "average_rating":2.5 （id=7）
  - 事実: resources/views/reports/index.blade.php:126 {{ number_format($genre['average_rating'], 1) }}
- L8: 日付形式（date型）: Y-m-d（例: "2012-06-23"）
  - 事実: app/Http/Resources/BookListResource.php:22 'published_date' => $this->published_date?->format('Y-m-d'),
  - 事実: app/Http/Resources/BookResource.php:22 'published_date' => $this->published_date?->format('Y-m-d'),
  - 事実: 実行結果.md R240 "published_date":"2012-06-23"
- L9: 日時形式（datetime型）: ISO8601・JSTオフセット付き（例: "2026-08-01T10:00:00+09:00"。Laravel標準のtoJSON()挙動）
  - 事実: app/Http/Resources/ReviewResource.php:21 'created_at' => $this->created_at->toIso8601String(),
  - 事実: config/app.php:73 'timezone' => 'Asia/Tokyo',
  - 事実: 実行結果.md R241 "created_at":"2026-09-27T09:49:38+09:00"
- L10: バリデーションエラー形式: {"message": string, "errors": {field: [string, ...]}}
  - 事実: app/Http/Requests/Api/V1/ApiFormRequest.php:24-27 throw new HttpResponseException(response()->json([ 'message' => '入力内容に誤りがあります。', 'errors' => $validator->errors(), ], 422));
  - 事実: 実行結果.md R243 per_page=0 → HTTP 422 {"message": "入力内容に誤りがあります。", "errors": {"per_page": ["取得件数は1〜100の範囲で指定してください"]}}
- L11: 存在しないIDのエラー形式: {"message": string}（errorsキーなし）
  - 事実: app/Exceptions/Handler.php:35-37 $this->renderable(function (NotFoundHttpException $e, Request $request) { if ($request->is('api/*')) { return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
  - 事実: 実行結果.md R242 curl "$B/api/v1/books/99999" → {"message":"\u6307\u5b9a…"} HTTP 404 ／ デコード {'message': '指定された書籍が見つかりません。'} ／ curl "$B/api/v1/books/abc" → HTTP 404
- L12: ジャンル指定（POST/PUT）: genres（配列。Web版genres[]と同一フィールド名・同一ルールをAPIでも踏襲）
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:21-22 'genres' => ['required', 'array', 'min:1'], 'genres.*' => ['exists:genres,id'],
  - 事実: app/Http/Requests/Api/V1/UpdateApiBookRequest.php:28-29 'genres' => ['required', 'array', 'min:1'], 'genres.*' => ['exists:genres,id'],
  - 事実: app/Http/Requests/StoreBookRequest.php:31-32 'genres' => ['required', 'array', 'min:1'], 'genres.*' => ['exists:genres,id'],
  - 事実: app/Http/Controllers/Api/V1/BookController.php:65 $book->genres()->sync($request->genres);
  - 事実: app/Http/Controllers/Api/V1/BookController.php:84 $book->genres()->sync($request->genres);
- L13: 【リクエスト】
  - 事実: N/A-記述（見出し）
- L14: keyword: nullable, string, max:255 / エラー文言: 検索キーワードは255文字以内で入力してください
  - 事実: app/Http/Requests/Api/V1/IndexBookRequest.php:15 'keyword' => ['nullable', 'string', 'max:255'],
  - 事実: app/Http/Requests/Api/V1/IndexBookRequest.php:30 'keyword.max' => '検索キーワードは255文字以内で入力してください',
  - 事実: 実行結果.md R243 keyword=（256文字）→ HTTP 422 {"message": "入力内容に誤りがあります。", "errors": {"keyword": ["検索キーワードは255文字以内で入力してください"]}}
- L15: genre_id: nullable, integer, exists:genres,id / エラー文言: 指定されたジャンルが存在しません
  - 事実: app/Http/Requests/Api/V1/IndexBookRequest.php:16 'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
  - 事実: app/Http/Requests/Api/V1/IndexBookRequest.php:31-32 'genre_id.integer' => '指定されたジャンルが存在しません', 'genre_id.exists' => '指定されたジャンルが存在しません',
  - 事実: 実行結果.md R243 genre_id=99999 → HTTP 422 {"genre_id": ["指定されたジャンルが存在しません"]} ／ genre_id=abc → HTTP 422 {"genre_id": ["指定されたジャンルが存在しません"]}
- L16: page: nullable, integer, min:1 / エラー文言: ページ番号は1以上の整数で指定してください
  - 事実: app/Http/Requests/Api/V1/IndexBookRequest.php:17 'page' => ['nullable', 'integer', 'min:1'],
  - 事実: app/Http/Requests/Api/V1/IndexBookRequest.php:33-34 'page.integer' => 'ページ番号は1以上の整数で指定してください', 'page.min' => 'ページ番号は1以上の整数で指定してください',
  - 事実: 実行結果.md R243 page=0 → HTTP 422 {"page": ["ページ番号は1以上の整数で指定してください"]} ／ page=abc → HTTP 422 同文言
- L17: per_page: nullable, integer, min:1, max:100（デフォルト10） / エラー文言: 取得件数は1〜100の範囲で指定してください
  - 事実: app/Http/Requests/Api/V1/IndexBookRequest.php:18 'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
  - 事実: app/Http/Requests/Api/V1/IndexBookRequest.php:35-37 'per_page.integer' / 'per_page.min' / 'per_page.max' => '取得件数は1〜100の範囲で指定してください',
  - 事実: app/Http/Controllers/Api/V1/BookController.php:25 $perPage = $request->input('per_page', 10);
  - 事実: 実行結果.md R243 per_page=0 / 101 / abc → いずれも HTTP 422 {"per_page": ["取得件数は1〜100の範囲で指定してください"]}
- L18: 例: GET /api/v1/books?genre_id=3&page=1
  - 事実: 実行結果.md R240 curl "$B/api/v1/books?genre_id=3&page=1" → HTTP 200
- L19: 【レスポンス（200・実データ例。シーディング要件シート9確定データ、genre_id=3で絞り込み）】
  - 事実: 実行結果.md R240 curl "$B/api/v1/books?genre_id=3&page=1" → HTTP 200
  - 事実: 実行結果.md R100 book_genre 行 3	3 ／ 7	3（genre_id=3 の book_id は 3 と 7）
- L20: {
  - 事実: 実行結果.md R240 出力の生JSON先頭 {"data":[
- L21:   "data": [
  - 事実: 実行結果.md R240 出力の生JSON先頭 {"data":[
- L22:     {"id": 3, "title": "リーダブルコード", "author": "Dustin Boswell", "isbn": "9784873115658", "published_date": "2012-06-23", "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=3", "average_rating": 4.5, "reviews_count": 3, "genres": [{"id": 3, "name": "技術書"}]},
  - 事実: 実行結果.md R240 デコード data[0] {"id": 3, "title": "リーダブルコード", "author": "Dustin Boswell", "isbn": "9784873115658", "published_date": "2012-06-23", "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=3", "average_rating": 4, "reviews_count": 3, "genres": [{"id": 3, "name": "技術書"}]}
  - 事実: 実行結果.md R100 reviews book_id=3 の行 9	2	3	4 ／ 10	3	3	5 ／ 11	4	3	3（rating 4, 5, 3）
- L23:     {"id": 7, "title": "Clean Code", "author": "Robert C. Martin", "isbn": "9784048930598", "published_date": "2017-12-18", "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=7", "average_rating": 4.0, "reviews_count": 2, "genres": [{"id": 3, "name": "技術書"}]}
  - 事実: 実行結果.md R240 デコード data[1] {"id": 7, "title": "Clean Code", "author": "Robert C. Martin", "isbn": "9784048930598", "published_date": "2017-12-18", "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=7", "average_rating": 2.5, "reviews_count": 4, "genres": [{"id": 3, "name": "技術書"}]}
  - 事実: 実行結果.md R100 reviews book_id=7 の行 19	1	7	2 ／ 20	2	7	4 ／ 21	4	7	1 ／ 22	5	7	3（rating 2, 4, 1, 3）
- L24:   ],
  - 事実: 実行結果.md R240 出力の生JSON …"genres":[{"id":3,"name":"\u6280\u8853\u66f8"}]}],"links":{
- L25:   "links": {"first": "/api/v1/books?genre_id=3&page=1", "last": "/api/v1/books?genre_id=3&page=1", "prev": null, "next": null},
  - 事実: 実行結果.md R240 デコード "links": {"first": "http://localhost:8022/api/v1/books?genre_id=3&page=1", "last": "http://localhost:8022/api/v1/books?genre_id=3&page=1", "prev": null, "next": null}
- L26:   "meta": {"current_page": 1, "last_page": 1, "per_page": 10, "total": 2}
  - 事実: 実行結果.md R240 デコード "meta": {"current_page": 1, "from": 1, "last_page": 1, "links": [{"url": null, "label": "&laquo; Previous", "active": false}, {"url": "http://localhost:8022/api/v1/books?genre_id=3&page=1", "label": "1", "active": true}, {"url": null, "label": "Next &raquo;", "active": false}], "path": "http://localhost:8022/api/v1/books", "per_page": 10, "to": 2, "total": 2}
- L27: }
  - 事実: 実行結果.md R240 出力の生JSON末尾 "per_page":10,"to":2,"total":2}}
- L28: ※ descriptionは一覧に含めない（詳細APIのみで返却。一覧ペイロードを軽くするための確定仕様）
  - 事実: app/Http/Resources/BookListResource.php:17-27 return [ 'id', 'title', 'author', 'isbn', 'published_date', 'image_url', 'average_rating', 'reviews_count', 'genres' ]（description キーの行なし）
  - 事実: app/Http/Resources/BookResource.php:23 'description' => $this->description,
  - 事実: 実行結果.md R244 data[0]のキー ['id', 'title', 'author', 'isbn', 'published_date', 'image_url', 'average_rating', 'reviews_count', 'genres']
- L29: 【レスポンス（422・パラメータ不正例）】
  - 事実: N/A-記述（見出し）
- L30: {"message": "入力内容に誤りがあります。", "errors": {"per_page": ["取得件数は1〜100の範囲で指定してください"]}}
  - 事実: 実行結果.md R243 # GET /api/v1/books?per_page=0 → HTTP 422 {"message": "入力内容に誤りがあります。", "errors": {"per_page": ["取得件数は1〜100の範囲で指定してください"]}}
- L31: 【ステータスコード】
  - 事実: N/A-記述（見出し）
- L32: 200: パラメータが正常（該当0件でもdata: []で200）
  - 事実: 実行結果.md R244 # 該当0件（keyword=zzzzzz）→ {"data":[],"links":{…},"meta":{"current_page":1,"from":null,"last_page":1,…,"per_page":10,"to":null,"total":0}} HTTP 200
  - 事実: 実行結果.md R240 HTTP 200
- L33: 422: keyword/genre_id/page/per_pageのいずれかがルール違反
  - 事実: 実行結果.md R243 keyword=（256文字）→ HTTP 422 ／ genre_id=99999 → HTTP 422 ／ genre_id=abc → HTTP 422 ／ page=0 → HTTP 422 ／ page=abc → HTTP 422 ／ per_page=0 → HTTP 422 ／ per_page=101 → HTTP 422 ／ per_page=abc → HTTP 422
  - 事実: app/Http/Requests/Api/V1/IndexBookRequest.php:5 class IndexBookRequest extends ApiFormRequest
  - 事実: app/Http/Requests/Api/V1/ApiFormRequest.php:24-27 throw new HttpResponseException(response()->json([ 'message' => '入力内容に誤りがあります。', 'errors' => $validator->errors(), ], 422));

### S13!C15
- L1: AP02: 書籍詳細API
  - 事実: 実行結果.md R12 GET|HEAD        api/v1/books/{book} ............. Api\V1\BookController@show ／ ⇂ api

### S13!D16
- L1: 指定IDの書籍詳細を取得するAPIエンドポイント。
  - 事実: 実行結果.md R12 GET|HEAD        api/v1/books/{book} ............. Api\V1\BookController@show ／ ⇂ api
  - 事実: app/Http/Controllers/Api/V1/BookController.php:49 public function show(Book $book): BookResource
- L2: ジャンル情報とレビュー（投稿者名・評価・コメント・投稿日時）を含めること。
  - 事実: app/Http/Controllers/Api/V1/BookController.php:51 $book->load(['genres', 'reviews.user'])
  - 事実: app/Http/Resources/BookResource.php:27-28 'genres' => GenreResource::collection($this->whenLoaded('genres')), 'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
  - 事実: app/Http/Resources/ReviewResource.php:18-21 'user_name' => $this->user->name, 'rating' => $this->rating, 'comment' => $this->comment, 'created_at' => $this->created_at->toIso8601String(),
  - 事実: 実行結果.md R241 デコード "reviews": [{"user_name": "鈴木花子", "rating": 4, "comment": "とても参考になりました。", "created_at": "2026-09-27T09:49:38+09:00"}, …]
- L3: 存在しないIDが指定された場合はエラーレスポンスを返すこと。
  - 事実: app/Exceptions/Handler.php:35-37 $this->renderable(function (NotFoundHttpException $e, Request $request) { if ($request->is('api/*')) { return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
  - 事実: 実行結果.md R242 curl "$B/api/v1/books/99999" → {"message":"\u6307\u5b9a…"} HTTP 404 ／ デコード {'message': '指定された書籍が見つかりません。'} ／ curl "$B/api/v1/books/abc" → HTTP 404
  - 事実: 実行結果.md R22 GET /api/v1/books/99999 -> 404
- L4: レスポンス形式はAPI Resourceを使用して整形すること。
  - 事実: app/Http/Controllers/Api/V1/BookController.php:55 return new BookResource($book);
  - 事実: app/Http/Resources/BookResource.php:8 class BookResource extends JsonResource
- L5: 【レスポンス（200・実データ例、id=3 リーダブルコード）】
  - 事実: 実行結果.md R241 curl "$B/api/v1/books/3" → HTTP 200
- L6: {
  - 事実: 実行結果.md R241 出力の生JSON先頭 {"data":{
- L7:   "data": {
  - 事実: 実行結果.md R241 出力の生JSON先頭 {"data":{
- L8:     "id": 3, "title": "リーダブルコード", "author": "Dustin Boswell", "isbn": "9784873115658", "published_date": "2012-06-23",
  - 事実: 実行結果.md R241 デコード "id": 3, "title": "リーダブルコード", "author": "Dustin Boswell", "isbn": "9784873115658", "published_date": "2012-06-23",
- L9:     "description": "より良いコードを書くためのシンプルで実践的なテクニックを解説する一冊。",
  - 事実: 実行結果.md R241 デコード "description": "他人が読んで理解しやすいコードを書くための実践的なテクニックを、命名・コメント・制御フローなどの具体例とともに解説する。"
- L10:     "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=3", "average_rating": 4.5, "reviews_count": 3,
  - 事実: 実行結果.md R241 デコード "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=3", "average_rating": 4, "reviews_count": 3,
- L11:     "genres": [{"id": 3, "name": "技術書"}],
  - 事実: 実行結果.md R241 デコード "genres": [{"id": 3, "name": "技術書"}],
- L12:     "reviews": [
  - 事実: 実行結果.md R241 デコード "reviews": [
  - 事実: app/Http/Resources/BookResource.php:28 'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
- L13:       {"user_name": "佐藤美咲", "rating": 5, "comment": "変数名の付け方だけでも読む価値がありました。", "created_at": "2026-08-01T10:00:00+09:00"},
  - 事実: 実行結果.md R241 デコード reviews[0] {"user_name": "鈴木花子", "rating": 4, "comment": "とても参考になりました。", "created_at": "2026-09-27T09:49:38+09:00"}
  - 事実: 実行結果.md R100 reviews 9	2	3	4	とても参考になりました。	2026-09-27 09:49:38 ／ users 2	鈴木花子
- L14:       {"user_name": "高橋健太", "rating": 4, "comment": null, "created_at": "2026-08-05T12:30:00+09:00"},
  - 事実: 実行結果.md R241 デコード reviews[1] {"user_name": "田中一郎", "rating": 5, "comment": "人生が変わりました。", "created_at": "2026-09-27T09:49:38+09:00"}
  - 事実: 実行結果.md R100 reviews 10	3	3	5	人生が変わりました。	2026-09-27 09:49:38 ／ users 3	田中一郎
- L15:       {"user_name": "田中一郎", "rating": 5, "comment": "新人研修の課題図書にしたいレベル。", "created_at": "2026-08-10T09:15:00+09:00"}
  - 事実: 実行結果.md R241 デコード reviews[2] {"user_name": "佐藤美咲", "rating": 3, "comment": "普通でした。", "created_at": "2026-09-27T09:49:38+09:00"}
  - 事実: 実行結果.md R100 reviews 11	4	3	3	普通でした。	2026-09-27 09:49:38 ／ users 4	佐藤美咲
- L16:     ]
  - 事実: 実行結果.md R241 出力の生JSON末尾 "created_at":"2026-09-27T09:49:38+09:00"}]}}
- L17:   }
  - 事実: 同上 S13!D16 L16
- L18: }
  - 事実: 同上 S13!D16 L16
- L19: ※ commentのnull許容は確定済みDB設計（reviews.comment NULL許可）と一致。user_nameはreviews.userのeager loadから取得（Blade show.blade.phpと同じリレーション経路）
  - 事実: database/migrations/2026_09_01_115251_create_reviews_table.php:19 $table->text('comment')->nullable();
  - 事実: app/Http/Resources/ReviewResource.php:20 'comment' => $this->comment,
  - 事実: app/Http/Resources/ReviewResource.php:18 'user_name' => $this->user->name,
  - 事実: app/Http/Controllers/Api/V1/BookController.php:51 $book->load(['genres', 'reviews.user'])
  - 事実: app/Http/Controllers/BookController.php:145-148 $book->load([ … 'reviews.user',
- L20: ※ description本文・レビューcomment本文は例示テキスト。数値・ID・タイトルの各構造化項目のみ実データ準拠
  - 事実: N/A-記述（例示データの扱いについての注記）
- L21: 【レスポンス（404・存在しないID／削除済みIDも同様。SoftDeleteの標準除外挙動）】
  - 事実: app/Exceptions/Handler.php:35-37 $this->renderable(function (NotFoundHttpException $e, Request $request) { if ($request->is('api/*')) { return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
  - 事実: app/Models/Book.php:14 use HasFactory, SoftDeletes;
  - 事実: 実行結果.md R22 # 削除済み書籍（deleted_at IS NOT NULL）の最小IDは NULL のため、削除済み書籍での {book} ルートの実行対象なし
  - 事実: tests/Feature/Api/BookApiTest.php:224-230 test_show_soft_deleted_returns_404 … $book->delete(); $this->getJson("/api/v1/books/{$book->id}")->assertStatus(404); 実行結果は 実行結果.md R91 参照
- L22: {"message": "指定された書籍が見つかりません。"}
  - 事実: app/Exceptions/Handler.php:37 return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
  - 事実: 実行結果.md R242 curl "$B/api/v1/books/99999" → {"message":"\u6307\u5b9a…"} HTTP 404 ／ デコード {'message': '指定された書籍が見つかりません。'} ／ curl "$B/api/v1/books/abc" → HTTP 404
- L23: 【ステータスコード】
  - 事実: N/A-記述（見出し）
- L24: 200: 対象IDが存在する
  - 事実: 実行結果.md R241 HTTP 200
  - 事実: 実行結果.md R22 GET /api/v1/books/1 -> 200
- L25: 404: 対象IDが存在しない、または削除済み（SoftDeleteで標準除外）
  - 事実: 実行結果.md R242 /api/v1/books/99999 → HTTP 404
  - 事実: 同上 S13!D16 L21

### S13!C18
- L1: AP03: 書籍登録API
  - 事実: 実行結果.md R12 POST            api/v1/books ................... Api\V1\BookController@store ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum

### S13!D19
- L1: 書籍を新規登録するAPIエンドポイント。
  - 事実: 実行結果.md R12 POST            api/v1/books ................... Api\V1\BookController@store ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: app/Http/Controllers/Api/V1/BookController.php:61-72 public function store(StoreApiBookRequest $request): JsonResponse … return (new BookResource($book))->response()->setStatusCode(Response::HTTP_CREATED);
- L2: バリデーションエラー時は日本語のエラーメッセージを返すこと。
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:31-49 public function messages(): array { return [ 'title.required' => 'タイトルを入力してください', … 'genres.*.exists' => '選択されたジャンルが存在しません', ];
  - 事実: app/Http/Requests/Api/V1/ApiFormRequest.php:24-27 throw new HttpResponseException(response()->json([ 'message' => '入力内容に誤りがあります。', 'errors' => $validator->errors(), ], 422));
  - 事実: tests/Feature/Api/BookApiTest.php:233-240 test_store_validation_error_when_authenticated … ->assertStatus(422) ->assertJsonStructure(['message', 'errors']); 実行結果は 実行結果.md R91 参照
  - 事実: 認証付きPOSTの実行はDBへのトークン作成を伴うため未実行（実行結果.md R245 はトークンなしで HTTP 401）
- L3: 登録成功時のHTTPステータスコードを適切に設定すること。
  - 事実: app/Http/Controllers/Api/V1/BookController.php:72 return (new BookResource($book))->response()->setStatusCode(Response::HTTP_CREATED);
  - 事実: tests/Feature/Api/BookApiTest.php:111-121 test_store_success_with_sanctum … ->assertStatus(201) 実行結果は 実行結果.md R91 参照
- L4: 【バリデーション（Web版books.storeと同一ルール＋user_id追加）】
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:14-23 'title' / 'author' / 'isbn' => ['required', 'string', 'regex:/^[0-9]{13}$/', 'unique:books,isbn'] / 'published_date' => ['required', 'date'] / 'description' / 'image_url' / 'genres' / 'genres.*'
  - 事実: app/Http/Requests/StoreBookRequest.php:27-28 'isbn' => ['nullable', 'string', 'regex:/^[0-9]{13}$/', 'unique:books,isbn'], 'published_date' => ['nullable', 'date'],
  - 事実: 該当なし grep -rn "user_id" app/Http/Requests/Api/ → 出力0件（exit=1）
- L5: title: required, string, max:255 / タイトルを入力してください／タイトルは255文字以内で入力してください
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:15 'title' => ['required', 'string', 'max:255'],
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:34-35 'title.required' => 'タイトルを入力してください', 'title.max' => 'タイトルは255文字以内で入力してください',
- L6: author: required, string, max:255 / 著者名を入力してください／著者名は255文字以内で入力してください
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:16 'author' => ['required', 'string', 'max:255'],
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:36-37 'author.required' => '著者名を入力してください', 'author.max' => '著者名は255文字以内で入力してください',
- L7: isbn: required, string, regex:/^[0-9]{13}$/, unique:books,isbn / ISBNを入力してください／ISBNは13桁の数字で入力してください／このISBNは既に登録されています
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:17 'isbn' => ['required', 'string', 'regex:/^[0-9]{13}$/', 'unique:books,isbn'],
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:38-40 'isbn.required' => 'ISBNを入力してください', 'isbn.regex' => 'ISBNは13桁の数字で入力してください', 'isbn.unique' => 'このISBNは既に登録されています',
- L8: published_date: required, date / 出版日を入力してください／出版日は正しい日付形式で入力してください
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:18 'published_date' => ['required', 'date'],
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:41-42 'published_date.required' => '出版日を入力してください', 'published_date.date' => '出版日は正しい日付形式で入力してください',
- L9: description: nullable, string, max:1000 / 説明は1000文字以内で入力してください
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:19 'description' => ['nullable', 'string', 'max:1000'],
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:43 'description.max' => '説明は1000文字以内で入力してください',
- L10: image_url: nullable, url, max:255 / 画像URLの形式が正しくありません／画像URLは255文字以内で入力してください
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:20 'image_url' => ['nullable', 'url', 'max:255'],
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:44-45 'image_url.url' => '画像URLの形式が正しくありません', 'image_url.max' => '画像URLは255文字以内で入力してください',
- L11: genres: required, array, min:1 ／ genres.*: exists:genres,id / ジャンルを1つ以上選択してください／選択されたジャンルが存在しません
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:21-22 'genres' => ['required', 'array', 'min:1'], 'genres.*' => ['exists:genres,id'],
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:46-48 'genres.required' => 'ジャンルを1つ以上選択してください', 'genres.min' => 'ジャンルを1つ以上選択してください', 'genres.*.exists' => '選択されたジャンルが存在しません',
- L12: user_id: required, integer, exists:users,id / 登録者IDを指定してください／指定された登録者が存在しません
  - 事実: 該当なし grep -rn "user_id" app/Http/Requests/Api/ → 出力0件（exit=1）
  - 事実: app/Http/Controllers/Api/V1/BookController.php:64 $book = Book::create($request->validated() + ['user_id' => Auth::id()]);
- L13: ※ user_idが必須なのは基本段階では認証なし（Auth::id()が使えない）ため。応用段階でSanctum導入後はAuth::id()取得方式に切替予定
  - 事実: app/Http/Controllers/Api/V1/BookController.php:64 $book = Book::create($request->validated() + ['user_id' => Auth::id()]);
  - 事実: routes/api.php:23 Route::middleware('auth:sanctum')->group(function () {
- L14: 【リクエスト例】
  - 事実: N/A-記述（見出し）
- L15: {"title": "プログラマー脳", "author": "Felienne Hermans", "isbn": "9784798068718", "published_date": "2023-01-01", "description": "コードを読み書きする際に脳内で何が起きているかを認知科学の観点から解説する。", "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=12", "genres": [3], "user_id": 3}
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:14-23 rules のキー 'title', 'author', 'isbn', 'published_date', 'description', 'image_url', 'genres', 'genres.*'（'user_id' キーの行なし）
  - 事実: app/Http/Controllers/Api/V1/BookController.php:64 $book = Book::create($request->validated() + ['user_id' => Auth::id()]);
  - 事実: 該当なし grep -n "9784798068718" docs/04_検品結果/照合調査/実行結果.md → 出力0件（exit=1）
- L16: 【レスポンス（201・成功。AP02のdata形式からreviewsキーを除いた形。user_idはレスポンスに含めない）】
  - 事実: app/Http/Controllers/Api/V1/BookController.php:70 $book->load('genres')->loadAvg('reviews', 'rating')->loadCount('reviews');
  - 事実: app/Http/Controllers/Api/V1/BookController.php:72 return (new BookResource($book))->response()->setStatusCode(Response::HTTP_CREATED);
  - 事実: app/Http/Resources/BookResource.php:28 'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
  - 事実: app/Http/Resources/BookResource.php:17-29 return [ 'id', 'title', 'author', 'isbn', 'published_date', 'description', 'image_url', 'average_rating', 'reviews_count', 'genres', 'reviews' ]（user_id キーの行なし）
- L17: {"data": {"id": 12, "title": "プログラマー脳", "author": "Felienne Hermans", "isbn": "9784798068718", "published_date": "2023-01-01", "description": "コードを読み書きする際に脳内で何が起きているかを認知科学の観点から解説する。", "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=12", "average_rating": 0, "reviews_count": 0, "genres": [{"id": 3, "name": "技術書"}]}}
  - 事実: app/Http/Resources/BookResource.php:17-29 return [ 'id' => $this->id, … 'average_rating' => round((float) $this->reviews_avg_rating, 1), 'reviews_count' => $this->reviews_count, 'genres' => GenreResource::collection($this->whenLoaded('genres')), 'reviews' => ReviewResource::collection($this->whenLoaded('reviews')), ];
  - 事実: tests/Feature/Api/BookApiTest.php:117-118 ->assertStatus(201) ->assertJsonPath('data.title', 'API書籍'); 実行結果は 実行結果.md R91 参照
  - 事実: 登録の実レスポンスはDB変更を伴うため未実行
- L18: 【レスポンス（422・バリデーションエラー例）】
  - 事実: N/A-記述（見出し）
- L19: {"message": "入力内容に誤りがあります。", "errors": {"title": ["タイトルを入力してください"], "isbn": ["このISBNは既に登録されています"]}}
  - 事実: app/Http/Requests/Api/V1/ApiFormRequest.php:24-27 throw new HttpResponseException(response()->json([ 'message' => '入力内容に誤りがあります。', 'errors' => $validator->errors(), ], 422));
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:34 'title.required' => 'タイトルを入力してください',
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:40 'isbn.unique' => 'このISBNは既に登録されています',
  - 事実: tests/Feature/Api/BookApiTest.php:233-240 ->assertStatus(422) ->assertJsonStructure(['message', 'errors']); 実行結果は 実行結果.md R91 参照
- L20: 【ステータスコード】
  - 事実: N/A-記述（見出し）
- L21: 201: 登録成功
  - 事実: app/Http/Controllers/Api/V1/BookController.php:72 ->setStatusCode(Response::HTTP_CREATED);
  - 事実: tests/Feature/Api/BookApiTest.php:117 ->assertStatus(201) 実行結果は 実行結果.md R91 参照
- L22: 422: バリデーションエラー
  - 事実: app/Http/Requests/Api/V1/ApiFormRequest.php:24-27 throw new HttpResponseException(response()->json([ 'message' => '入力内容に誤りがあります。', 'errors' => $validator->errors(), ], 422));
  - 事実: tests/Feature/Api/BookApiTest.php:238 ->assertStatus(422) 実行結果は 実行結果.md R91 参照

### S13!C21
- L1: AP04: 書籍更新API
  - 事実: 実行結果.md R12 PUT             api/v1/books/{book} ........... Api\V1\BookController@update ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum

### S13!D22
- L1: 指定IDの書籍を更新するAPIエンドポイント。
  - 事実: 実行結果.md R12 PUT             api/v1/books/{book} ........... Api\V1\BookController@update ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: app/Http/Controllers/Api/V1/BookController.php:78-90 public function update(UpdateApiBookRequest $request, Book $book): BookResource … return new BookResource($book);
- L2: 存在しないIDの場合はエラーレスポンスを返すこと。
  - 事実: app/Exceptions/Handler.php:35-37 $this->renderable(function (NotFoundHttpException $e, Request $request) { if ($request->is('api/*')) { return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
  - 事実: app/Http/Kernel.php:44 \Illuminate\Routing\Middleware\SubstituteBindings::class,
  - 事実: 実行結果.md R245 # PUT /api/v1/books/99999（トークンなし）→ HTTP 401（トークン付きの実行はDBへのトークン作成を伴うため未実行）
- L3: バリデーションルールは書籍登録と同等（ただしISBNの一意性チェックでは自身を除外）。
  - 事実: app/Http/Requests/Api/V1/UpdateApiBookRequest.php:19-24 'isbn' => [ 'required', 'string', 'regex:/^[0-9]{13}$/', Rule::unique('books', 'isbn')->ignore($this->route('book')), ],
  - 事実: app/Http/Requests/Api/V1/UpdateApiBookRequest.php:17-29 title / author / isbn / published_date / description / image_url / genres / genres.* の各ルール
- L4: 【バリデーション（AP03の全項目を同一ルールで適用。差分はisbnのみ）】
  - 事実: app/Http/Requests/Api/V1/UpdateApiBookRequest.php:16-30 return [ 'title' => ['required', 'string', 'max:255'], 'author' => ['required', 'string', 'max:255'], 'isbn' => [ … ], 'published_date' => ['required', 'date'], 'description' => ['nullable', 'string', 'max:1000'], 'image_url' => ['nullable', 'url', 'max:255'], 'genres' => ['required', 'array', 'min:1'], 'genres.*' => ['exists:genres,id'], ];
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:14-23 return [ …同じキー… 'isbn' => ['required', 'string', 'regex:/^[0-9]{13}$/', 'unique:books,isbn'], … ];
- L5: isbn: required, string, regex:/^[0-9]{13}$/, unique:books,isbn,{book},id（自身のレコードを除外） / ISBNは13桁の数字で入力してください／このISBNは既に登録されています
  - 事実: app/Http/Requests/Api/V1/UpdateApiBookRequest.php:19-24 'isbn' => [ 'required', 'string', 'regex:/^[0-9]{13}$/', Rule::unique('books', 'isbn')->ignore($this->route('book')), ],
  - 事実: app/Http/Requests/Api/V1/UpdateApiBookRequest.php:45-47 'isbn.required' => 'ISBNを入力してください', 'isbn.regex' => 'ISBNは13桁の数字で入力してください', 'isbn.unique' => 'このISBNは既に登録されています',
- L6: ※ user_idもAP03と同一ルールでバリデーション対象
  - 事実: 該当なし grep -rn "user_id" app/Http/Requests/Api/ → 出力0件（exit=1）
  - 事実: app/Http/Controllers/Api/V1/BookController.php:83 $book->update($request->validated());
- L7: 【リクエスト例】AP03と同一形式
  - 事実: app/Http/Requests/Api/V1/UpdateApiBookRequest.php:16-30 rules のキー 'title', 'author', 'isbn', 'published_date', 'description', 'image_url', 'genres', 'genres.*'
- L8: 【レスポンス（200）】AP02のdata形式（reviewsキーを除く）
  - 事実: app/Http/Controllers/Api/V1/BookController.php:87-89 $book->load('genres')->loadAvg('reviews', 'rating')->loadCount('reviews'); return new BookResource($book);
  - 事実: app/Http/Resources/BookResource.php:28 'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
  - 事実: tests/Feature/Api/BookApiTest.php:124-138 test_update_success_with_sanctum … ->assertOk() ->assertJsonPath('data.title', '更新後タイトル'); 実行結果は 実行結果.md R91 参照
- L9: 【レスポンス（404）】{"message": "指定された書籍が見つかりません。"}
  - 事実: app/Exceptions/Handler.php:37 return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
  - 事実: 実行結果.md R245 # PUT /api/v1/books/99999（トークンなし）→ HTTP 401
- L10: 【レスポンス（422）】AP03と同一形式
  - 事実: app/Http/Requests/Api/V1/UpdateApiBookRequest.php:7 class UpdateApiBookRequest extends ApiFormRequest
  - 事実: app/Http/Requests/Api/V1/ApiFormRequest.php:24-27 throw new HttpResponseException(response()->json([ 'message' => '入力内容に誤りがあります。', 'errors' => $validator->errors(), ], 422));
- L11: 【ステータスコード】
  - 事実: N/A-記述（見出し）
- L12: 200: 更新成功
  - 事実: app/Http/Controllers/Api/V1/BookController.php:78 public function update(UpdateApiBookRequest $request, Book $book): BookResource
  - 事実: app/Http/Controllers/Api/V1/BookController.php:89 return new BookResource($book);
  - 事実: tests/Feature/Api/BookApiTest.php:134 ->assertOk() 実行結果は 実行結果.md R91 参照
- L13: 404: 対象IDが存在しない
  - 事実: app/Exceptions/Handler.php:35-37 $this->renderable(function (NotFoundHttpException $e, Request $request) { if ($request->is('api/*')) { return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
  - 事実: 実行結果.md R245 # PUT /api/v1/books/99999（トークンなし）→ HTTP 401
- L14: 422: バリデーションエラー
  - 事実: app/Http/Requests/Api/V1/ApiFormRequest.php:24-27 throw new HttpResponseException(response()->json([ 'message' => '入力内容に誤りがあります。', 'errors' => $validator->errors(), ], 422));

### S13!C24
- L1: AP05: 書籍削除API
  - 事実: 実行結果.md R12 DELETE          api/v1/books/{book} .......... Api\V1\BookController@destroy ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum

### S13!D25
- L1: 指定IDの書籍を削除するAPIエンドポイント。
  - 事実: 実行結果.md R12 DELETE          api/v1/books/{book} .......... Api\V1\BookController@destroy ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: app/Http/Controllers/Api/V1/BookController.php:95-102 public function destroy(Book $book): Response { $this->authorize('delete', $book); $book->delete(); return response()->noContent(); }
- L2: 関連データ（レビュー・お気に入り・ジャンル紐付け）も適切に処理されること。
  - 事実: app/Http/Controllers/Api/V1/BookController.php:99 $book->delete();
  - 事実: app/Models/Book.php:14 use HasFactory, SoftDeletes;
  - 事実: database/migrations/2026_09_01_115251_create_reviews_table.php:17 $table->foreignId('book_id')->constrained()->cascadeOnDelete();
  - 事実: database/migrations/2026_09_01_115252_create_book_genre_table.php:15 $table->foreignId('book_id')->constrained()->cascadeOnDelete();
  - 事実: database/migrations/2026_09_01_115253_create_favorites_table.php:16 $table->foreignId('book_id')->constrained()->cascadeOnDelete();
- L3: 削除成功時のHTTPステータスコードを適切に設定すること。
  - 事実: app/Http/Controllers/Api/V1/BookController.php:101 return response()->noContent();
  - 事実: tests/Feature/Api/BookApiTest.php:141-148 test_destroy_success_with_sanctum … ->assertStatus(204); $this->assertSoftDeleted('books', ['id' => $book->id]); 実行結果は 実行結果.md R91 参照
- L4: 【レスポンス（204・成功）】本文なし
  - 事実: app/Http/Controllers/Api/V1/BookController.php:101 return response()->noContent();
- L5: 【レスポンス（404）】{"message": "指定された書籍が見つかりません。"}
  - 事実: app/Exceptions/Handler.php:37 return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
  - 事実: 実行結果.md R245 # DELETE /api/v1/books/99999（トークンなし）→ HTTP 401
- L6: 【ステータスコード】
  - 事実: N/A-記述（見出し）
- L7: 204: 削除成功（本文なし）
  - 事実: app/Http/Controllers/Api/V1/BookController.php:101 return response()->noContent();
  - 事実: tests/Feature/Api/BookApiTest.php:147 ->assertStatus(204); 実行結果は 実行結果.md R91 参照
- L8: 404: 対象IDが存在しない
  - 事実: app/Exceptions/Handler.php:35-37 $this->renderable(function (NotFoundHttpException $e, Request $request) { if ($request->is('api/*')) { return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
  - 事実: 実行結果.md R245 # DELETE /api/v1/books/99999（トークンなし）→ HTTP 401
- L9: 【関連データの扱い（面談②反映・確定）】
  - 事実: N/A-記述（見出し）
- L10: 書籍は物理削除ではなく論理削除（SoftDelete）される。reviews／favorites／book_genreのレコードは削除されず保持される（favorites／book_genre起点のcascade設定はSoftDelete下では発火しない）。204/404というレスポンス形状はcascade方式・SoftDelete方式いずれでも変わらないため、当初面談①時点では削除連動の内部実装が保留だったが、面談②でのSoftDelete確定によりこの保留は解消済み。
  - 事実: app/Models/Book.php:14 use HasFactory, SoftDeletes;
  - 事実: database/migrations/2026_09_01_115251_create_books_table.php:23 $table->softDeletes();
  - 事実: app/Http/Controllers/Api/V1/BookController.php:99 $book->delete();
  - 事実: 同上 S13!D25 L2（reviews / book_genre / favorites の book_id cascadeOnDelete 行）
  - 事実: tests/Feature/Api/BookApiTest.php:148 $this->assertSoftDeleted('books', ['id' => $book->id]); 実行結果は 実行結果.md R91 参照
- L11: 【ステータスコード早見表（AP01〜AP05）】
  - 事実: N/A-記述（見出し）
- L12: 200: 取得・更新成功（AP01, AP02, AP04）
  - 事実: 実行結果.md R240 HTTP 200（AP01）
  - 事実: 実行結果.md R241 HTTP 200（AP02）
  - 事実: app/Http/Controllers/Api/V1/BookController.php:78 public function update(UpdateApiBookRequest $request, Book $book): BookResource
  - 事実: tests/Feature/Api/BookApiTest.php:134 ->assertOk() 実行結果は 実行結果.md R91 参照
- L13: 201: 新規作成成功（AP03）
  - 事実: app/Http/Controllers/Api/V1/BookController.php:72 ->setStatusCode(Response::HTTP_CREATED);
- L14: 204: 削除成功・本文なし（AP05）
  - 事実: app/Http/Controllers/Api/V1/BookController.php:101 return response()->noContent();
- L15: 404: 対象が存在しない（AP02, AP04, AP05）
  - 事実: app/Exceptions/Handler.php:35-37 $this->renderable(function (NotFoundHttpException $e, Request $request) { if ($request->is('api/*')) { return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
  - 事実: 実行結果.md R242 GET /api/v1/books/99999 → HTTP 404（AP02）
  - 事実: 実行結果.md R245 PUT /api/v1/books/99999・DELETE /api/v1/books/99999（トークンなし）→ HTTP 401
- L16: 422: バリデーションエラー（AP01, AP03, AP04）
  - 事実: app/Http/Requests/Api/V1/ApiFormRequest.php:24-27 throw new HttpResponseException(response()->json([ 'message' => '入力内容に誤りがあります。', 'errors' => $validator->errors(), ], 422));
  - 事実: 実行結果.md R243 （AP01）per_page=0 等 → HTTP 422
  - 事実: app/Http/Requests/Api/V1/StoreApiBookRequest.php:5 class StoreApiBookRequest extends ApiFormRequest
  - 事実: app/Http/Requests/Api/V1/UpdateApiBookRequest.php:7 class UpdateApiBookRequest extends ApiFormRequest

### S13!C27
- L1: ★ AP06: Sanctum APIトークン認証（応用）
  - 事実: 実行結果.md R10 laravel/sanctum         3.3.3   Laravel Sanctum provides a featherweight au...
  - 事実: routes/api.php:23 Route::middleware('auth:sanctum')->group(function () {

### S13!D28
- L1: 公開API の書き込み系エンドポイント（POST/PUT/DELETE）に Laravel Sanctum を導入し、認証＋認可を行う。
  - 事実: routes/api.php:22-27 // 書き込み系（Sanctumトークン認証必須） Route::middleware('auth:sanctum')->group(function () { Route::post(…); Route::put(…); Route::delete(…); });
  - 事実: 実行結果.md R12 POST            api/v1/books ................... Api\V1\BookController@store ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: 実行結果.md R12 PUT             api/v1/books/{book} ........... Api\V1\BookController@update ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: 実行結果.md R12 DELETE          api/v1/books/{book} .......... Api\V1\BookController@destroy ／ ⇂ api ／ ⇂ App\Http\Middleware\Authenticate:sanctum
  - 事実: app/Http/Controllers/Api/V1/BookController.php:80 $this->authorize('update', $book);
  - 事実: app/Http/Controllers/Api/V1/BookController.php:97 $this->authorize('delete', $book);
  - 事実: app/Policies/BookPolicy.php:15 / :23 return $user->id === $book->user_id && ! $book->trashed();
- L2: Bearer トークン（Authorization ヘッダ）による認証方式を採用すること。
  - 事実: app/Models/User.php:15 use HasApiTokens, HasFactory, Notifiable;
  - 事実: app/Http/Kernel.php:42 // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
  - 事実: config/sanctum.php:36 'guard' => ['web'],
  - 事実: public/.htaccess:9-10 RewriteCond %{HTTP:Authorization} . ／ RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
  - 事実: 実行結果.md R245 # POST /api/v1/books（Authorization: Bearer invalid-token）→ HTTP 401 ／ PUT /api/v1/books/3 → HTTP 401 ／ DELETE /api/v1/books/3 → HTTP 401
  - 事実: 該当なし grep -rn "createToken" app/ routes/ → 出力0件（exit=1）
  - 事実: tests/Feature/Api/BookApiTest.php:114 Sanctum::actingAs($user);
- L3: 未認証時・認可エラー時のHTTPステータスコードを適切に設定すること。
  - 事実: app/Exceptions/Handler.php:41-44 $this->renderable(function (AuthenticationException $e, Request $request) { if ($request->is('api/*')) { return response()->json(['message' => '認証が必要です。'], 401);
  - 事実: app/Exceptions/Handler.php:47-50 $this->renderable(function (AccessDeniedHttpException $e, Request $request) { if ($request->is('api/*')) { return response()->json(['message' => 'この操作を実行する権限がありません。'], 403);
  - 事実: 実行結果.md R245 POST / PUT / DELETE（トークンなし）→ いずれも HTTP 401 ／ デコード {'message': '認証が必要です。'}
  - 事実: tests/Feature/Api/BookApiTest.php:184-212 test_update_other_users_book_returns_403 / test_destroy_other_users_book_returns_403 ->assertStatus(403) 実行結果は 実行結果.md R91 参照
