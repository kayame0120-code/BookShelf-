# 機能仕様書 — books（書籍CRUD／論理削除・復元／検索・ソート／ISBN検索）

| 項目 | 内容 |
|---|---|
| 対象発注書 | 02_書籍CRUD ／ 07_検索ソート ／ 08_ISBN検索（一部が 01_土台認証・06_基本テスト・14_応用テストに波及） |
| 正本 | 要件シート.xlsx シート5/7/8/9/10/11/12 ＋ Bladeモック `basic` / `advanced`（frozen） |
| 適用範囲 | 基本要件（書籍CRUD・論理削除・復元）と ★応用要件（検索/フィルタ/ソート・ISBN検索・ISBN/出版日のnullable化） |
| 完成条件 | 本書だけで発注書02・07・08が書ける（他ファイル参照不要） |

---

## 0. スコープ

**含む**: 書籍の一覧・詳細・登録・編集・削除（論理削除）・復元、ジャンル紐付けの sync、BookPolicy、books / book_genre テーブル、BookSeeder、キーワード検索・ジャンルフィルタ・ソート、Google Books API による ISBN 検索、ISBN と出版日の nullable 化、以上すべてのテスト観点。

**含まない**: ジャンルのCRUD（genres.md）／レビュー・いいね（reviews_likes.md）／お気に入りトグル（favorites.md）／ランキング（ranking.md）／認証と全テーブルのmigration統括（auth.md）／公開API（public_api.md）／読書計画（reading_plans.md）／通知（notifications.md）／マイ読書レポート（reports.md）。

---

## 1. ルーティング

`routes/web.php`。カッコ内は Blade が `route()` で参照している名前で、**変更不可**。

| # | メソッド | URI | route名 | Controller@Action | 認証 | 認可 | 段階 |
|---|---|---|---|---|---|---|---|
| 1 | GET | `/` | （無名。books.index と同一アクション） | `BookController@index` | 不要（公開） | — | 基本 |
| 2 | GET | `/books` | `books.index` | `BookController@index` | 不要（公開） | — | 基本 |
| 3 | GET | `/books/create` | `books.create` | `BookController@create` | 必須 | — | 基本 |
| 4 | POST | `/books` | `books.store` | `BookController@store` | 必須 | — | 基本 |
| 5 | GET | `/books/isbn/{isbn}` | `books.isbn` | `BookController@searchByIsbn` | 必須 | — | ★応用 |
| 6 | GET | `/books/{book}` | `books.show` | `BookController@show` | 不要（公開） | — | 基本 |
| 7 | GET | `/books/{book}/edit` | `books.edit` | `BookController@edit` | 必須 | `BookPolicy::update` | 基本 |
| 8 | PUT | `/books/{book}` | `books.update` | `BookController@update` | 必須 | `BookPolicy::update` | 基本 |
| 9 | DELETE | `/books/{book}` | `books.destroy` | `BookController@destroy` | 必須 | `BookPolicy::delete` | 基本 |
| 10 | PATCH | `/books/{book}/restore` | `books.restore` | `BookController@restore` | 必須 | `BookPolicy::restore` | 基本 |

### 定義コード（確定）

`Route::resource()` は使わず、公開ルートと認証必須ルートが混在するため個別に定義する。**定義順は下記のとおりに固定すること**（`/books/create` と `/books/isbn/{isbn}` を `/books/{book}` より先に書かないと、`create` / `isbn` が `{book}` として解決され 404 になる）。

```php
// 公開
Route::get('/', [BookController::class, 'index']);                 // 名前は付けない
Route::get('/books', [BookController::class, 'index'])->name('books.index');

// 認証必須（/books/{book} より前に置く）
Route::middleware('auth')->group(function () {
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/isbn/{isbn}', [BookController::class, 'searchByIsbn'])->name('books.isbn'); // ★応用
});

// 公開
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show')->withTrashed();

// 認証必須
Route::middleware('auth')->group(function () {
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    Route::patch('/books/{book}/restore', [BookController::class, 'restore'])
        ->name('books.restore')->withTrashed();
});
```

- `books.index` と `books.show` には `auth` を付けない（公開ページ）。それ以外の books 系はすべて `auth` を付ける。
- `withTrashed()` を付けるのは **show と restore の2本だけ**。edit / update / destroy に付けないことで、削除済み書籍への直リクエストは暗黙のルートモデル紐付けが 404 を返し、到達不能になる（要件シート シート7「削除済みの書籍では編集ボタン自体が非表示のため到達不能」の実装手段）。
- `->withTrashed()` のルート指定は Laravel 9.35 以降の機能。技術スタックは Laravel 10.x なので使用できる。
- `books.isbn` は `web` ミドルウェアグループ内に置く。Blade側のJSがセッションCookie付きで `fetch()` するため、`api.php` へは置かない。未認証時は `auth` ミドルウェアが `/login` へリダイレクトする。

---

## 2. コントローラー仕様（`App\Http\Controllers\BookController`）

すべて「リクエスト受付とレスポンス返却」に専念し、クエリは Eloquent のみで書く（生SQL・クエリビルダ禁止）。

