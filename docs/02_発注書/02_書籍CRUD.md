status: draft

# 発注書 02 — 書籍CRUD

**対象走行**: 走行②（第4週）
**出典**: `docs/01_機能仕様書/books.md`（この発注書はbooks.mdだけで書けている。他の機能仕様書は参照不要）
**対の検品表**: `docs/03_検品表/02_書籍CRUD.md`
**前提**: 走行①（土台＋認証）が合格・確定済み（`04_検品結果/01_土台認証_結果.md` 全90行YES）。books / book_genre テーブル、`Book` モデル、Fortify認証は完成済みとして扱う。

---

## 0. スコープ

### やること

1. `BookController`（index / create / store / show / edit / update / destroy / restore の8アクション）
2. `routes/web.php` への書籍系ルート追加（9本。`/` の無名ルート含む）
3. `StoreBookRequest` / `UpdateBookRequest`（FormRequest）
4. `BookPolicy`（update / delete / restore）
5. `books/show.blade.php` への4点追記（削除済みバナー・お気に入りボタンのガード・レビュー投稿フォームのガード・復元ボタン。§10-1参照。**この4点以外のBlade改変は禁止**）
6. `BookFactory`（テスト用。走行①で新規作成済みなら再利用）

### やらないこと（他の走行のスコープ。手を出すな）

| やらないこと | 担当走行 |
|---|---|
| ジャンルCRUD（GenreController） | 走行④ |
| レビュー投稿・いいね（ReviewController） | 走行③ |
| お気に入りトグル（FavoriteController） | 走行③ |
| ランキング（RankingController） | 走行④ |
| 公開API（`Api\V1\BookController`） | 走行⑤ |
| BookSeeder | 走行⑤ |
| テストコード（Unit/Feature） | 走行⑥ |
| ★検索/フィルタ/ソート・ISBN検索・nullable変更 | 走行⑦⑧（応用） |
| 型宣言・PHPDoc・Collection化の全体適用 | 走行⑬（応用） |

### この発注書の完成条件

`docs/03_検品表/02_書籍CRUD.md` の全行がYESになること。具体的には、書籍の一覧・詳細・登録・編集・削除（論理削除）・復元がBladeモックの契約どおりに動作し、削除済み書籍の詳細ページが404にならず「この本は削除されました」バナーとともに表示され、登録者本人にのみ復元操作が可能であること。

---

## 1. ルーティング（`routes/web.php` へ追加）

| # | メソッド | URI | route名 | Action | 認証 | 認可 |
|---|---|---|---|---|---|---|
| 1 | GET | `/` | （無名。`books.index` と同一アクション） | `BookController@index` | 不要 | — |
| 2 | GET | `/books` | `books.index` | `BookController@index` | 不要 | — |
| 3 | GET | `/books/create` | `books.create` | `BookController@create` | 必須 | — |
| 4 | POST | `/books` | `books.store` | `BookController@store` | 必須 | — |
| 5 | GET | `/books/{book}` | `books.show` | `BookController@show` | 不要 | — |
| 6 | GET | `/books/{book}/edit` | `books.edit` | `BookController@edit` | 必須 | `BookPolicy::update` |
| 7 | PUT | `/books/{book}` | `books.update` | `BookController@update` | 必須 | `BookPolicy::update` |
| 8 | DELETE | `/books/{book}` | `books.destroy` | `BookController@destroy` | 必須 | `BookPolicy::delete` |
| 9 | PATCH | `/books/{book}/restore` | `books.restore` | `BookController@restore` | 必須 | `BookPolicy::restore` |

**定義順を厳守すること**（`/books/create` を `/books/{book}` より前に書かないと `create` が `{book}` パラメータとして解決され404になる）。

