status: draft

# 検品表 08 — ISBN検索

**対象発注書**: `docs/02_発注書/08_ISBN検索.md`
**証拠ファイル**: `docs/04_検品結果/証拠_⑦_⑧.md`（CCが本表の行に対応させて生の実行結果のみを記入する。判定語は書かない）
**出力先**: `docs/04_検品結果/08_ISBN検索_結果.md`（証拠ファイルと本表を突き合わせ、このチャットがYES/NO判定を行って出力する）

## 証拠収集の実施条件

- 実装を行ったCCセッションとは別の、文脈ゼロの新規セッションで証拠を収集する。
- 渡してよいのは①`CLAUDE.md`（自動読込） ②本検品表と対の発注書 ③実装済みコードの3点のみ。
- CCは各行に対応する生の実行結果（コマンド出力・DB確認結果・レスポンスJSONの内容・実機で見た文字列）のみを記入する。判定・評価語は一切書かない。
- 判定（YES/NO）はこのチャットが証拠ファイルと本表を突き合わせて行う。1行でもNOがあれば走行⑧は不合格。

## 証拠収集前に一度だけ実行するコマンド（出力を証拠ファイルに貼る）

```bash
git branch --show-current
git log --oneline -5
sail artisan migrate:fresh --seed
sail bin pint --test
sail artisan route:list --path=books
```

`route:list`の出力で`books.searchByIsbn`（`GET /books/isbn/{isbn}`）が`books.show`（`GET /books/{book}`）より前の行に来ていることを目視で確認できる状態にすること（B-6の証拠として使う）。

---

## A. ISBN検索・正常系

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| A-1 | 実在する13桁ISBN（例: `9784873115658`「リーダブルコード」）を書籍登録画面のISBN欄に入力し「ISBN検索」を押すと、タイトル・著者・出版日・説明・画像が自動入力される | 実機 | 差し戻し |
| A-2 | 上記A-1のレスポンスJSONが`title`・`author`・`description`・`image_url`・`published_date`・`published_date_padded`の6キーを持つ | ブラウザの開発者ツールでレスポンスボディを確認 | 差し戻し |
| A-3 | 著者が複数いる書籍で、`author`が「、」区切りの1つの文字列として返る | 実機（複数著者の書籍で確認） | 差し戻し |
| A-4 | `publishedDate`が年のみ（例:`"2012"`）の場合、`published_date`が`"2012-01-01"`、`published_date_padded`が`true`で返る | ソース確認（`BookController::searchByIsbn`の正規表現分岐に`$rawDate = "2012"`を代入した場合の戻り値を手動でトレースする） | 差し戻し |
| A-5 | `publishedDate`が年月のみ（例:`"2012-06"`）の場合、`published_date`が`"2012-06-01"`、`published_date_padded`が`true`で返る | ソース確認（同上、`$rawDate = "2012-06"`でトレース） | 差し戻し |
| A-6 | `publishedDate`が完全な日付（例:`"2012-06-15"`）の場合、`published_date`が`"2012-06-15"`のまま、`published_date_padded`が`false`で返る | ソース確認（同上、`$rawDate = "2012-06-15"`でトレース） | 差し戻し |
| A-7 | `publishedDate`キー自体が存在しない、または上記いずれの形式にも一致しない場合、`published_date`が空文字、`published_date_padded`が`false`で返る | ソース確認（同上、`$rawDate = ''`でトレース） | 差し戻し |
| A-8 | `published_date_padded`が`true`のレスポンスを受け取った場合、フロントJSが出版日欄に値を代入し、「出版日は年月までの情報のため、日付は仮の値（1日）を自動設定しました。正しい日付が分かる場合は修正してください。」というメッセージを表示する | ソース確認（`create.blade.php`・`edit.blade.php`のJS分岐） | 差し戻し |
| A-9 | `published_date_padded`が`false`かつ`published_date`に値がある場合、フロントJSが出版日欄に値を代入し、「書籍情報を自動入力しました」という通常メッセージを表示する | ソース確認（同上） | 差し戻し |
| A-10 | フロントJSから、出版日欄への値代入を完全なYYYY-MM-DD形式のみに限定していた正規表現ガード（`/^\d{4}-\d{2}-\d{2}$/`）が撤去されている | ソース確認（`create.blade.php`・`edit.blade.php`を目視し、出版日欄の代入が無条件の`setValue`呼び出しになっていること） | 差し戻し |