| アクション | 処理 |
|---|---|
| `index` | §2-1 の検索・ソート仕様に従い `$books`（Paginator・10件）と `$genres`（全件）を `books.index` ビューへ渡す |
| `create` | `Genre::orderBy('id')->get()` を `$genres` として `books.create` ビューへ渡す |
| `store` | `StoreBookRequest` で検証 → `Book::create(validated + user_id: Auth::id())` → `$book->genres()->sync($request->genres)` → `redirect()->route('books.show', $book)->with('success', '書籍を登録しました')` |
| `show` | `$book->load(['genres', 'reviews' => fn ($q) => $q->latest(), 'reviews.user', 'reviews.likedByUsers'])` を `$book` として `books.show` ビューへ渡す |
| `edit` | `$this->authorize('update', $book)` → `$book` と `$genres`（全件）を `books.edit` ビューへ渡す |
| `update` | `$this->authorize('update', $book)` → `UpdateBookRequest` で検証 → `$book->update(validated)` → `$book->genres()->sync($request->genres)` → `redirect()->route('books.show', $book)->with('success', '書籍を更新しました')` |
| `destroy` | `$this->authorize('delete', $book)` → `$book->delete()`（SoftDeletes により `deleted_at` がセットされる） → `redirect()->route('books.index')->with('success', '書籍を削除しました')` |
| `restore` | `$this->authorize('restore', $book)` → `$book->restore()` → `redirect()->route('books.show', $book)->with('success', '書籍を復元しました')` |
| `searchByIsbn` ★ | §2-2 の仕様に従い JSON を返す（ビューは返さない） |

- `store` / `update` は `DB::transaction()` で書籍保存とジャンル sync を1単位にする。
- `index` の `with('genres')` と `withAvg` は N+1 回避のため必須（シート3 Eloquent 要件）。

### 2-1. ★ index の検索・フィルタ・ソート仕様

`books/index.blade.php`（advanced）が `request('keyword')` / `request('genre')` / `request('sort')` を直接読んで入力欄の再表示を行うため、コントローラー側で値を加工して渡し直さない。

```php
$books = Book::query()
    ->with('genres')
    ->withAvg('reviews', 'rating')
    ->when($request->filled('keyword'), fn ($q) => $q->where(
        fn ($sub) => $sub->where('title', 'like', '%'.$request->keyword.'%')
                         ->orWhere('author', 'like', '%'.$request->keyword.'%')
    ))
    ->when($request->filled('genre'), fn ($q) => $q->whereHas(
        'genres', fn ($sub) => $sub->where('genres.id', $request->genre)
    ))
    ->paginate(10)
    ->withQueryString();
```

**並び替えの適用（`paginate()` の前に置く）**

| `sort` の値 | 並び順 | 実装 |
|---|---|---|
| `newest`（既定・未指定時・未定義値のフォールバック先） | 登録日の新しい順 | `latest()` |
| `oldest` | 登録日の古い順 | `oldest()` |
| `title` | タイトル昇順 | `orderBy('title')` |
| `rating` | 平均評価の高い順。レビューが1件もない書籍は末尾 | `orderByDesc('reviews_avg_rating')` |

- `match` 式で分岐し、`default` を `newest` と同じ処理にする。未定義値でエラーにしない（要件シート シート7 確定）。
- `rating` の並びで `reviews_avg_rating` が null の行は MySQL の `ORDER BY ... DESC` により最後に並ぶ。追加の `whereNotNull` は入れない（レビュー0件の書籍も一覧から消してはならない）。
- キーワードは `title` **または** `author` の部分一致。2条件を必ず1つのクロージャで囲む（囲まないとジャンル絞り込みと OR で結合され、絞り込みが効かなくなる）。
- ページ送りで条件を維持するため `->withQueryString()` を必ず付ける。Blade側は `$books->links()` を呼ぶだけで、クエリ付与はここで行う。
- ジャンルフィルタのプルダウン用に `$genres = Genre::orderBy('id')->get()` を同時に渡す。Blade は `@foreach($genres ?? [] as $genre)` でガードしているが、渡さないと選択肢が空になる。
- 検索結果件数「検索結果: N件」は Blade が `$books->total()` を使って自前で描画する。コントローラー側で件数を別途渡さない。

### 2-2. ★ searchByIsbn の仕様（Google Books API 連携）

`books/create.blade.php` と `books/edit.blade.php`（advanced）に埋め込まれた JavaScript が `fetch('/books/isbn/' + isbn)` を呼び、返った JSON を各入力欄へ代入する。JS は `data.error` の有無だけで分岐するため、**成功時に `error` キーを含めてはならない**。

**成功レスポンス（200）**

```json
{
  "title": "リーダブルコード",
  "author": "Dustin Boswell",
  "description": "より良いコードを書くための...",
  "image_url": "https://books.google.com/books/content?id=...",
  "published_date": "2012-06-23"
}
```

