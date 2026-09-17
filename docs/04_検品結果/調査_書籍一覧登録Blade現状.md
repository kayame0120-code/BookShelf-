# 調査_書籍一覧登録Blade現状

## やったこと（事実のみ）
- `resources/views/books/index.blade.php` / `create.blade.php` / `_form.blade.php` / `edit.blade.php` を読み取り、指定の grep を実行して生出力を採取した。
- `resources/views/` 配下・`resources/js`・`public/js`・`routes/web.php`・`app/Http/Controllers/BookController.php` を参照し、検索フォーム／ISBN自動入力に相当する partial・JS・ルートの所在を確認した。
- 実装コード（resources/・app/・routes/）は一切変更していない。

## 変更ファイル
なし（`git diff --stat` 空）

実行コマンド：
```
$ git branch --show-current && echo "---STAT---" && git diff --stat
```
出力：
```
feature/14-advanced-tests
---STAT---
```
（現在のブランチ名：`feature/14-advanced-tests`。`---STAT---` の後に出力なし＝差分なし）

## 証拠（生の実行結果のみ）

### books ディレクトリ構成（partial の実在確認）

実行コマンド：
```
$ ls -la resources/views/books/
```
出力（抜粋、ファイル一覧）：
```
-rw-r--r-- create.blade.php
-rw-r--r-- edit.blade.php
-rw-r--r-- _form.blade.php
-rw-r--r-- index.blade.php
-rw-r--r-- show.blade.php
```
（登録・編集フォームの入力項目は `_form.blade.php` に集約され、`create.blade.php` / `edit.blade.php` から `@include('books._form')` される。検索フォーム用の別 partial は books ディレクトリに存在しない。）

---

### 論点1: 書籍一覧 index.blade.php の検索フォーム

実行コマンド：
```
$ grep -nE "name=\"(keyword|genre|sort)\"|<form|検索|リセット|書籍を登録" resources/views/books/index.blade.php
```
出力（exit=0）：
```
12:                    書籍を登録
```
（ヒットは12行目の「書籍を登録」ボタン文字列のみ。`<form`、`name="keyword"`、`name="genre"`、`name="sort"`、「検索」、「リセット」はいずれもヒットしない。）

index.blade.php の該当コード引用：
- `resources/views/books/index.blade.php:10-14`（グリッド上部にあるのは「書籍を登録」リンク1個のみ。検索フォームのカードは無い）
```
10  <div class="mb-4 flex justify-end">
11      <a href="{{ route('books.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
12          書籍を登録
13      </a>
14  </div>
```
- `resources/views/books/index.blade.php:22-48`（`書籍を登録`リンクの直後は `bg-white ... shadow` カード→書籍グリッド `@foreach($books ...)` が続く。両者の間に `<form>` 要素・keyword/genre/sort 入力は存在しない）

resources/views 全体で検索フォーム用 partial を探した結果：

実行コマンド：
```
$ grep -rlnE "name=\"keyword\"|name=\"sort\"|searchByIsbn|自動入力" resources/views/ 2>/dev/null || echo "(no files matched)"
```
出力：
```
(no files matched)
```

参考（コントローラ側の文脈。index は keyword/genre/sort をクエリ受理する）：
`app/Http/Controllers/BookController.php:23-56`
```
23  public function index(Request $request): View
25      $query = Book::query()->with('genres');
27      $keyword = trim((string) $request->query('keyword', ''));
35      $genre = (int) $request->query('genre', 0);
42      $sort = (string) $request->query('sort', 'newest');
52      return view('books.index', [
53          'books' => $books,
54          'genres' => Genre::all(),
55      ]);
```
（コントローラは `keyword`/`genre`/`sort` を `$request->query()` で受け取り、`genres` をビューへ渡している。一方 index.blade.php 側にはこれらを送出する `<form>`・入力要素・select が存在しない。渡された `$genres` を参照する箇所も index.blade.php には無い。）

---

### 論点2: 書籍登録 create.blade.php の ISBN 自動入力セクション

実行コマンド：
```
$ grep -nE "isbn|ISBN|searchByIsbn|自動入力|Google|fetch\(|axios|<script" resources/views/books/create.blade.php
```
出力（exit=1、ヒット0件）：
```
（出力なし）
```
（create.blade.php 内に `isbn`/`ISBN`/`searchByIsbn`/`自動入力`/`Google`/`fetch(`/`axios`/`<script` はいずれもヒットしない。）