## B. エラー系・実装方式

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| B-1 | 13桁でないISBN（例: `123`）を入力すると422で`{"message":"ISBNは13桁の数字で入力してください"}`が返る | 実機 | 差し戻し |
| B-2 | 実在しない13桁ISBN（例: `9999999999999`）を入力すると404で`{"message":"該当する書籍が見つかりませんでした"}`が返る | 実機（実際にGoogle Books APIへ通信させて確認） | 差し戻し |
| B-3 | 通信エラー時（接続失敗・5xx）に502で`{"message":"書籍情報の取得に失敗しました"}`が返る実装になっている | ソース確認（`try/catch`で`ConnectionException`を捕捉し502を返す分岐、および`$response->failed()`時に502を返す分岐の両方が存在すること）。実機での再現確認は不要（走行⑭の`Http::fake()`自動テストで動作確認する） | 差し戻し |
| B-4 | `Illuminate\Support\Facades\Http`経由で外部通信している（`curl_exec`・`file_get_contents`・Guzzleクライアントの直接インスタンス化がない） | ソース確認 | 差し戻し |
| B-5 | 未ログインで`/books/isbn/{isbn}`にアクセスすると`/login`へリダイレクトされる（401 JSONではない） | 実機（ログアウト状態で直接URLアクセス） | 差し戻し |
| B-6 | `routes/web.php`で`books.searchByIsbn`が`books.show`より前に定義されている | `route:list`の出力での行順確認 | 差し戻し |
| B-7 | ISBN検索のエラー応答3種（422・404・502）のキーがすべて`message`である（`error`キーが残っていない） | ソース確認（`grep -n "'error'" app/Http/Controllers/BookController.php` が空であること） | 差し戻し |

## C. booksテーブルのnullable化

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| C-1 | ISBNを空欄のまま書籍の新規登録ができる | 実機 | 差し戻し |
| C-2 | 出版日を空欄のまま書籍の新規登録ができる | 実機 | 差し戻し |
| C-3 | ISBN・出版日を空欄のまま書籍の編集（更新）ができる | 実機（既存書籍を編集しISBN・出版日を消して保存） | 差し戻し |
| C-4 | ISBN・出版日が未入力の書籍について、書籍詳細画面で「未登録」と表示される | 実機 | 差し戻し |
| C-5 | ISBN・出版日が未入力の書籍について、お気に入り一覧画面で「未登録」と表示される | 実機 | 差し戻し |
| C-6 | ISBNに値を入れた場合は13桁チェックが従来通り働く（13桁でない値でエラーになる） | 実機 | 差し戻し |
| C-7 | ISBNに値を入れた場合は一意性チェックが従来通り働く（既存と重複する値でエラーになる） | 実機 | 差し戻し |
| C-8 | 出版日を持つ書籍の詳細画面がY-m-d形式で表示される（Carbonへのキャストが有効） | 実機 | 差し戻し |
| C-9 | 「ISBNを入力してください」「出版日を入力してください」という必須チェックのエラーメッセージが表示されなくなっている | 実機（空欄のまま送信してもこれらの文言が出ないこと） | 差し戻し |

## D. コード品質・スコープ厳守

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| D-1 | `sail bin pint --test` が「No fixable issues were found」 | コマンド出力 | 差し戻し |
| D-2 | ISBN検索・nullable化以外の書籍CRUD機能（登録・編集・削除・復元・認可）の既存挙動に変化がない | 実機（走行②時点の主要操作を一通り再確認） | 差し戻し |
| D-3 | 発注書に明記されていない独自ロジックが追加されていない | ソース確認・実機 | 差し戻し |
| D-4 | migrationの変更が`isbn`・`published_date`のnullable化のみに限定されている | `git diff main -- database/migrations/` | 差し戻し |
| D-5 | `resources/`配下の変更が、発注書08 §5で明示した`create.blade.php`・`edit.blade.php`のISBN検索自動入力ロジック（出版日欄への値代入・メッセージ分岐）に限定されている（それ以外の差分がない） | `git diff main -- resources/` の出力を目視し、差分が§5で明示した範囲のみであることを確認 | 差し戻し |
| D-6 | `QUESTIONS.md` に走行⑧由来の未解消行がない、または全て記録されている | `QUESTIONS.md` 確認 | 差し戻し |