| キー | Google Books API の取得元 | 値がないとき |
|---|---|---|
| `title` | `items[0].volumeInfo.title` | `null` |
| `author` | `items[0].volumeInfo.authors[0]`（配列の先頭1件のみ） | `null` |
| `description` | `items[0].volumeInfo.description` | `null` |
| `image_url` | `items[0].volumeInfo.imageLinks.thumbnail` | `null` |
| `published_date` | `items[0].volumeInfo.publishedDate` | `null` |

- JS 側は `data.title || ''` の形で受けるため、値がないキーは `null` で返してよい（キー自体を落としてもよいが、`null` で揃える）。
- `published_date` は Google Books が `2012` や `2012-06` の形式を返すことがある。JS 側が `new Date()` で解釈し `isNaN` なら入力欄へ代入しない作りになっているため、**サーバー側で日付形式を補正しない**。取得した文字列をそのまま返す。
- `authors` は配列で返るが、フォームの著者欄は単一項目のため先頭1件のみを採用する。

**エラーレスポンス**

| 条件 | ステータス | ボディ |
|---|---|---|
| `{isbn}` が13桁の数字でない | 422 | `{"error": "ISBNは13桁の数字で入力してください"}` |
| Google Books API が該当書籍なし（`totalItems` が 0、または `items` キーなし） | 404 | `{"error": "該当する書籍が見つかりませんでした"}` |
| Google Books API への通信失敗・APIが5xxを返す | 502 | `{"error": "書籍情報の取得に失敗しました"}` |

**実装上の確定事項**

- 通信は `Illuminate\Support\Facades\Http` を使う（CLAUDE.md 応用フェーズ追加ルール）。
- リクエスト先は `https://www.googleapis.com/books/v1/volumes?q=isbn:{isbn}`。APIキーは不要のため `.env` への追加も不要。
- 13桁チェックはコントローラー冒頭で行う。FormRequest には分離しない（ルートパラメータ1個のみの検証であり、`$errors` を返す画面が存在しないため）。
- 通信失敗の検知には `Http::get(...)` の戻り値に対する `->failed()` と、`ConnectionException` の捕捉の両方を用いる。
- 本アクションは書籍レコードを一切作成・更新しない。取得した値を返すだけで、保存は利用者がフォームを送信した時点の `store` / `update` が行う。

---

## 3. 画面契約（Bladeモック実測・改変禁止部分）

| 画面ID | Bladeファイル | 渡す変数 | Blade が要求する属性・リレーション | flashスロット |
|---|---|---|---|---|
| PG01 | `books/index.blade.php` | `$books`（Paginator・10件）／★`$genres` | `image_url` / `title` / `author` / `genres[].name` / `reviews_avg_rating` / `->links()` ／★`->total()` ／★`request('keyword')` `request('genre')` `request('sort')` | `session('success')` あり ／★`session('error')` あり |
| PG02 | `books/show.blade.php` | `$book` | `image_url` / `title` / `author` / `isbn` / `published_date` / `description` / `genres[].name` / `reviews[]`（`user.name` `rating` `comment` `created_at` `likedByUsers`） ／ `Auth::user()->favoriteBooks` ／ `Auth::user()->likedReviews` | `session('success')` あり |
| PG03 | `books/create.blade.php` + `books/_form.blade.php` | `$genres` | `$genres->isEmpty()` / `$genre->id` / `$genre->name` | なし（`$errors` と `old()` のみ） |
| PG04 | `books/edit.blade.php` + `books/_form.blade.php` | `$book`, `$genres` | 上記＋ `$book->genres->pluck('id')`（`_form` 内で `$bookGenreIds` を自前生成） | なし |

**フォーム項目（`_form.blade.php` 実測）**: `title` / `author` / `isbn` / `published_date`(type=date) / `description`(textarea) / `image_url` / `genres[]`(checkbox・複数)。
基本段階（basic）で必須マーク `*` が付いているのは title / author / ISBN-13 / 出版日 / ジャンル。ISBN欄の注記は「13桁のISBNコードを入力してください」、placeholder は `9784000000000`。

**重要**: flash を描画できるのは `books.index` / `books.show` / `genres.index` の3画面のみ。書籍系のリダイレクト先はこの制約を満たしている。

### 3-1. ★ advanced ブランチでの変更点（移入時の確認対象）

| ファイル | 変更内容 | 実装への影響 |
|---|---|---|
| `books/index.blade.php` | 検索フォーム（`keyword` テキスト・`genre` セレクト・`sort` セレクト）、検索ボタン、リセットリンク、検索結果件数表示、`session('error')` スロットを追加 | `index` が `$genres` を追加で渡す必要がある（§2-1） |
| `books/create.blade.php` | ISBN検索フォーム（`#isbn-search` `#fetch-btn`）と自動入力JSを `@push('scripts')` で追加 | `books.isbn` ルートが必要（§2-2）。`components/app-layout.blade.php` に `@stack('scripts')` があるため追加設定は不要 |
| `books/edit.blade.php` | 同上 | 同上。編集画面でもISBN検索が使える |
| `books/_form.blade.php` | ISBN・出版日の必須マーク `*` を削除。出版日の初期値を `isset($book->published_date) ? $book->published_date->format('Y-m-d') : ''` に変更 | `Book` モデルの `$casts` に `published_date => 'date'` が必須（§9）。キャストがないと `format()` 呼び出しで落ちる |
| `books/show.blade.php` | ISBN・出版日が未設定のとき「未登録」と表示（`{{ $book->isbn ?? '未登録' }}` / `{{ $book->published_date?->format('Y-m-d') ?? '未登録' }}`） | 同上 |
| `favorites/index.blade.php` | ISBNが未設定のとき「未登録」と表示 | favorites.md の実装に変更は生じない（表示のみの変更） |
| `layouts/navigation.blade.php` | マイレポート・読書計画のナビリンク、通知ベルアイコンと未読件数バッジを追加 | reports.md / reading_plans.md / notifications.md の担当範囲 |

