# 機能仕様書 — books（書籍CRUD／論理削除・復元）

| 項目 | 内容 |
|---|---|
| 対象発注書 | 02_書籍CRUD（一部が 01_土台認証・06_基本テストに波及） |
| 正本 | 要件シート.xlsx シート5/7/8/9/10/11/12 ＋ Bladeモック `basic`（frozen） |
| 適用範囲 | 基本要件のみ。★応用（検索/フィルタ/ソート・ISBN検索・nullable変更）は第5週に本書へ追記する |
| 完成条件 | 本書だけで発注書02が書ける（他ファイル参照不要） |
| 版 | v1（2026-09-01・面談②のSoftDelete確定を反映済み） |

---

## 0. スコープ

**含む**: 書籍の一覧・詳細・登録・編集・削除（論理削除）・復元、ジャンル紐付けの sync、BookPolicy、books / book_genre テーブル、BookSeeder、書籍CRUDのテスト観点。

**含まない**: ジャンルのCRUD（genres.md）／レビュー・いいね（reviews_likes.md）／お気に入りトグル（favorites.md）／ランキング（ranking.md）／認証と全テーブルのmigration統括（auth.md）／公開API（public_api.md）。

---

## 1. ルーティング

`routes/web.php`。カッコ内は Blade が `route()` で参照している名前で、**変更不可**。

| # | メソッド | URI | route名 | Controller@Action | 認証 | 認可 |
|---|---|---|---|---|---|---|
| 1 | GET | `/` | （無名。books.index と同一アクション） | `BookController@index` | 不要（公開） | — |
| 2 | GET | `/books` | `books.index` | `BookController@index` | 不要（公開） | — |
| 3 | GET | `/books/create` | `books.create` | `BookController@create` | 必須 | — |
| 4 | POST | `/books` | `books.store` | `BookController@store` | 必須 | — |
| 5 | GET | `/books/{book}` | `books.show` | `BookController@show` | 不要（公開） | — |
| 6 | GET | `/books/{book}/edit` | `books.edit` | `BookController@edit` | 必須 | `BookPolicy::update` |
| 7 | PUT | `/books/{book}` | `books.update` | `BookController@update` | 必須 | `BookPolicy::update` |
| 8 | DELETE | `/books/{book}` | `books.destroy` | `BookController@destroy` | 必須 | `BookPolicy::delete` |
| 9 | PATCH | `/books/{book}/restore` | `books.restore` | `BookController@restore` | 必須 | `BookPolicy::restore` |

### 定義コード（確定）

`Route::resource()` は使わず、公開ルートと認証必須ルートが混在するため個別に定義する。**定義順は下記のとおりに固定すること**（`/books/create` を `/books/{book}` より先に書かないと、`create` が `{book}` として解決され 404 になる）。

