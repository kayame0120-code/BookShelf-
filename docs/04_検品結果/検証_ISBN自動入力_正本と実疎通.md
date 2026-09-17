# 検証 — ISBNから書籍情報を自動入力（Google Books API連携）／正本引用・実コード確認・実疎通

## やったこと（事実のみ）
- 正本（機能仕様書 books.md §2-2、発注書_08_ISBN検索.md）の該当記述を grep・引用で抽出した。
- `BookController::searchByIsbn`・`routes/web.php`・`resources/views/books/create.blade.php`・`resources/views/books/_form.blade.php` の該当箇所を行番号付きで引用した。
- コンテナから実 Google Books API を curl／Httpファサード／コントローラ経由で1回ずつ叩き、生の応答を採取した。

## 変更ファイル
実装コード（app/・routes/・resources/）の変更：なし。

```
$ git status --porcelain
?? .claude/
$ git diff --stat -- app/ routes/ resources/
（出力なし＝空）
```

```
$ git branch --show-current
fix/book-search-and-isbn-ui
```

---

## 証拠（生の実行結果・コード引用）

### ① 正本は何と書いてあるか（生引用）

grep コマンド：
```
$ grep -rniE "isbn|Google Books|自動入力|自動補完|volumes" docs/01_機能仕様書/
```
（`docs/01_機能仕様書/books.md` の ISBN検索関連ヒット。抜粋）
```
docs/01_機能仕様書/books.md:30:| 5 | GET | `/books/isbn/{isbn}` | `books.isbn` | `BookController@searchByIsbn` | 必須 | — | ★応用 |
docs/01_機能仕様書/books.md:127:### 2-2. ★ searchByIsbn の仕様（Google Books API 連携）
docs/01_機能仕様書/books.md:159:| `{isbn}` が13桁の数字でない | 422 | `{"error": "ISBNは13桁の数字で入力してください"}` |
docs/01_機能仕様書/books.md:160:| Google Books API が該当書籍なし（`totalItems` が 0、または `items` キーなし） | 404 | `{"error": "該当する書籍が見つかりませんでした"}` |
docs/01_機能仕様書/books.md:161:| Google Books API への通信失敗・APIが5xxを返す | 502 | `{"error": "書籍情報の取得に失敗しました"}` |
docs/01_機能仕様書/books.md:166:- リクエスト先は `https://www.googleapis.com/books/v1/volumes?q=isbn:{isbn}`。APIキーは不要のため `.env` への追加も不要。
```

**機能仕様書 books.md §2-2（docs/01_機能仕様書/books.md:127-169）原文抜粋：**
```
### 2-2. ★ searchByIsbn の仕様（Google Books API 連携）

`books/create.blade.php` と `books/edit.blade.php`（advanced）に埋め込まれた JavaScript が `fetch('/books/isbn/' + isbn)` を呼び、返った JSON を各入力欄へ代入する。JS は `data.error` の有無だけで分岐するため、**成功時に `error` キーを含めてはならない**。

**成功レスポンス（200）**
{
  "title": "リーダブルコード",
  "author": "Dustin Boswell",
  "description": "より良いコードを書くための...",
  "image_url": "https://books.google.com/books/content?id=...",
  "published_date": "2012-06-23"
}

| キー | Google Books API の取得元 | 値がないとき |
| `title` | `items[0].volumeInfo.title` | `null` |
| `author` | `items[0].volumeInfo.authors[0]`（配列の先頭1件のみ） | `null` |
| `description` | `items[0].volumeInfo.description` | `null` |
| `image_url` | `items[0].volumeInfo.imageLinks.thumbnail` | `null` |
| `published_date` | `items[0].volumeInfo.publishedDate` | `null` |

**エラーレスポンス**
| `{isbn}` が13桁の数字でない | 422 | `{"error": "ISBNは13桁の数字で入力してください"}` |
| Google Books API が該当書籍なし（`totalItems` が 0、または `items` キーなし） | 404 | `{"error": "該当する書籍が見つかりませんでした"}` |
| Google Books API への通信失敗・APIが5xxを返す | 502 | `{"error": "書籍情報の取得に失敗しました"}` |