`sort` セレクトの選択肢は `newest`（新しい順）／`oldest`（古い順）／`rating`（評価順）／`title`（タイトル順）の4値。**この4値がソート仕様の正本**であり、他の値を実装しない。

---

## 4. バリデーション（要件シート シート8・確定版）

FormRequest に必ず分離する。`messages()` に下表の文言をそのまま実装する（汎用テンプレート禁止）。

### `App\Http\Requests\StoreBookRequest`（books.store）— 基本段階

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

`authorize()` は `true`（ログイン必須は `auth` ミドルウェアが担保。所有者概念なし）。

### `App\Http\Requests\UpdateBookRequest`（books.update）— 基本段階

`isbn` 以外は StoreBookRequest と同一ルール・同一文言。差分は1点のみ。

| フィールド | ルール | メッセージ |
|---|---|---|
| `isbn` | `required, string, regex:/^[0-9]{13}$/, unique:books,isbn,{book},id`（自身のレコードを除外） | ISBNは13桁の数字で入力してください／このISBNは既に登録されています |

`Rule::unique('books', 'isbn')->ignore($this->route('book'))` で実装する。

### ★ 応用段階での変更（Store / Update 両方に適用）

要件シート シート8 の★行で確定。`isbn` と `published_date` を任意入力に変える。

| フィールド | 応用段階のルール | メッセージ |
|---|---|---|
| `isbn` | `nullable, string, regex:/^[0-9]{13}$/, unique:books,isbn`（update は `->ignore($this->route('book'))` を維持） | ISBNは13桁の数字で入力してください／このISBNは既に登録されています |
| `published_date` | `nullable, date` | 出版日は正しい日付形式で入力してください |

- `required` の削除に伴い、「ISBNを入力してください」「出版日を入力してください」のメッセージは `messages()` から削除する。残しておいても発火しないが、実装と文言定義の不一致になるため消す。
- `nullable` と個別ルールを併用する。値が入っている場合のみ 13桁チェックと一意性チェックが働く。
- update での自身のレコード除外方式は基本段階から変更しない。
- 他の6項目（title / author / description / image_url / genres / genres.*）は基本段階のまま変更しない。
- migration 側も `isbn` と `published_date` を nullable に変更する（§8）。

### ISBN一意性と論理削除の関係（確定・変更禁止）

`unique:books,isbn` は生のDBクエリであり、`deleted_at` が入った行も一意性チェックの対象に含まれる。**削除済み書籍のISBNは「使用中」のまま**とする。根拠は復元機能の存在で、「①本Aを削除 → ②同ISBNで本Bを登録 → ③本Aを復元」の順で重複が発生するのを構造的に防ぐため。`withTrashed()` 相当の除外処理を入れてはならない。

nullable 化後も、ISBN が NULL の行同士は MySQL の UNIQUE 制約で重複扱いにならないため、ISBN 未入力の書籍は何件でも登録できる。

---

## 5. 認可（`App\Policies\BookPolicy`）

コントローラーで `$this->authorize()` を呼び、Blade は `@can` で分岐する（シート3 Policy要件）。

| メソッド | 判定 |
|---|---|
| `update(User $user, Book $book)` | `$user->id === $book->user_id && ! $book->trashed()` |
| `delete(User $user, Book $book)` | `$user->id === $book->user_id && ! $book->trashed()` |
| `restore(User $user, Book $book)` | `$user->id === $book->user_id && $book->trashed()` |

`viewAny` / `view` / `create` は定義しない（一覧・詳細は公開、登録は所有者概念なしでログイン必須のみ）。

`! $book->trashed()` を条件に含めることで、`books/show.blade.php` に既にある `@can('update', $book)` / `@can('delete', $book)` がそのまま「削除済みなら編集・削除ボタンを非表示」を満たす。**この2箇所のBladeは改変不要**。

★応用段階でも BookPolicy の判定内容は変更しない。公開APIの Sanctum 認可（public_api.md AP06）は同じ `update` / `delete` をそのまま流用する。

---

## 6. 画面遷移・フラッシュ文言（要件シート シート7・確定版）

