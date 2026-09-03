# 機能仕様書 — genres（ジャンルCRUD・削除制限）

| 項目 | 内容 |
|---|---|
| 対象発注書 | 04_ジャンルランキング（ジャンル部分） |
| 正本 | 要件シート.xlsx シート5/7/8/9/10/11/12 ＋ Bladeモック `basic`（frozen） |
| 適用範囲 | 基本要件のみ |
| 完成条件 | 本書だけで発注書04のジャンル部分が書ける（他ファイル参照不要） |
| 版 | v1（2026-09-01・面談②のSoftDelete確定を反映済み） |

---

## 0. スコープ

**含む**: ジャンルの一覧・詳細・登録・編集・削除、削除制限（二重防御）、genres / book_genre テーブル、GenreSeeder、ジャンルのテスト観点。

**含まない**: 書籍CRUDとジャンル紐付けの sync（books.md）／ランキング（ranking.md）／全テーブルのmigration統括（auth.md）。

**ジャンルには所有者概念がない**。ログイン済みであれば誰でも登録・編集・削除できる。したがって GenrePolicy は作らない。

---

## 1. ルーティング

| # | メソッド | URI | route名 | Controller@Action | 認証 | 認可 |
|---|---|---|---|---|---|---|
| 1 | GET | `/genres` | `genres.index` | `GenreController@index` | 必須 | — |
| 2 | GET | `/genres/create` | `genres.create` | `GenreController@create` | 必須 | — |
| 3 | POST | `/genres` | `genres.store` | `GenreController@store` | 必須 | — |
| 4 | GET | `/genres/{genre}` | `genres.show` | `GenreController@show` | 必須 | — |
| 5 | GET | `/genres/{genre}/edit` | `genres.edit` | `GenreController@edit` | 必須 | — |
| 6 | PUT | `/genres/{genre}` | `genres.update` | `GenreController@update` | 必須 | — |
| 7 | DELETE | `/genres/{genre}` | `genres.destroy` | `GenreController@destroy` | 必須 | アプリ側事前チェック |

```php
Route::middleware('auth')->group(function () {
    Route::resource('genres', GenreController::class);
});
```

ジャンル系は7本すべて `auth` 必須（公開ページではない）。

---

## 2. コントローラー仕様（`App\Http\Controllers\GenreController`）

| アクション | 処理 |
|---|---|
| `index` | `Genre::withCount('books')->orderBy('id')->get()` を `$genres` として `genres.index` ビューへ渡す |
| `create` | `genres.create` ビューを返す（変数なし） |
| `store` | `StoreGenreRequest` で検証 → `Genre::create($validated)` → `redirect()->route('genres.index')->with('success', 'ジャンルを登録しました')` |
| `show` | `$genre` と、`$genre->books()->with('genres')->latest('books.created_at')->paginate(10)` を `$books` として `genres.show` ビューへ渡す |
| `edit` | `$genre` を `genres.edit` ビューへ渡す |
| `update` | `UpdateGenreRequest` で検証 → `$genre->update($validated)` → `redirect()->route('genres.index')->with('success', 'ジャンルを更新しました')` |
| `destroy` | §5 の削除制限を実行。可なら `$genre->delete()` → success flash、不可なら error flash。いずれも `genres.index` へリダイレクト |

`withCount('books')` は Blade 実測の `$genre->books_count` を供給するために必須。`Book` が SoftDeletes を使うため、この件数は**生存している書籍のみ**を数える。

---

## 3. 画面契約（Bladeモック実測・改変禁止）

| 画面ID | Bladeファイル | 渡す変数 | Blade が要求する属性 | flashスロット |
|---|---|---|---|---|
| PG05 | `genres/index.blade.php` | `$genres`（Collection） | `$genres->isEmpty()` / `$genre->name` / `$genre->books_count` | `session('success')` と `session('error')` の**両方あり**（error スロットを持つ唯一の画面） |
| PG06 | `genres/show.blade.php` | `$genre`, `$books`（Paginator・10件） | `$genre->name` / `$books->isEmpty()` / `$book->image_url` `title` `author` / `$book->genres[].id` `.name` / `$books->links()` | なし |
| PG07 | `genres/create.blade.php` | — | `old('name')` | なし |
| PG08 | `genres/edit.blade.php` | `$genre` | `old('name', $genre->name)` | なし |

