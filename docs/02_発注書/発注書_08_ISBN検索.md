status: draft

# 発注書 08 — ISBN検索

**対象走行**: 走行⑧（第5週）
**出典**: `docs/01_機能仕様書/books.md`（★応用追記分。要件シート シート3 R31・シート5 R20/R26・シート7 R31・シート8 R17・シート9 R6・シート10 R24〜25・シート11 R14・シート12 R25/R26 を統合）
**対の検品表**: `docs/03_検品表/08_ISBN検索.md`
**前提**: 走行①〜⑥が合格・確定済み。advancedブランチのBladeが移入済みで、`books/create.blade.php`・`books/edit.blade.php`にISBN検索フォームと自動入力用JavaScriptが、`books/_form.blade.php`にISBN・出版日の必須マーク「*」削除と出版日初期値の`format('Y-m-d')`化が、`books/show.blade.php`・`favorites/index.blade.php`に未登録時「未登録」表示が実装済みの状態から着手する。

---

## 0. スコープ

### やること

1. `GET /books/isbn/{isbn}` エンドポイントの新設（Google Books APIを叩いてフォーム自動入力用JSONを返す）
2. `books`テーブルの`isbn`・`published_date`カラムをnullableに変更するmigration
3. 書籍登録・編集（`StoreBookRequest`・`UpdateBookRequest`）のバリデーション改修（isbn・published_dateをnullable化しつつ、値がある場合の桁数・一意性・日付形式チェックは維持）

### なぜ2・3をこの発注書に含めるか（設計判断）

要件シート シート8 R17とシート11 R14は、ISBN検索の追加とISBN/出版日のnullable化を**同一の変更エントリ**として記載しており、両方とも書籍登録・編集フォーム（`books/_form.blade.php`）に関わる変更である。検索・フィルタ・ソート（発注書07）は一覧表示のみを対象としフォームに触れないため対象外とし、この発注書08にまとめて含める。

### やらないこと

1. 検索・フィルタ・ソート（発注書07で対応済み）。
2. シート10 R24前段の「編集画面の認可（HTTPメソッド別403確認）」「未認証時の挙動」の**再テスト**。これは走行②で実装済みの認可ロジックそのものへの変更を伴わない、走行⑭（応用テスト）側のテスト精度向上の話であり、この発注書での実装対象ではない。
3. 自動テストコード（PHPUnit）の作成。走行⑭で一括して書く。

---

## 1. ルーティング（実装順序に注意）

`routes/web.php`に以下を追加する。

```php
Route::get('/books/isbn/{isbn}', [BookController::class, 'searchByIsbn'])
    ->middleware('auth')
    ->name('books.searchByIsbn');
```

**このルートは`Route::get('/books/{book}', ...)`より前に定義すること。** 後に定義すると`/books/isbn/9784...`へのアクセスが`{book}`パターンに先に一致してしまい、`isbn`という文字列をBookモデルの主キーとして解決しようとして404または想定外の例外になる。既存の`/books/create`と同様の「静的セグメントを動的パラメータより先に置く」原則に従う。

認可はシート7 R31 C11の通り「未認証: /loginへリダイレクト（書籍登録・編集画面内の補助機能のため、親画面と同じ認可）」。JSON APIとして401を返す一般的な設計ではなく、`auth`ミドルウェアの標準リダイレクト挙動をそのまま使うこと（要件シートの明記通り）。

---

## 2. リクエスト・バリデーション

`{isbn}`は13桁の数字文字列であることをコントローラー内で検証する（この1項目だけのためにFormRequestを作らず、コントローラー冒頭で正規表現チェックする）。桁数不正時のエラー応答は、キーを`message`とし、HTTPステータス422で返す。

```php
if (! preg_match('/^[0-9]{13}$/', $isbn)) {
    return response()->json(['message' => 'ISBNは13桁の数字で入力してください'], 422);
}
```

---

## 3. Google Books API連携

- `Illuminate\Support\Facades\Http`を使用すること（`file_get_contents`・cURL直叩き・Guzzleの直接インスタンス化は禁止）。テストが`Http::fake()`でこの呼び出しを差し替える前提のため、Httpファサード経由でない実装はテストが成立しない。
- エンドポイント: `GET https://www.googleapis.com/books/v1/volumes?q=isbn:{isbn}`（Google Books APIの公開エンドポイント。要件シートに個別のURL指定はないが、ISBN検索を行う一般的な方式として採用する。APIキーは設定しない — 採点環境での実行は全てテストの`Http::fake()`配下で行われる前提のため、実キーの有無は動作に影響しない）。
- タイムアウトを明示的に設定すること（例: `Http::timeout(5)`）。

```php
$response = Http::timeout(5)->get('https://www.googleapis.com/books/v1/volumes', [
    'q' => "isbn:{$isbn}",
]);
```

---

## 4. レスポンス分岐と自動入力用JSONの組み立て

エラー系レスポンスのキーはすべて`message`に統一する（Web画面向けエラー応答・API向けエラー応答を通じてアプリ全体で応答キーを`message`に揃える方針に従う）。

| 状況 | ステータス | レスポンスボディ |
|---|---|---|
| 通信自体が失敗（接続エラー・タイムアウト・5xx） | 502 | `{"message": "書籍情報の取得に失敗しました"}` |
| 通信成功・`totalItems`が0（該当書籍なし） | 404 | `{"message": "該当する書籍が見つかりませんでした"}` |
| 通信成功・`totalItems`が1以上 | 200 | `{"title": ..., "author": ..., "description": ..., "image_url": ..., "published_date": ...}` |