| 操作 | 成功時の遷移先 | フラッシュ文言 | 失敗時 | 認可失敗時 |
|---|---|---|---|---|
| 一覧を表示 | 当該画面（公開） | — | — | — |
| ★ キーワード検索 | 当該画面（絞り込み結果を表示） | — | 該当0件でも一覧画面を表示し「書籍が見つかりませんでした。」を表示。エラー扱いにしない | —（公開ページのため認可の概念なし） |
| ★ ジャンルフィルタ | 当該画面（選択ジャンルの書籍のみ表示） | — | 該当0件でも一覧画面を表示 | — |
| ★ ソート順の変更 | 当該画面（指定順で表示） | — | 未定義値は `newest` 扱いにフォールバック | — |
| ★ ページネーションリンク | 当該画面（条件を維持して指定ページ） | — | 存在しないページ番号は空の一覧を表示。404にしない | — |
| 「書籍を登録」ボタン | `books.create` | — | — | 未認証: `/login` へリダイレクト |
| 登録フォーム送信 | `books.show($book)` | `書籍を登録しました` | `back()` + `$errors`（自動） | 未認証: `/login` へ |
| ★ 「ISBN検索」ボタン | 画面遷移なし（JSONのみ） | —（API） | 422 / 404 / 502 の各JSON（§2-2） | 未認証: `/login` へリダイレクト |
| 詳細を表示 | 当該画面（公開） | — | — | — |
| 「編集」ボタン | `books.edit` | — | — | 403 |
| 更新フォーム送信 | `books.show($book)` | `書籍を更新しました` | `back()` + `$errors`（自動） | 403 |
| 「削除」ボタン | `books.index` | `書籍を削除しました` | 業務エラーなし（論理削除のため関連レコードは保持） | 403 |
| 「復元する」ボタン | `books.show($book)` | `書籍を復元しました` | 業務エラーなし | 403（ボタンは本人にしか出ないため、直URL叩きのみ到達） |

---

## 7. 削除済み書籍の挙動（面談②確定・本書の中核）

書籍は物理削除せず `deleted_at` による論理削除にする。「通常」と「削除済み」の2状態を削除・復元で往復し、どちらの状態でも reviews / favorites / book_genre のレコードは失われない。

### 状態と表示範囲

| 場所 | 削除済み書籍の扱い |
|---|---|
| 書籍一覧（PG01）・★検索結果・★ジャンルフィルタ結果 | 表示されない（SoftDeletes の標準除外） |
| ジャンル詳細の書籍一覧（PG06） | 表示されない |
| お気に入り一覧（PG10） | 表示されない |
| ランキング（PG11） | 集計・表示ともに対象外 |
| 公開API 全5本 | 対象外（AP02/AP04/AP05 は 404） |
| ★ 読書計画作成の書籍プルダウン | 表示されない |
| ★ マイ読書レポート（PG14） | **集計・表示ともに対象に含める**（reports.md。ランキングと意図的に異なる） |
| 書籍詳細（PG02） | **表示する**（`withTrashed()`。404にしない） |

### 書籍詳細（PG02）が削除済みのときの要素別挙動

| 要素 | 挙動 | 実現方法 |
|---|---|---|
| バナー | ページ上部に「この本は削除されました」を表示 | Blade追記（§10-1） |
| レビュー・評価一覧 | そのまま表示（本人以外の分も全部読める） | 変更なし |
| 編集ボタン | 非表示 | `BookPolicy::update` が false（Blade改変不要） |
| 削除ボタン | 非表示 | `BookPolicy::delete` が false（Blade改変不要） |
| 復元ボタン | 登録者本人にのみ表示。押すと通常状態に戻る | Blade追記（§10-1） |
| お気に入りボタン | 非表示 | Blade追記（§10-1） |
| 新規レビュー投稿フォーム | 非表示。「削除済みの書籍にはレビューを投稿できません」の案内文に差し替え | Blade追記（§10-1） |
| レビューの「いいね」 | 操作可能（変更なし） | 変更なし |
| レビュー本人の編集・削除 | 操作可能（変更なし） | 変更なし |

分岐の原則は「**本が生きている前提の操作**（編集・削除・お気に入り・新規レビュー投稿）は削除済みなら非表示、**本の状態と無関係な操作**（いいね・レビュー本人の編集削除）はそのまま残す」。

### 設計根拠（発注書には転記不要・レビュー時の参照用）

削除連動を「本に紐づくデータか」ではなく「**そのデータの作成主体が誰か**」で分岐させた。書籍の登録者とレビューの投稿者は別人格になり得るため、登録者の削除操作で他人のレビューが消えてはならない。Restrict案（レビューが付いた本を削除不可にする）と Nullify案（`reviews.book_id` を nullable 化して SET NULL）の2案を退けたうえで SoftDelete を採用した。Nullify は、基本13画面に**レビュー単独表示画面が存在しない**ため、本が消えると詳細画面ごと消えてレビューの表示先が無くなる、という理由で撤回している。

---

## 8. テーブル仕様（要件シート シート12）

### books