- `genres/index` の一覧は表形式で、列は「ジャンル名 / 書籍数 / 操作（編集・削除）」。書籍数は `{{ $genre->books_count }}冊`。
- `genres/show` の各書籍タグは、表示中のジャンルと一致するものだけ色が変わる（`$g->id === $genre->id`）。そのため `$books` には `with('genres')` が必須。
- フォーム項目は `name` の1つだけ（必須マーク `*` あり）。

---

## 4. バリデーション（要件シート シート8・確定版）

### `App\Http\Requests\StoreGenreRequest`（genres.store）

| フィールド | ルール | メッセージ |
|---|---|---|
| `name` | `required, string, max:255, unique:genres,name` | ジャンル名を入力してください／ジャンル名は255文字以内で入力してください／このジャンル名は既に登録されています |

### `App\Http\Requests\UpdateGenreRequest`（genres.update）

| フィールド | ルール | メッセージ |
|---|---|---|
| `name` | `required, string, max:255, unique:genres,name,{genre},id`（自身のレコードを除外） | ジャンル名を入力してください／ジャンル名は255文字以内で入力してください／このジャンル名は既に登録されています |

`Rule::unique('genres', 'name')->ignore($this->route('genre'))` で実装する。両クラスとも `authorize()` は `true`。

---

## 5. 削除制限（二重防御・本書の中核）

紐づく書籍が1件でもあるジャンルは削除させない。**アプリ側の事前チェックが第一防御、DB制約が第二防御**。

### 第一防御：アプリ側事前チェック（`GenreController@destroy`）

```php
if ($genre->books()->withTrashed()->exists()) {
    return redirect()->route('genres.index')
        ->with('error', 'このジャンルに紐づく書籍が存在するため削除できません');
}
$genre->delete();
return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました');
```

**`withTrashed()` を必ず付けること。** `book_genre.genre_id` は `restrictOnDelete()` であり、論理削除された書籍の `book_genre` 行も残り続ける。`withTrashed()` を付けずに生存書籍だけで判定すると、「削除済み書籍にのみ紐づくジャンル」でアプリ側チェックを通過し、DB制約側で `QueryException` が発生して画面が500になる。判定範囲をDB制約と一致させるのが必須条件。

なお、一覧に表示される `books_count` は生存書籍のみを数える（Blade実測が `withCount('books')` を前提としているため変更しない）。結果として「書籍数 0冊 と表示されているのに削除できない」ケースが起こり得るが、これはBladeの表示契約とDB制約の判定範囲が別物であることによる仕様であり、error flash の文言で削除できない理由は伝わる。

### 第二防御：DB制約

`book_genre.genre_id` に `restrictOnDelete()`。アプリ側チェックを迂回した経路（直接のDELETEリクエスト、tinkerからの操作）でも削除を阻止する。

---

## 6. 画面遷移・フラッシュ文言（要件シート シート7・確定版）

| 操作 | 成功時の遷移先 | フラッシュ文言 | 失敗時 | 認可失敗時 |
|---|---|---|---|---|
| 一覧を表示 | 当該画面 | — | — | 未認証: `/login` へリダイレクト |
| ジャンル名を押す | `genres.show` | — | — | — |
| 「ジャンルを登録」ボタン | `genres.create` | — | — | 未認証: `/login` へ |
| 登録フォーム送信 | `genres.index` | `ジャンルを登録しました` | `back()` + `$errors`（自動） | 未認証: `/login` へ（所有者概念なし） |
| 「編集」ボタン | 当該画面を表示 | — | — | 未認証: `/login` へ（所有者概念なし。ログイン済みなら誰でも編集可） |
| 更新フォーム送信 | `genres.index` | `ジャンルを更新しました` | `back()` + `$errors`（自動） | 未認証: `/login` へ |
| 「削除」ボタン（紐付きなし） | `genres.index` | `ジャンルを削除しました` | — | 未認証: `/login` へ |
| 「削除」ボタン（紐付きあり） | `genres.index` | error: `このジャンルに紐づく書籍が存在するため削除できません` | — | 未認証: `/login` へ |
| 詳細を表示 | 当該画面 | — | — | 未認証: `/login` へ |