**実装上の確定事項**
- 通信は `Illuminate\Support\Facades\Http` を使う（CLAUDE.md 応用フェーズ追加ルール）。
- リクエスト先は `https://www.googleapis.com/books/v1/volumes?q=isbn:{isbn}`。APIキーは不要のため `.env` への追加も不要。
- 13桁チェックはコントローラー冒頭で行う。FormRequest には分離しない。
- 通信失敗の検知には `Http::get(...)` の戻り値に対する `->failed()` と、`ConnectionException` の捕捉の両方を用いる。
- 本アクションは書籍レコードを一切作成・更新しない。取得した値を返すだけ。
```

grep コマンド：
```
$ grep -niE "isbn|Google Books|自動入力|自動補完|volumes|13桁|502|422|404" docs/02_発注書/発注書_08_ISBN検索.md
```

**発注書_08_ISBN検索.md 抜粋（原文）：**
```
発注書_08:16:1. `GET /books/isbn/{isbn}` エンドポイントの新設（Google Books APIを叩いてフォーム自動入力用JSONを返す）
発注書_08:37:Route::get('/books/isbn/{isbn}', [BookController::class, 'searchByIsbn'])
発注書_08:39:    ->name('books.searchByIsbn');
発注書_08:50:`{isbn}`は13桁の数字文字列であることをコントローラー内で検証する（…コントローラー冒頭で正規表現チェックする）。桁数不正時のエラー応答は、キーを`message`とし、HTTPステータス422で返す。
発注書_08:53:if (! preg_match('/^[0-9]{13}$/', $isbn)) {
発注書_08:54:    return response()->json(['message' => 'ISBNは13桁の数字で入力してください'], 422);
発注書_08:63:- エンドポイント: `GET https://www.googleapis.com/books/v1/volumes?q=isbn:{isbn}`。APIキーは設定しない…
発注書_08:67:$response = Http::timeout(5)->get('https://www.googleapis.com/books/v1/volumes', ['q' => "isbn:{$isbn}",
発注書_08:76:エラー系レスポンスのキーはすべて`message`に統一する
発注書_08:80:| 通信自体が失敗（接続エラー・タイムアウト・5xx） | 502 | `{"message": "書籍情報の取得に失敗しました"}` |
発注書_08:81:| 通信成功・`totalItems`が0（該当書籍なし） | 404 | `{"message": "該当する書籍が見つかりませんでした"}` |
発注書_08:82:| 通信成功・`totalItems`が1以上 | 200 | `{"title": ..., "author": ..., "description": ..., "image_url": ..., "published_date": ...}` |
発注書_08:105:    'title'          => $volumeInfo['title'] ?? '',
発注書_08:106:    'author'         => implode('、', $volumeInfo['authors'] ?? []),
発注書_08:107:    'description'    => $volumeInfo['description'] ?? '',
発注書_08:108:    'image_url'      => $volumeInfo['imageLinks']['thumbnail'] ?? '',
発注書_08:109:    'published_date' => $volumeInfo['publishedDate'] ?? '',
発注書_08:117:- 成功時レスポンスキー名（`title`・`author`・`description`・`image_url`・`published_date`）はシート10 R25の完了条件に完全一致させること。
発注書_08:118:- エラー時レスポンスキーは`message`とする。
```

**正本間の相違（生引用の事実）：**
- エラー時JSONキー：機能仕様書 books.md §2-2（:159-161）は `"error"`。発注書_08（:76,:80-82,:118）は `"message"`。
- 成功時著者：機能仕様書 books.md（:146）は「`authors[0]`（配列の先頭1件のみ）」。発注書_08（:106,:115）は「`、`（読点）で連結」。
- ルート名：機能仕様書 books.md（:30）は `books.isbn`。発注書_08（:39）は `books.searchByIsbn`。

grep コマンド（`docs/00_正本/`）：
```
$ grep -rniE "isbn|Google Books|自動入力|volumes" docs/00_正本/Bladeモック参照.md
（下記「未確認・保留」に記載。要件シート.xlsx はバイナリのため grep 対象外）
```

### ② バックエンドは実コードか（該当箇所の引用）

`app/Http/Controllers/BookController.php:61-93`：
```php
61	    public function searchByIsbn(string $isbn): JsonResponse
62	    {
63	        if (! preg_match('/^[0-9]{13}$/', $isbn)) {
64	            return response()->json(['message' => 'ISBNは13桁の数字で入力してください'], 422);
65	        }
66	
67	        try {
68	            $response = Http::timeout(5)->get('https://www.googleapis.com/books/v1/volumes', [
69	                'q' => "isbn:{$isbn}",
70	            ]);
71	        } catch (ConnectionException $e) {
72	            return response()->json(['message' => '書籍情報の取得に失敗しました'], 502);
73	        }
74	
75	        if ($response->failed()) {
76	            return response()->json(['message' => '書籍情報の取得に失敗しました'], 502);
77	        }
78	
79	        $totalItems = $response->json('totalItems', 0);
80	        if ($totalItems === 0) {
81	            return response()->json(['message' => '該当する書籍が見つかりませんでした'], 404);
82	        }
83	
84	        $volumeInfo = $response->json('items.0.volumeInfo', []);
85	
86	        return response()->json([
87	            'title' => $volumeInfo['title'] ?? '',
88	            'author' => implode('、', $volumeInfo['authors'] ?? []),
89	            'description' => $volumeInfo['description'] ?? '',
90	            'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
91	            'published_date' => $volumeInfo['publishedDate'] ?? '',
92	        ]);
93	    }
```
import 部（同ファイル）：
```php
10	use Illuminate\Http\Client\ConnectionException;
16	use Illuminate\Support\Facades\Http;
```

`routes/web.php:28-36`：
```php
28	// 書籍（認証必須。/books/{book} より前に定義）
29	Route::middleware('auth')->group(function () {
30	    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
31	    Route::get('/books/isbn/{isbn}', [BookController::class, 'searchByIsbn'])->name('books.searchByIsbn');
32	    Route::post('/books', [BookController::class, 'store'])->name('books.store');
33	});
34	
35	// 書籍詳細（公開・削除済みも解決）
36	Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show')->withTrashed();
```
ルート順（`isbn` が `{book}` より前）の確認：
```
$ ./vendor/bin/sail artisan route:list --path=books
  GET|HEAD  books/isbn/{isbn} books.searchByIsbn › BookController@searchByIsbn
  GET|HEAD  books/{book} .................... books.show › BookController@show
```

`resources/views/books/create.blade.php:44-94`（`@push('scripts')` 内 JS）：
```php
44	    @push('scripts')
45	        <script>
46	            document.addEventListener('DOMContentLoaded', function () {
47	                const button = document.getElementById('isbn_search_button');
48	                const input = document.getElementById('isbn_search');
49	                const message = document.getElementById('isbn_search_message');
...
57	                const setValue = function (id, value) {
58	                    const field = document.getElementById(id);
59	                    if (field && value) {
60	                        field.value = value;
61	                    }
62	                };
63	
64	                button.addEventListener('click', async function () {
65	                    const isbn = input.value.trim();
...
69	                        const response = await fetch(`/books/isbn/${encodeURIComponent(isbn)}`, {
70	                            headers: { 'Accept': 'application/json' },
71	                        });
72	                        const data = await response.json();
73	
74	                        if (!response.ok) {
75	                            showMessage(data.message ?? '書籍情報の取得に失敗しました', true);
76	                            return;
77	                        }
78	
79	                        setValue('title', data.title);
80	                        setValue('author', data.author);
81	                        setValue('description', data.description);
82	                        setValue('image_url', data.image_url);
83	                        if (/^\d{4}-\d{2}-\d{2}$/.test(data.published_date ?? '')) {
84	                            setValue('published_date', data.published_date);
85	                        }
86	                        document.getElementById('isbn').value = isbn;
87	                        showMessage('書籍情報を自動入力しました', false);
88	                    } catch (e) {
89	                        showMessage('書籍情報の取得に失敗しました', true);
90	                    }
91	                });
92	            });
93	        </script>
94	    @endpush
```
ISBN検索フォーム要素（同ファイル:16-24）：
```php
16	                            <input type="text" id="isbn_search" value=""
19	                            <button type="button" id="isbn_search_button"
24	                        <p id="isbn_search_message" class="text-sm mt-2 hidden"></p>
```

`resources/views/books/_form.blade.php` 入力要素の id：
```
$ grep -nE "id=|name=" resources/views/books/_form.blade.php
12:        <input type="text" name="title" id="title" ...
25:        <input type="text" name="author" id="author" ...
38:        <input type="text" name="isbn" id="isbn" ...
52:        <input type="date" name="published_date" id="published_date" ...
64:        <textarea name="description" id="description" ...
77:        <input type="text" name="image_url" id="image_url" ...
98:        <input type="checkbox" name="genres[]" ...
```
JS が代入する id（`title`/`author`/`description`/`image_url`/`published_date`/`isbn`）と _form の input id の突き合わせ：全て _form 側に同名 id が存在する。

（注：機能仕様書 books.md §3-1（:192）は ISBN検索フォームの id を `#isbn-search` `#fetch-btn` と記述。実装の id は `isbn_search` `isbn_search_button`。JS と Blade は同一ファイル内で id を自己参照するため画面上は連動するが、正本記載の id 文字列とは不一致。）

### ③ 実際に動くか（実疎通）

1. コンテナから実 Google Books API へ直接 curl（実在ISBN 9784101010014）：
```
$ ./vendor/bin/sail exec -T laravel.test curl -s -m 10 "https://www.googleapis.com/books/v1/volumes?q=isbn:9784101010014"
{
  "error": {
    "code": 429,
    "message": "Quota exceeded for quota metric 'Queries' and limit 'Queries per day' of service 'books.googleapis.com' for consumer 'project_number:624717413613'.",
    ...
    "status": "RESOURCE_EXHAUSTED",
    ...
        "quota_limit_value": "0",
```
HTTPステータス：
```
$ ./vendor/bin/sail exec -T laravel.test curl -s -o /dev/null -w "HTTP_STATUS:%{http_code}\n" -m 10 "https://www.googleapis.com/books/v1/volumes?q=isbn:9784101010014"
HTTP_STATUS:429
```
→ ネットワークは Google に到達する。APIキー未設定のため keyless quota（`quota_limit_value: 0`）で HTTP 429 が返り、書籍データ（items）は返らない。

2. アプリのHttpファサード経由（tinker）：
```
$ ./vendor/bin/sail artisan tinker --execute="\$r = Illuminate\Support\Facades\Http::timeout(5)->get('https://www.googleapis.com/books/v1/volumes', ['q'=>'isbn:9784101010014']); echo 'status='.\$r->status(); echo 'failed='.var_export(\$r->failed(),true); echo 'totalItems='.var_export(\$r->json('totalItems'),true); echo 'title='.var_export(\$r->json('items.0.volumeInfo.title'),true);"
status=429
failed=true
totalItems=NULL
title=NULL
```

3. コントローラ経由（tinker、実在13桁ISBN）：
```
$ ./vendor/bin/sail artisan tinker --execute="\$c = app(App\Http\Controllers\BookController::class); \$resp = \$c->searchByIsbn('9784101010014'); echo 'HTTP='.\$resp->getStatusCode(); echo 'BODY='.\$resp->getContent();"
HTTP=502
BODY={"message":"書籍情報の取得に失敗しました"}
```
（`書...` のデコード）
```
$ ./vendor/bin/sail artisan tinker --execute="echo json_decode('\"書籍情報の取得に失敗しました\"');"
書籍情報の取得に失敗しました
```
→ 実 API が 429 を返す（`$response->failed()` が true）ため、コントローラは §4 の通信失敗分岐に入り HTTP 502 ＋ `{"message":"書籍情報の取得に失敗しました"}` を返した。実データ（title 等）は返っていない。

4. コントローラ経由（tinker、12桁＝桁不正）：
```
$ ./vendor/bin/sail artisan tinker --execute="\$c = app(App\Http\Controllers\BookController::class); \$resp = \$c->searchByIsbn('978410101001'); echo 'HTTP='.\$resp->getStatusCode(); echo 'BODY='.\$resp->getContent();"
HTTP=422
BODY={"message":"ISBNは13桁の数字で入力してください"}
```
（デコード：ISBNは13桁の数字で入力してください）
→ 外部通信前のコントローラ冒頭の正規表現チェックで 422 が返った。

---

## 未確認・保留
- 成功系（HTTP 200・実データで title/author/description/image_url/published_date が埋まる）の実疎通：実 API が keyless quota 超過で HTTP 429 を返すため、実データでの200応答を採取できなかった。②のコード上は 200 で該当5キーを組み立てる分岐が存在するが、実データでの200到達は本走行では未確認。
- 該当0件（totalItems=0 → 404）の実疎通：同じく 429 で `failed()` が先に true になるため、totalItems=0 経路（404）を実データで踏めなかった。テスト（`tests/Feature/IsbnSearchTest.php` の Http::fake モック）側でのみ確認される範囲。
- ブラウザ実機での fetch → 各フィールド自動代入の目視確認は未実施（本走行はコード引用と tinker/curl の実疎通に限定）。
- `docs/00_正本/片倉_菖さん_新模擬案件_Bookshelf_要件シート (1).xlsx`：バイナリのため grep で内容を確認できていない。シート10 R25・シート8 R17 等の原文は未確認。
- `docs/00_正本/Bladeモック参照.md` の ISBN検索記述有無は本ファイルでは grep 出力を貼っておらず未提示。

## worktree情報
- ブランチ名：fix/book-search-and-isbn-ui
- 本体（main）へのマージ：未（本ファイルは検証証拠の採取のみ。実装コードは一切変更していない）

## 判定はしない
本ファイルは YES/NO・PASS/FAIL の判定を含まない。判定は片倉／チャット側がこの証拠を見て行う。