| カラム | 型 | PK | NOT NULL | FK | 補足 |
|---|---|---|---|---|---|
| id | bigint unsigned | ○ | ○ | | `$table->id()` |
| title | varchar(255) | | ○ | | |
| author | varchar(255) | | ○ | | |
| isbn | varchar(13) | | 基本: ○ ／ ★応用: — | | UNIQUE。`_form` の placeholder「9784000000000」・注記「13桁」に基づく。★応用段階で nullable に変更 |
| published_date | date | | 基本: ○ ／ ★応用: — | | ★応用段階で nullable に変更 |
| description | text | | | | NULL許可 |
| image_url | varchar(255) | | | | NULL許可。URL形式チェックは FormRequest 側 |
| user_id | bigint unsigned | | ○ | users.id | `restrictOnDelete()` |
| deleted_at | timestamp | | | | NULL許可。`$table->softDeletes()` |
| created_at / updated_at | timestamp | | | | `$table->timestamps()` |

### ★ nullable 化のマイグレーション

既存の `create_books_table` を書き換えず、新規のマイグレーションファイルを追加して列定義を変更する（走行①〜⑥で確定済みのマイグレーションは改変しない）。

```php
public function up(): void
{
    Schema::table('books', function (Blueprint $table) {
        $table->string('isbn', 13)->nullable()->change();
        $table->date('published_date')->nullable()->change();
    });
}
```

- `->change()` を使うため `doctrine/dbal` が必要かどうかは Laravel のバージョンに依存する。10.x 系では必要になるため、`sail composer require doctrine/dbal` を実行してから作成する。
- `isbn` の UNIQUE 制約は `->change()` では落ちない。制約を張り直す記述を書かないこと（書くと重複エラーになる）。
- `down()` では `nullable(false)` に戻す記述を書く。ただし NULL 値が入った行があるとロールバックに失敗するため、実運用でのロールバックは想定しない。

### book_genre（中間・複合主キー）

| カラム | 型 | PK | NOT NULL | FK | 補足 |
|---|---|---|---|---|---|
| book_id | bigint unsigned | ○（複合） | ○ | books.id | `cascadeOnDelete()`。論理削除下では発火しない設定だが害はないため残す |
| genre_id | bigint unsigned | ○（複合） | ○ | genres.id | `restrictOnDelete()` |

`$table->primary(['book_id', 'genre_id'])`。timestamps は持たせない。

---

## 9. モデル（`App\Models\Book`）

```php
class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'author', 'isbn', 'published_date', 'description', 'image_url', 'user_id'];
    protected $casts = ['published_date' => 'date'];

    public function user()              { return $this->belongsTo(User::class); }
    public function genres()            { return $this->belongsToMany(Genre::class, 'book_genre'); }
    public function reviews()           { return $this->hasMany(Review::class); }
    public function favoritedByUsers()  { return $this->belongsToMany(User::class, 'favorites'); }
    public function readingPlans()      { return $this->hasMany(ReadingPlan::class); } // ★応用
}
```

- `SoftDeletes` トレイトが `books.deleted_at` を扱う。これにより一覧・検索・ランキング・APIの除外が自動で効く。
- `published_date => 'date'` のキャストは**必須**。advanced の `_form.blade.php` と `show.blade.php` が `->format('Y-m-d')` と `?->format('Y-m-d')` を呼ぶため、キャストがないと文字列に対するメソッド呼び出しで落ちる。nullable 化後は `?->` により NULL でも落ちない。
- 中間テーブルは timestamps を持たないので `withTimestamps()` は付けない。
- 平均評価は列を持たず、`withAvg('reviews', 'rating')` の集計エイリアス `reviews_avg_rating` で供給する（Blade実測の属性名）。
- ★応用フェーズでは CLAUDE.md の追加ルールに従い、全リレーションメソッドに戻り値型（`BelongsTo` / `HasMany` / `BelongsToMany`）を宣言する。

---

## 10. 実装上の注意（CC向け）

### 10-1. `books/show.blade.php` への追記（Blade改変が必要な唯一の箇所）

frozen 宣言のあるモックだが、**削除済み状態の表示は元のモックに定義自体が存在しない**（404用Bladeも存在しない）。既存の記述を書き換えるのではなく、以下4点を追記する。既存のマークアップ・クラス名・レイアウトは変更しないこと。

1. `session('success')` ブロックの直後に、`@if($book->trashed())` で囲んだバナー「この本は削除されました」を追加する。
2. お気に入りボタンの `@auth` ブロック全体を `@if(! $book->trashed())` で囲む。
3. レビュー投稿フォームの `@auth` ブロック全体を `@if(! $book->trashed())` で囲み、`@else` 側に「削除済みの書籍にはレビューを投稿できません」の案内文（`<p class="mb-6 text-gray-600">`）を置く。
4. 編集・削除ボタンの `<div class="flex gap-2 mt-4">` 内に、`@can('restore', $book)` で囲んだ「復元する」ボタン（`PATCH` を `@method('PATCH')` で送る form）を追加する。

★ advanced ブランチの `books/show.blade.php` を移入した際は、上記4点の追記を**再度適用する**。advanced 版には ISBN・出版日の「未登録」表示は入っているが、削除済み状態の表示は入っていない。

### 10-2. ★ advanced ブランチ移入時の手順

- `resources/` を advanced ブランチの内容で置き換えた後、`sail artisan migrate:fresh --seed` でデータベースを再構築する（要件シート シート9 の★Seeder に切り替わるため）。
- 移入により §3-1 の7ファイルが差し替わる。`books/show.blade.php` は §10-1 の追記を再適用する。
- 移入後に `sail npm run dev` を再実行し、Tailwind が新しいクラス名を拾っていることを確認する。