削除の成功・失敗がどちらも `genres.index` へ戻るのは、この画面が success / error の両スロットを持つ唯一の画面だから。

---

## 7. テーブル仕様（要件シート シート12）

### genres

| カラム | 型 | PK | NOT NULL | FK | 補足 |
|---|---|---|---|---|---|
| id | bigint unsigned | ○ | ○ | | `$table->id()` |
| name | varchar(255) | | ○ | | UNIQUE |
| created_at / updated_at | timestamp | | | | `$table->timestamps()` |

### book_genre（中間・複合主キー）

| カラム | 型 | PK | NOT NULL | FK | 補足 |
|---|---|---|---|---|---|
| book_id | bigint unsigned | ○（複合） | ○ | books.id | `cascadeOnDelete()`。論理削除下では発火しない設定だが害はないため残す |
| genre_id | bigint unsigned | ○（複合） | ○ | genres.id | `restrictOnDelete()`。紐付き書籍がある間は削除禁止 |

`$table->primary(['book_id', 'genre_id'])`。timestamps は持たせない。

---

## 8. モデル（`App\Models\Genre`）

```php
class Genre extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function books() { return $this->belongsToMany(Book::class, 'book_genre'); }
}
```

SoftDeletes は使わない（ジャンルは物理削除）。中間テーブルは timestamps を持たないので `withTimestamps()` は付けない。

---

## 9. 実装上の注意（CC向け）

- `genres/index` の一覧順は `orderBy('id')`（シーダー投入順）。要件シート・Bladeモックとも並び順の指定がないため、最も単純で決定的な順序を採用している。
- `genres/show` の書籍一覧は `books.created_at` の降順（新しい順）。書籍一覧（PG01）の「最新順」と揃えている。
- `$genre->books()` は SoftDeletes の影響を受ける。**表示（books_count・一覧）は生存書籍のみ、削除可否判定は `withTrashed()` 付き**という使い分けを取り違えないこと。
- Blade は改変不要。ジャンル機能はモックの契約どおりに実装できる。

---

## 10. シーディング（GenreSeeder・要件シート シート9／採点直結・改変禁止）

genres テーブルにジャンルを固定で10件投入する。`firstOrCreate` を使用し `name` の重複を防ぐ。

`小説` / `ビジネス` / `技術書` / `自己啓発` / `エッセイ` / `歴史` / `科学` / `芸術` / `料理` / `旅行`

投入順がそのまま id 1〜10 になる（`技術書` は id=3。公開APIのレスポンス例が `genre_id=3` を技術書として使っている）。

---

## 11. テスト観点（要件シート シート10）

**全体要件（共通）**: 全テスト通過。`sail artisan test --coverage` で基本機能のみ60%超を目標。

### 単体テスト `tests/Unit/GenreTest.php`

| # | 検証観点 |
|---|---|
| U-G1 | `Genre` の `belongsToMany Book`（book_genre 経由）が正しく取得できる |

### 機能テスト `tests/Feature/GenreTest.php`

| # | 検証観点 |
|---|---|
| F-G1 | 登録バリデーション — `name` 未入力でエラーが表示される |
| F-G2 | 登録バリデーション — `name` 重複でエラーが表示される |
| F-G3 | 編集の認可 — 所有者概念がなく、ログイン済みであれば作成者でなくても編集できる |
| F-G4 | 削除制限 — 紐づく書籍が1件でも存在する場合、error flash「このジャンルに紐づく書籍が存在するため削除できません」とともに削除が失敗し、レコードが残る |
| F-G5 | 削除成功 — 紐づく書籍が存在しない場合は削除でき、「ジャンルを削除しました」が表示される |
| F-G6 | 二重防御 — アプリ側の事前件数チェックとDB制約 `restrictOnDelete()` の両方が機能し、アプリ側チェックを迂回してもDB側で削除が防止される |