実装方針:

```php
try {
    $response = Http::timeout(5)->get(...);
} catch (\Illuminate\Http\Client\ConnectionException $e) {
    return response()->json(['message' => '書籍情報の取得に失敗しました'], 502);
}

if ($response->failed()) {
    return response()->json(['message' => '書籍情報の取得に失敗しました'], 502);
}

$totalItems = $response->json('totalItems', 0);
if ($totalItems === 0) {
    return response()->json(['message' => '該当する書籍が見つかりませんでした'], 404);
}

$volumeInfo = $response->json('items.0.volumeInfo', []);

return response()->json([
    'title'          => $volumeInfo['title'] ?? '',
    'author'         => implode('、', $volumeInfo['authors'] ?? []),
    'description'    => $volumeInfo['description'] ?? '',
    'image_url'      => $volumeInfo['imageLinks']['thumbnail'] ?? '',
    'published_date' => $volumeInfo['publishedDate'] ?? '',
]);
```

補足（設計判断・要件シートに個別指定がないため以下の方針で確定する）:

- `authors`は配列で返ってくる。複数著者は`、`（読点）で連結して1つの文字列にする。
- `publishedDate`はGoogle Books API側の都合で`"2012"`のような年のみ・`"2012-06"`のような年月のみの場合がある。この発注書ではフォーマット補正を行わず、取得した文字列をそのまま返す。理由: この値はフォーム自動入力の下書きであり、最終的な保存は書籍登録・編集のバリデーション（§5）を通るため、この時点での厳密な補正は不要と判断する。
- 成功時レスポンスキー名（`title`・`author`・`description`・`image_url`・`published_date`）はシート10 R25の完了条件に完全一致させること。キー名を変えるとBlade側の自動入力JavaScript（提供済み・変更不可）が値を拾えなくなる。
- エラー時レスポンスキーは`message`とする。成功時のフォーム自動入力用キー（`title`等）とは用途が異なり、Blade側の自動入力JavaScriptは成功時のキーのみを参照するため、エラー時キーを`message`にしても自動入力挙動には影響しない。

---

## 5. books テーブルのnullable化とバリデーション改修

### migration

新規migrationで以下2カラムを変更する。

```php
Schema::table('books', function (Blueprint $table) {
    $table->string('isbn', 13)->nullable()->change();
    $table->date('published_date')->nullable()->change();
});
```

`doctrine/dbal`パッケージが必要な場合はインストールすること（`->change()`を使うカラム変更に必要）。

### StoreBookRequest / UpdateBookRequest の改修

```php
'isbn' => ['nullable', 'string', 'regex:/^[0-9]{13}$/', 'unique:books,isbn' /* 編集時は自身のレコードを除外 */],
'published_date' => ['nullable', 'date'],
```

エラーメッセージ:

- ISBN: 「ISBNは13桁の数字で入力してください（入力がある場合のみ）」「このISBNは既に登録されています」
- 出版日: 「出版日は正しい日付形式で入力してください（入力がある場合のみ）」
- 「ISBNを入力してください」「出版日を入力してください」（必須チェックのメッセージ）はnullable化に伴い削除すること。

### 表示側との整合

- `books/show.blade.php`・`favorites/index.blade.php`の「未登録」表示は既にBlade側に実装済み。バックエンドは`isbn`・`published_date`が`null`のまま保存できることだけ保証すればよい。
- 出版日を持つ書籍の詳細画面表示がY-m-d形式・Carbonキャストで機能することを確認する（`Book`モデルの`$casts`に`published_date`が既に`date`として登録済みか確認し、未登録なら追加する）。

---

## 6. 実装上の注意（CC向け）

- ISBN検索（§1〜4）とnullable化（§5）は同じ`_form.blade.php`を経由するが、実装自体は独立している。どちらか一方が未完成でももう一方は単体でテスト可能な設計にすること。
- `unique:books,isbn`は値が`null`の行同士では一意性違反にならない（Laravelの標準挙動）。追加の除外コードは不要。
- 編集時の一意性チェックで自身のレコードを除外する実装（`unique:books,isbn,{book},id`）は基本機能から変更しない。

---

## 7. 禁止事項

1. `resources/`配下のBlade/CSS/JSを1文字も変更しない。
2. 本発注書に明記されていないロジックを独自に追加しない（発注書の指定と異なる実装をする場合は、仮決めせず`QUESTIONS.md`に記録して停止する）。
3. `migrate:fresh`を要するmigration変更は§5のisbn/published_date変更のみに限定する。他のカラム・テーブルへの変更は行わない。
4. `main`ブランチへ直接コミットしない。

---

## 8. 未定義事項に当たったとき

`QUESTIONS.md`へ以下4欄を記入し、該当作業を止めて次の作業に移る。

- 発生日 ／ 対象発注書（`02_発注書/08_ISBN検索.md`） ／ 止まった箇所 ／ CCの解釈候補（2案以上）

---

## 9. 完了時の報告

- 作業ブランチ名と最終コミットID
- `sail artisan route:list --path=books` の出力（`books.searchByIsbn`が`books.show`より前の順に並んでいることの確認込み）
- `sail bin pint --test` の出力
- `sail artisan migrate:fresh --seed` の出力（isbn/published_dateのカラム変更後もシーディングがエラーなく完了すること）
- `QUESTIONS.md` に追記した行の有無