### 10-3. その他

- `BookController@show` と `@restore` は `withTrashed()` 付きルートで解決する。付け忘れると削除済み書籍が 404 になり、要件が満たせない。
- `Review::book()` リレーションには `->withTrashed()` を付ける（reviews_likes.md 参照）。付けないと、削除済み書籍のレビューを編集・削除する際に `$review->book` が null になり `reviews/edit.blade.php` が落ちる。
- `image_url` は `nullable` だが、Blade は `@if($book->image_url)` でガードしているので空でも崩れない。
- `/` は `books.index` と同一アクションに束ねる。`welcome.blade.php` はどの `route()` からも参照されない未使用ファイルだが、CLAUDE.md 16章に従い削除せず残す（ルート定義の対象外とするだけ）。
- ★ `books.isbn` のルートは `/books/{book}` より**必ず前**に定義する。順序を誤ると `isbn` という文字列が書籍IDとして解決され、常に 404 になる。

---

## 11. シーディング（BookSeeder・要件シート シート9／採点直結・改変禁止）

books テーブルに11件。`firstOrCreate`（ISBN重複防止）と `genres()->sync()` を使う。

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

- 各書籍に `description` を設定する。
- `image_url` は `https://placehold.co/200x300/e2e8f0/475569?text={番号}`（`{番号}` は 1〜11）で固定。
- **登録者**: 基本段階は `User::first()`（山田太郎）。★応用段階は `$users->random()->id`（ランダムユーザー割当。マイ読書レポートで複数ユーザーの所有書籍を表示するため）に変更する。タイトル・著者・ISBN・出版日・説明・画像URL・ジャンル紐付けは基本段階と同一で変更しない。

---

## 12. テスト観点（要件シート シート10）

**全体要件（共通）**: 全テスト通過。`sail artisan test --coverage` で基本機能のみ60%超、★応用機能込みで80%以上を目標。

### 単体テスト `tests/Unit/BookTest.php`

| # | 検証観点 |
|---|---|
| U-B1 | `Book` の `belongsTo User` が正しく取得できる |
| U-B2 | `Book` の `belongsToMany Genre`（book_genre 経由）が正しく取得できる |
| U-B3 | `Book` の `hasMany Review` が正しく取得できる |
| U-B4 | `withAvg('reviews','rating')` による平均評価が算出できる。レビュー0件のとき `reviews_avg_rating` が null になる |
| U-B5 | SoftDeletes の標準除外 — `Book::find()` は削除済みを取得できず、`withTrashed()` でのみ取得できる |

### 機能テスト `tests/Feature/BookCrudTest.php`

| # | 検証観点 |
|---|---|
| F-B1 | 登録 — 全項目を正しく入力すると `books.show` へ遷移し「書籍を登録しました」が表示される |
| F-B2 | 登録バリデーション — title / author / isbn / published_date 未入力、isbn不正（13桁でない）、isbn重複、genre未選択の各ケースで `back()` + `$errors` によりエラーが表示される |
| F-B3 | ジャンル紐付け — 登録・編集時に選択したジャンルのみが `book_genre` に `sync()` される |
| F-B4 | 編集の認可 — 登録者本人は編集でき、それ以外は403になる |
| F-B5 | 削除の認可 — 登録者本人は削除でき、それ以外は403になる |
| F-B6 | 削除後の一覧除外 — 削除した書籍が一覧・ランキング・公開APIから除外される |
| F-B7 | 削除済み書籍詳細ページの表示 — 404にならず「この本は削除されました」バナーとともに表示される |
| F-B8 | 削除済み時のボタン非表示 — 編集・削除・お気に入りボタンと新規レビュー投稿フォームが非表示になる |
| F-B9 | 削除済み時のレビュー閲覧維持 — 本人以外の分も含め引き続き閲覧できる |
| F-B10 | 復元 — 登録者本人が「復元する」を押すと通常状態に戻り、一覧・ランキングに再表示される |
| F-B11 | 復元の認可 — 登録者本人以外が `PATCH /books/{book}/restore` に直接アクセスすると403になる |
| F-B12 | 削除済みISBNの一意性 — 削除済みの本と同じISBNで新規登録しようとすると一意性エラーになる |
| F-B13 | DBレベルの保持確認 — 書籍削除後も `reviews` レコードが物理削除されず `book_id` を保持したままである（`assertDatabaseHas`） |

### ★ 機能テスト `tests/Feature/BookAuthorizationTest.php`（シート10「書籍CRUD（認可の詳細テスト）」）