```php
// 公開
Route::get('/', [BookController::class, 'index']);                 // 名前は付けない
Route::get('/books', [BookController::class, 'index'])->name('books.index');

// 認証必須（/books/{book} より前に置く）
Route::middleware('auth')->group(function () {
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
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

---

## 2. コントローラー仕様（`App\Http\Controllers\BookController`）

すべて「リクエスト受付とレスポンス返却」に専念し、クエリは Eloquent のみで書く（生SQL・クエリビルダ禁止）。

| アクション | 処理 |
|---|---|
| `index` | `Book::with('genres')->withAvg('reviews', 'rating')->latest()->paginate(10)` を `$books` として `books.index` ビューへ渡す |
| `create` | `Genre::orderBy('id')->get()` を `$genres` として `books.create` ビューへ渡す |
| `store` | `StoreBookRequest` で検証 → `Book::create(validated + user_id: Auth::id())` → `$book->genres()->sync($request->genres)` → `redirect()->route('books.show', $book)->with('success', '書籍を登録しました')` |
| `show` | `$book->load(['genres', 'reviews' => fn ($q) => $q->latest(), 'reviews.user', 'reviews.likedByUsers'])` を `$book` として `books.show` ビューへ渡す |
| `edit` | `$this->authorize('update', $book)` → `$book` と `$genres`（全件）を `books.edit` ビューへ渡す |
| `update` | `$this->authorize('update', $book)` → `UpdateBookRequest` で検証 → `$book->update(validated)` → `$book->genres()->sync($request->genres)` → `redirect()->route('books.show', $book)->with('success', '書籍を更新しました')` |
| `destroy` | `$this->authorize('delete', $book)` → `$book->delete()`（SoftDeletes により `deleted_at` がセットされる） → `redirect()->route('books.index')->with('success', '書籍を削除しました')` |
| `restore` | `$this->authorize('restore', $book)` → `$book->restore()` → `redirect()->route('books.show', $book)->with('success', '書籍を復元しました')` |

- `store` / `update` は `DB::transaction()` で書籍保存とジャンル sync を1単位にする。
- `index` の `with('genres')` と `withAvg` は N+1 回避のため必須（シート3 Eloquent 要件）。

---

## 3. 画面契約（Bladeモック実測・改変禁止部分）

| 画面ID | Bladeファイル | 渡す変数 | Blade が要求する属性・リレーション | flashスロット |
|---|---|---|---|---|
| PG01 | `books/index.blade.php` | `$books`（Paginator・10件） | `image_url` / `title` / `author` / `genres[].name` / `reviews_avg_rating` / `->links()` | `session('success')` あり |
| PG02 | `books/show.blade.php` | `$book` | `image_url` / `title` / `author` / `isbn` / `published_date` / `description` / `genres[].name` / `reviews[]`（`user.name` `rating` `comment` `created_at` `likedByUsers`） ／ `Auth::user()->favoriteBooks` ／ `Auth::user()->likedReviews` | `session('success')` あり |
| PG03 | `books/create.blade.php` + `books/_form.blade.php` | `$genres` | `$genres->isEmpty()` / `$genre->id` / `$genre->name` | なし（`$errors` と `old()` のみ） |
| PG04 | `books/edit.blade.php` + `books/_form.blade.php` | `$book`, `$genres` | 上記＋ `$book->genres->pluck('id')`（`_form` 内で `$bookGenreIds` を自前生成） | なし |

**フォーム項目（`_form.blade.php` 実測）**: `title` / `author` / `isbn` / `published_date`(type=date) / `description`(textarea) / `image_url` / `genres[]`(checkbox・複数)。
必須マーク `*` が付いているのは title / author / ISBN-13 / 出版日 / ジャンル。ISBN欄の注記は「13桁のISBNコードを入力してください」、placeholder は `9784000000000`。

**重要**: flash を描画できるのは `books.index` / `books.show` / `genres.index` の3画面のみ。書籍系のリダイレクト先はこの制約を満たしている。

---

## 4. バリデーション（要件シート シート8・確定版）

FormRequest に必ず分離する。`messages()` に下表の文言をそのまま実装する（汎用テンプレート禁止）。

### `App\Http\Requests\StoreBookRequest`（books.store）

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

### `App\Http\Requests\UpdateBookRequest`（books.update）

`isbn` 以外は StoreBookRequest と同一ルール・同一文言。差分は1点のみ。

| フィールド | ルール | メッセージ |
|---|---|---|
| `isbn` | `required, string, regex:/^[0-9]{13}$/, unique:books,isbn,{book},id`（自身のレコードを除外） | ISBNは13桁の数字で入力してください／このISBNは既に登録されています |

`Rule::unique('books', 'isbn')->ignore($this->route('book'))` で実装する。

### ISBN一意性と論理削除の関係（確定・変更禁止）

`unique:books,isbn` は生のDBクエリであり、`deleted_at` が入った行も一意性チェックの対象に含まれる。**削除済み書籍のISBNは「使用中」のまま**とする。根拠は復元機能の存在で、「①本Aを削除 → ②同ISBNで本Bを登録 → ③本Aを復元」の順で重複が発生するのを構造的に防ぐため。`withTrashed()` 相当の除外処理を入れてはならない。

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

---

## 6. 画面遷移・フラッシュ文言（要件シート シート7・確定版）

| 操作 | 成功時の遷移先 | フラッシュ文言 | 失敗時 | 認可失敗時 |
|---|---|---|---|---|
| 一覧を表示 | 当該画面（公開） | — | — | — |
| 「書籍を登録」ボタン | `books.create` | — | — | 未認証: `/login` へリダイレクト |
| 登録フォーム送信 | `books.show($book)` | `書籍を登録しました` | `back()` + `$errors`（自動） | 未認証: `/login` へ |
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
| 書籍一覧（PG01） | 表示されない（SoftDeletes の標準除外） |
| ジャンル詳細の書籍一覧（PG06） | 表示されない |
| お気に入り一覧（PG10） | 表示されない |
| ランキング（PG11） | 集計・表示ともに対象外 |
| 公開API 全5本 | 対象外（AP02/AP04/AP05 は 404） |
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
| isbn | varchar(13) | | ○ | | UNIQUE。`_form` の placeholder「9784000000000」・注記「13桁」に基づく |
| published_date | date | | ○ | | |
| description | text | | | | NULL許可 |
| image_url | varchar(255) | | | | NULL許可。URL形式チェックは FormRequest 側 |
| user_id | bigint unsigned | | ○ | users.id | `restrictOnDelete()` |
| deleted_at | timestamp | | | | NULL許可。`$table->softDeletes()` |
| created_at / updated_at | timestamp | | | | `$table->timestamps()` |

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
}
```