create.blade.php 全文構造の引用：
- `resources/views/books/create.blade.php:12-23`（フォームは `books.store` への POST。最上部に独立した「ISBN から書籍情報を自動入力」セクションは無く、`<form>` 直下は即 `@include('books._form')`）
```
12  <form action="{{ route('books.store') }}" method="POST" novalidate>
13      @include('books._form')
15      <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
16          <a href="{{ route('books.index') }}" ...>キャンセル</a>
19          <button type="submit" ...>登録</button>
```

ISBN 入力欄の実体（`_form.blade.php` 内。登録項目としての1フィールドのみ）：

実行コマンド：
```
$ grep -nE "name=\"isbn\"|searchByIsbn|自動入力|Google|fetch\(|axios|<script" resources/views/books/_form.blade.php
```
出力（exit=0）：
```
38:        <input type="text" name="isbn" id="isbn" value="{{ old('isbn', $book->isbn ?? '') }}"
```
（`_form.blade.php` の ISBN 関連ヒットは38行目の `name="isbn"` 入力欄1個のみ。`searchByIsbn`/`自動入力`/`Google`/`fetch(`/`axios`/`<script` はヒットしない。）

該当コード引用 `resources/views/books/_form.blade.php:33-45`：
```
33  <!-- ISBN -->
34  <div>
35      <label for="isbn" ...>ISBN-13 <span class="text-red-500">*</span></label>
38      <input type="text" name="isbn" id="isbn" value="{{ old('isbn', $book->isbn ?? '') }}"
40          placeholder="9784000000000">
41      <p class="text-xs text-gray-500 mt-1">13桁のISBNコードを入力してください</p>
```
（ISBN 入力は登録項目として `name="isbn"` が1つ。自動入力用の別 ISBN 入力欄・専用「検索」ボタンは存在しない。）

ルート・JS 側の文脈（Blade から呼ばれているかの確認）：

実行コマンド：
```
$ grep -nE "isbn|searchByIsbn|Isbn" routes/web.php
```
出力：
```
31:    Route::get('/books/isbn/{isbn}', [BookController::class, 'searchByIsbn'])->name('books.searchByIsbn');
```

実行コマンド：
```
$ grep -rnE "searchByIsbn|books/isbn|autofill|自動入力" resources/js public/js 2>/dev/null || echo "(no matches)"
```
出力：
```
(no matches)
```
（`GET /books/isbn/{isbn}`（`books.searchByIsbn`）ルートと `BookController::searchByIsbn`（`app/Http/Controllers/BookController.php:61`）はサーバ側に存在する。しかし `resources/views/` の Blade・`resources/js`・`public/js` のいずれからも、この URL を叩く JS（fetch/axios）・専用 ISBN 入力・「検索」ボタンは参照されていない。）

---

### 参考: 正本 Bladeモック参照.md

実行コマンド：
```
$ cat docs/00_正本/Bladeモック参照.md
```
出力（抜粋の生引用）：
```
## リポジトリ
https://github.com/coachtech-prepared-file/Preparedblade-mockcase-BookShelf.git

## ブランチ
| basic    | 基本機能の正本 | frozen（改変禁止） |
| advanced | 応用機能の正本 | frozen（改変禁止） |

## frozen宣言
両ブランチとも改変禁止。... Bladeモックが優先する（CLAUDE.md 0章の正本優先順位1位）。
```
（モック本体はローカルに無く、GitHub の basic/advanced ブランチにある旨のみ記載。書籍一覧・登録モックの具体ファイル名の記述はこのファイルには無い。ローカルに clone された `blade-basic` / `blade-advanced` ディレクトリは確認範囲に存在しなかった。）

## 未確認・保留
- Bladeモック本体（basic/advanced ブランチ）はローカルに clone されておらず、モック側の index/create の実体（検索フォーム・ISBN自動入力セクションの有無）は本調査では直接参照できていない。片倉指摘の「デザイン（正しい姿）」との一字一致比較はモック本体の取得が必要。
- `docs/00_正本/Bladeモック参照.md` には書籍一覧・登録モックの具体ファイルパスの記述が無いため、どのファイルが対応モックかは未確認。

## worktree情報
- ブランチ名：`feature/14-advanced-tests`
- 本体へのマージ：未（本タスクは読み取り調査のみ。実装変更・コミットなし。`git diff --stat` 空）

## 判定はしない
本ファイルは YES/NO・PASS/FAIL の判定を含まない。判定は片倉／チャット側がこの証拠を見て行う。