| # | 検証観点 |
|---|---|
| F-BA1 | 編集画面の認可 — 他ユーザーが `GET /books/{book}/edit` へ直接アクセスすると403になる |
| F-BA2 | 更新の認可 — 他ユーザーが `PUT /books/{book}` を直接送信すると403になる |
| F-BA3 | 削除の認可 — 他ユーザーが `DELETE /books/{book}` を直接送信すると403になる |
| F-BA4 | 復元の認可 — 他ユーザーが `PATCH /books/{book}/restore` を直接送信すると403になる |
| F-BA5 | 未認証時の挙動 — 未ログインで create・edit 画面および store・update・destroy・restore の各エンドポイントにアクセスすると `/login` へリダイレクトされる |
| F-BA6 | ISBNのnullable化 — ISBNを空のまま登録・更新が成功する |
| F-BA7 | 出版日のnullable化 — 出版日を空のまま登録・更新が成功する |
| F-BA8 | 空値時の表示 — ISBN・出版日が未入力の書籍について、書籍詳細画面とお気に入り一覧画面で「未登録」と表示される |
| F-BA9 | 値がある場合の検証維持 — ISBNに値を入れた場合は13桁チェックと一意性チェックが働き、13桁でない値・既存と重複する値でエラーになる |
| F-BA10 | 出版日の日付キャスト — 出版日を持つ書籍の詳細画面が `Y-m-d` 形式で表示される |

F-BA1〜F-BA4 は HTTPメソッドごとに個別のリクエストで検証し、いずれか1つの結果で他を代表させない（CLAUDE.md 12-4 規則3）。

### ★ 機能テスト `tests/Feature/BookSearchTest.php`（シート10「検索・フィルタ」）

| # | 検証観点 |
|---|---|
| F-S1 | キーワード部分一致・タイトル — `keyword` にタイトルの一部を渡すと該当書籍のみが返る |
| F-S2 | キーワード部分一致・著者 — `keyword` に著者名の一部を渡すと該当書籍のみが返る |
| F-S3 | キーワード該当0件 — 一覧画面自体は200で表示され「書籍が見つかりませんでした。」が表示される。バリデーションエラーにはしない |
| F-S4 | ジャンル絞り込み — `genre` にジャンルIDを渡すと当該ジャンルに紐づく書籍のみが返る |
| F-S5 | 条件の併用 — `keyword` と `genre` を同時に指定するとAND条件で絞り込まれる |
| F-S6 | 検索結果件数の表示 — `keyword` または `genre` を指定した場合のみ「検索結果: N件」が表示され、無指定時は表示されない |
| F-S7 | ページ送りでの条件維持 — 2ページ目へ遷移しても `keyword` / `genre` / `sort` が維持され、ページネーションリンクに各クエリが付与されている |
| F-S8 | 存在しないページ番号 — `page=999` でも404にならず空の一覧が表示される |
| F-S9 | 削除済み書籍の除外 — 論理削除済みの書籍が検索結果・ジャンル絞り込み結果に現れない |
| F-S10 | ゲストアクセス — 未ログインでも検索・絞り込みが実行できる |

### ★ 機能テスト `tests/Feature/BookSortTest.php`（シート10「ソート」）

| # | 検証観点 |
|---|---|
| F-O1 | 既定順 — `sort` 未指定のとき `newest` 扱いとなり、登録日の新しい順に並ぶ |
| F-O2 | `newest` — 登録日の新しい順に並ぶ |
| F-O3 | `oldest` — 登録日の古い順に並ぶ |
| F-O4 | `title` — タイトルの昇順に並ぶ |
| F-O5 | `rating` — レビュー平均評価の高い順に並び、レビューが1件もない書籍が末尾に配置される |
| F-O6 | 未定義値のフォールバック — `sort=abc` のような未定義値を渡してもエラーにならず `newest` 扱いで表示される |
| F-O7 | 選択状態の保持 — 並び替え後のセレクトで選択中の `sort` 値が selected になっている |
| F-O8 | ソート維持のページ送り — 並び替えた状態で2ページ目へ遷移しても `sort` が維持される |
| F-O9 | ソートと検索の併用 — `keyword` または `genre` で絞り込んだ結果に対して `sort` が適用される |

### ★ 機能テスト `tests/Feature/IsbnSearchTest.php`（シート10「ISBN検索」）

| # | 検証観点 |
|---|---|
| F-I1 | 正常系 — `Http::fake()` で Google Books API の成功応答をモックし、`GET /books/isbn/{isbn}` が200で `title` / `author` / `description` / `image_url` / `published_date` の各キーを持つJSONを返す |
| F-I2 | ISBN形式不正 — 13桁でない値を渡すと422で `{"error": "ISBNは13桁の数字で入力してください"}` が返る |
| F-I3 | 該当書籍なし — Google Books API が該当0件を返す応答をモックすると404で `{"error": "該当する書籍が見つかりませんでした"}` が返る |
| F-I4 | 通信エラー — Google Books API が接続失敗または5xxを返す応答をモックすると502で `{"error": "書籍情報の取得に失敗しました"}` が返る |
| F-I5 | エラー応答の形状統一 — 422・404・502のいずれも `error` キーを持つJSONであり、フロント側のJSが `data.error` で分岐できる |
| F-I6 | 認可 — 未ログインで `GET /books/isbn/{isbn}` にアクセスすると `/login` へリダイレクトされる |
| F-I7 | 外部APIへの実通信がないこと — 全テストが `Http::fake()` 配下で実行され、実際の Google Books API を呼び出していない |
