status: draft

# 発注書 10 — Sanctum APIトークン認証

**対象走行**: 走行⑩（第6週）
**出典**: 要件シート.xlsx シート3 R32（★Sanctum APIトークン認証）／シート7 R56（★Sanctum認証を要求する書き込み系API）／シート8 R18（★公開API 書籍登録・編集のバリデーション見直し）／シート10 R27（★Sanctum認証 テスト観点）／シート12（personal_access_tokensテーブル）／シート13 AP06（★Sanctum APIトークン認証の詳細仕様）
**対の検品表**: `docs/03_検品表/10_Sanctum.md`
**前提**: 走行①〜⑨が合格・確定済み。走行⑤で実装済みの公開API（`Api\V1\BookController`、認証なしCRUD）が対象。Blade側の変更は一切ない（本走行はAPIのみ）。

---

## 0. スコープ

### やること

1. `composer require laravel/sanctum` の導入。
2. Sanctumのマイグレーション発行・実行（`personal_access_tokens`テーブル作成）。
3. `User` モデルに `Laravel\Sanctum\HasApiTokens` トレイトを追加。
4. `routes/api.php` の `POST /api/v1/books`・`PUT /api/v1/books/{book}`・`DELETE /api/v1/books/{book}` の3ルートに `auth:sanctum` ミドルウェアを適用。
5. `Api\V1\BookController` の `store`・`update`・`destroy` メソッドの改修（`user_id` のリクエストボディ受領を廃止し `Auth::id()` から取得する方式へ変更。`update`・`destroy` に `BookPolicy` の `update`／`delete` を適用）。
6. 該当するFormRequestのバリデーションルールから `user_id` を除外する。
7. 401・403エラー時の日本語JSONレスポンスの実装（Laravel標準の英語文言を露出させない）。

### やらないこと

1. `GET /api/v1/books`・`GET /api/v1/books/{book}` の2読み取り系エンドポイントへの認証追加。
2. トークン発行用のAPIエンドポイントの新設（本アプリにその機能はない。§3参照）。
3. Web版（`routes/web.php`）のコントローラー・ルートの変更。
4. SanctumのSPA向けCookie認証（`EnsureFrontendRequestsAreStateful`）の設定。本アプリはBearerトークンのみを使用し、SPA連携は行わない。
5. 自動テストコード（PHPUnit）の作成。走行⑭で一括して書く。

---

## 1. Sanctumの導入手順

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag="sanctum-migrations"
sail artisan migrate
```

`User` モデル（`app/Models/User.php`）に以下を追加する。

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens; // 既存のトレイトに追加する
}
```

---

## 2. ルーティング（`routes/api.php`）

```php
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{book}', [BookController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{book}', [BookController::class, 'update']);
    Route::delete('/books/{book}', [BookController::class, 'destroy']);
});
```

適用対象は上記3本のみ。読み取り系2本（`index`・`show`）は既存のまま `auth:sanctum` を付けない。

---

## 3. トークンの入手経路（新設しない）

本アプリにトークン発行用のAPIエンドポイントは存在しない。以下の2経路のみを使う。

- 自動テスト: `Laravel\Sanctum\Sanctum::actingAs($user)` を使用する。
- 手動確認・採点者向け: `sail artisan tinker` から `$user->createToken('manual')->plainTextToken` で発行する。この手順は第7週のREADME作成タスクで「APIエンドポイント一覧」セクションに明記するが、本走行の完了報告にも手順そのものを含めること。
- トークンに有効期限は設けない（`personal_access_tokens.expires_at` はNULLのまま）。`abilities`（権限スコープ）は使用しない。

---

## 4. 認可・`user_id` の扱い

- `store`（新規登録）: 認証のみを要求する。登録前のリソースに所有者が存在しないため、Policyによる認可は行わない。登録者は `Auth::id()` から取得し、リクエストボディの `user_id` は無視する（存在しても422にしない）。
- `update`・`destroy`: 認証に加えて `BookPolicy` の `update`／`delete` を適用する。判定内容はWeb版と同一（`Auth::id() === $book->user_id`）。API専用のPolicyは新設しない。
- 論理削除済みの書籍を対象とした `update`・`destroy` は、SoftDeletesの標準除外により404となる（基本段階と同じ挙動を維持する。追加実装は不要）。
- FormRequestのバリデーション対象から `user_id` を除外する。

---

## 5. エラーレスポンス

Laravel標準の英語文言を返さず、以下の日本語JSONに統一する。

- 401（未認証）: `{"message": "認証が必要です。"}`
- 403（認可失敗）: `{"message": "この操作を実行する権限がありません。"}`
- 422（バリデーションエラー）: 基本段階と同一形式・同一文言を維持する（`{"message": "入力内容に誤りがあります。", "errors": {...}}`）。
- 404（対象なし）: 基本段階と同一（`{"message": "指定された書籍が見つかりません。"}`）。

`routes/api.php` 側は未認証時にログイン画面へリダイレクトせず、JSONで401を返すこと。実機での確認を必ず行う。

---

## 6. 禁止事項

1. `resources/views/` 配下のBlade/CSS/JSを1文字も変更しない。
2. 読み取り系2エンドポイント（AP01・AP02）に認証を追加しない。
3. トークン発行用のAPIエンドポイントを新設しない。
4. 本発注書に明記されていないロジックを独自に追加しない。
5. `main` ブランチへ直接コミットしない。

---

## 7. 未定義事項に当たったとき

`QUESTIONS.md` へ以下4欄を記入し、該当作業を止めて次の作業に移る。

- 発生日 ／ 対象発注書（`02_発注書/10_Sanctum.md`） ／ 止まった箇所 ／ CCの解釈候補（2案以上）

---

## 8. 完了時の報告

- 作業ブランチ名と最終コミットID
- `sail artisan route:list --path=api` の出力（3ルートに `auth:sanctum` ミドルウェアが付与されていることが分かる出力）
- `sail bin pint --test` の出力
- `sail artisan tinker` で発行したトークンを使った手動確認の実行結果（200/201/401/403の再現結果）
- `QUESTIONS.md` に追記した行の有無