- `SoftDeletes` トレイトが `books.deleted_at` を扱う。これにより一覧・検索・ランキング・APIの除外が自動で効く。
- 中間テーブルは timestamps を持たないので `withTimestamps()` は付けない。
- 平均評価は列を持たず、`withAvg('reviews', 'rating')` の集計エイリアス `reviews_avg_rating` で供給する（Blade実測の属性名）。

---

## 10. 実装上の注意（CC向け）

### 10-1. `books/show.blade.php` への追記（Blade改変が必要な唯一の箇所）

frozen 宣言のあるモックだが、**削除済み状態の表示は元のモックに定義自体が存在しない**（404用Bladeも存在しない）。既存の記述を書き換えるのではなく、以下4点を追記する。既存のマークアップ・クラス名・レイアウトは変更しないこと。

1. `session('success')` ブロックの直後に、`@if($book->trashed())` で囲んだバナー「この本は削除されました」を追加する。
2. お気に入りボタンの `@auth` ブロック全体を `@if(! $book->trashed())` で囲む。
3. レビュー投稿フォームの `@auth` ブロック全体を `@if(! $book->trashed())` で囲み、`@else` 側に「削除済みの書籍にはレビューを投稿できません」の案内文（`<p class="mb-6 text-gray-600">`）を置く。
4. 編集・削除ボタンの `<div class="flex gap-2 mt-4">` 内に、`@can('restore', $book)` で囲んだ「復元する」ボタン（`PATCH` を `@method('PATCH')` で送る form）を追加する。

### 10-2. その他

- `BookController@show` と `@restore` は `withTrashed()` 付きルートで解決する。付け忘れると削除済み書籍が 404 になり、要件が満たせない。
- `Review::book()` リレーションには `->withTrashed()` を付ける（reviews_likes.md 参照）。付けないと、削除済み書籍のレビューを編集・削除する際に `$review->book` が null になり `reviews/edit.blade.php` が落ちる。
- `image_url` は `nullable` だが、Blade は `@if($book->image_url)` でガードしているので空でも崩れない。
- `/` は `books.index` と同一アクションに束ねる。`welcome.blade.php` はどの `route()` からも参照されない未使用ファイルなので、ルート定義の対象外とする。

---

## 11. シーディング（BookSeeder・要件シート シート9／採点直結・改変禁止）

books テーブルに11件。登録者は `User::first()`（山田太郎）。`firstOrCreate`（ISBN重複防止）と `genres()->sync()` を使う。

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

---

## 12. テスト観点（要件シート シート10）

**全体要件（共通）**: 全テスト通過。`sail artisan test --coverage` で基本機能のみ60%超を目標。

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