```php
// 公開
Route::get('/', [BookController::class, 'index']);
Route::get('/books', [BookController::class, 'index'])->name('books.index');

// 認証必須（/books/{book} より前）
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

`withTrashed()` を付けるのは **show と restore の2本だけ**。edit / update / destroy に付けないことで、削除済み書籍への直リクエストは暗黙のルートモデル紐付けが404を返す（編集・削除ボタン非表示の裏付け）。

---

## 2. コントローラー仕様（`App\Http\Controllers\BookController`）

すべて「リクエスト受付とレスポンス返却」に専念する。クエリは Eloquent のみ（生SQL・クエリビルダ禁止）。

| アクション | 処理 |
|---|---|
| `index` | `Book::with('genres')->withAvg('reviews', 'rating')->latest()->paginate(10)` を `$books` として `books.index` へ |
| `create` | `Genre::orderBy('id')->get()` を `$genres` として `books.create` へ |
| `store` | `StoreBookRequest` で検証 → `DB::transaction()` 内で `Book::create(validated + user_id: Auth::id())` → `$book->genres()->sync($request->genres)` → `redirect()->route('books.show', $book)->with('success', '書籍を登録しました')` |
| `show` | `$book->load(['genres', 'reviews' => fn ($q) => $q->latest(), 'reviews.user', 'reviews.likedByUsers'])` を `$book` として `books.show` へ |
| `edit` | `$this->authorize('update', $book)` → `$book` と `$genres`（全件）を `books.edit` へ |
| `update` | `$this->authorize('update', $book)` → `UpdateBookRequest` で検証 → `DB::transaction()` 内で `$book->update(validated)` → `$book->genres()->sync($request->genres)` → `redirect()->route('books.show', $book)->with('success', '書籍を更新しました')` |
| `destroy` | `$this->authorize('delete', $book)` → `$book->delete()`（SoftDeletesにより`deleted_at`がセットされる） → `redirect()->route('books.index')->with('success', '書籍を削除しました')` |
| `restore` | `$this->authorize('restore', $book)` → `$book->restore()` → `redirect()->route('books.show', $book)->with('success', '書籍を復元しました')` |

`index` の `with('genres')` と `withAvg` はN+1回避のため必須。

---

## 3. 画面契約（Bladeモック実測・改変禁止）

| 画面ID | Bladeファイル | 渡す変数 | 必須の属性・リレーション | flashスロット |
|---|---|---|---|---|
| PG01 | `books/index.blade.php` | `$books`（Paginator・10件） | `image_url` `title` `author` `genres[].name` `reviews_avg_rating` `->links()` | `session('success')` あり |
| PG02 | `books/show.blade.php` | `$book` | `image_url` `title` `author` `isbn` `published_date` `description` `genres[].name` `reviews[]`（`user.name` `rating` `comment` `created_at` `likedByUsers`）／`Auth::user()->favoriteBooks`／`Auth::user()->likedReviews` | `session('success')` あり |
| PG03 | `books/create.blade.php` + `_form.blade.php` | `$genres` | `$genres->isEmpty()` `$genre->id` `$genre->name` | なし（`$errors`と`old()`のみ） |
| PG04 | `books/edit.blade.php` + `_form.blade.php` | `$book`, `$genres` | 上記＋`$book->genres->pluck('id')` | なし |

フォーム項目（`_form.blade.php`実測）: `title` / `author` / `isbn` / `published_date`(date) / `description`(textarea) / `image_url` / `genres[]`(checkbox・複数)。必須マーク`*`は title / author / ISBN-13 / 出版日 / ジャンル。ISBN欄の注記「13桁のISBNコードを入力してください」、placeholder `9784000000000`。

flashを描画できるのは `books.index` / `books.show` / `genres.index` の3画面のみ。書籍系のリダイレクト先はこの制約を満たしている。

---

## 4. バリデーション（FormRequestに分離。`messages()`は下表の文言をそのまま実装）

### `App\Http\Requests\StoreBookRequest`

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

`authorize()` は `true`。

### `App\Http\Requests\UpdateBookRequest`

`isbn`以外はStoreBookRequestと同一。差分は1点。

| フィールド | ルール | メッセージ |
|---|---|---|
| `isbn` | `required, string, regex:/^[0-9]{13}$/, unique:books,isbn,{book},id`（自身除外） | ISBNは13桁の数字で入力してください／このISBNは既に登録されています |

`Rule::unique('books', 'isbn')->ignore($this->route('book'))` で実装。

**確定事項（変更禁止）**: `unique:books,isbn` は削除済み行も対象に含める（`withTrashed()`相当の除外を入れない）。削除済み書籍のISBNは「使用中」のまま扱う。根拠は復元機能との整合（削除→同ISBNで新規登録→復元、の順で重複が発生するのを防ぐため）。

---

## 5. 認可（`App\Policies\BookPolicy`）

コントローラーで `$this->authorize()` を呼び、Bladeは `@can` で分岐する。

| メソッド | 判定 |
|---|---|
| `update(User $user, Book $book)` | `$user->id === $book->user_id && ! $book->trashed()` |
| `delete(User $user, Book $book)` | `$user->id === $book->user_id && ! $book->trashed()` |
| `restore(User $user, Book $book)` | `$user->id === $book->user_id && $book->trashed()` |

`viewAny` / `view` / `create` は定義しない（一覧・詳細は公開、登録は所有者概念なしでログイン必須のみ）。

`! $book->trashed()` を条件に含めることで、`books/show.blade.php` 既存の `@can('update', $book)` / `@can('delete', $book)` がそのまま「削除済みなら編集・削除ボタン非表示」を満たす。**この2箇所のBladeは改変不要**。

---

## 6. 画面遷移・フラッシュ文言

| 操作 | 成功時の遷移先 | フラッシュ文言 | 失敗時 | 認可失敗時 |
|---|---|---|---|---|
| 一覧を表示 | 当該画面（公開） | — | — | — |
| 「書籍を登録」ボタン | `books.create` | — | — | 未認証: `/login` へ |
| 登録フォーム送信 | `books.show($book)` | `書籍を登録しました` | `back()`+`$errors`（自動） | 未認証: `/login` へ |
| 「編集」ボタン | `books.edit` | — | — | 403 |
| 更新フォーム送信 | `books.show($book)` | `書籍を更新しました` | `back()`+`$errors`（自動） | 403 |
| 「削除」ボタン | `books.index` | `書籍を削除しました` | 業務エラーなし（論理削除） | 403 |
| 「復元する」ボタン | `books.show($book)` | `書籍を復元しました` | 業務エラーなし | 403（本人以外の直URL叩きのみ到達） |

---

## 7. 削除済み書籍の挙動（本発注書の中核・面談②確定）

書籍は物理削除せず`deleted_at`による論理削除。

| 場所 | 削除済み書籍の扱い |
|---|---|
| 書籍一覧（PG01） | 表示されない（SoftDeletesの標準除外） |
| 書籍詳細（PG02） | **表示する**（`withTrashed()`。404にしない） |

書籍詳細が削除済みのときの要素別挙動:

| 要素 | 挙動 |
|---|---|
| バナー | ページ上部に「この本は削除されました」を表示 |
| レビュー・評価一覧 | そのまま表示（本人以外の分も全部読める） |
| 編集・削除ボタン | 非表示（`BookPolicy`が false を返すため。Blade改変不要） |
| 復元ボタン | 登録者本人にのみ表示。押すと通常状態に戻る |
| お気に入りボタン | 非表示 |
| 新規レビュー投稿フォーム | 非表示。「削除済みの書籍にはレビューを投稿できません」に差し替え |
| レビューの「いいね」／投稿者本人の編集・削除 | 操作可能（変更なし） |

### 8-1. `books/show.blade.php` への追記（Blade改変が必要な唯一の箇所）

frozen宣言のあるモックだが、削除済み状態の表示は元のモックに定義自体が存在しない。**既存の記述を書き換えず、以下4点のみ追記する**。

1. `session('success')` ブロック直後に、`@if($book->trashed())` で囲んだバナー「この本は削除されました」を追加
2. お気に入りボタンの `@auth` ブロック全体を `@if(! $book->trashed())` で囲む
3. レビュー投稿フォームの `@auth` ブロック全体を `@if(! $book->trashed())` で囲み、`@else` 側に「削除済みの書籍にはレビューを投稿できません」の案内文（`<p class="mb-6 text-gray-600">`）を置く
4. 編集・削除ボタンの `<div class="flex gap-2 mt-4">` 内に、`@can('restore', $book)` で囲んだ「復元する」ボタン（`PATCH`を`@method('PATCH')`で送るform）を追加

上記4点以外のマークアップ・クラス名・レイアウトは一切変更しないこと。

---

## 9. テーブル・モデル（走行①で作成済み。本走行では変更しない）

`books` / `book_genre` テーブル、`App\Models\Book` は走行①で確定済み。本走行はコントローラー・Policy・FormRequestの追加のみで、migrationもモデルのプロパティ定義も一切変更しない。

参考（変更禁止・確認用）:

```php
class Book extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['title', 'author', 'isbn', 'published_date', 'description', 'image_url', 'user_id'];
    protected $casts = ['published_date' => 'date'];
    public function user()             { return $this->belongsTo(User::class); }
    public function genres()           { return $this->belongsToMany(Genre::class, 'book_genre'); }
    public function reviews()          { return $this->hasMany(Review::class); }
    public function favoritedByUsers() { return $this->belongsToMany(User::class, 'favorites'); }
}
```

---

## 10. 実装上の注意（CC向け）

- `BookController@show` と `@restore` は `withTrashed()` 付きルートで解決する。付け忘れると削除済み書籍が404になる。
- `store` / `update` は `DB::transaction()` で書籍保存とジャンルsyncを1単位にする。
- `image_url` は `nullable` だが、Bladeは `@if($book->image_url)` でガード済みなので空でも崩れない。
- `/` は `books.index` と同一アクションに束ねる。`welcome.blade.php` はどの `route()` からも参照されない未使用ファイルなので、ルート定義の対象外。
- `Review::book()` リレーションに `->withTrashed()` が付いているか確認すること（books.mdの管轄外だが、付いていないと本走行のF-B9・F-B13相当の挙動が壊れる）。これは走行①で `App\Models\Review` に既に定義済みのはずであり、本走行で変更しない。

---

## 11. 禁止事項

1. `resources/` 配下のBlade / CSS / JSを、本書§8-1に明記した4点以外は1文字も変更しない。
2. 本発注書に明記されていないバリデーションロジック・認可ロジックを独自に追加しない（発注書の指定と異なる実装をする場合は、仮決めせず`QUESTIONS.md`に記録して停止する）。
3. `migrate:fresh` を要するmigration変更をしない（テーブル定義は走行①で確定済み）。
4. 「やらないこと」表に挙げた成果物を先取りして作らない。
5. `main` ブランチへ直接コミットしない。

---

## 12. 未定義事項に当たったとき

この発注書・`CLAUDE.md`のいずれにも定義がない事項に当たった場合、その場で仮決めせず`QUESTIONS.md`へ以下4欄を記入し、該当作業を止めて次の作業に移る。

- 発生日
- 対象発注書（`02_発注書/02_書籍CRUD.md`）
- 止まった箇所（ファイル・行・実行したコマンド）
- CCの解釈候補（2案以上）

---

## 13. 完了時の報告

- 作業ブランチ名と最終コミットID
- `sail artisan route:list --path=books` の出力
- `sail bin pint --test` の出力
- `QUESTIONS.md` に追記した行の有無
