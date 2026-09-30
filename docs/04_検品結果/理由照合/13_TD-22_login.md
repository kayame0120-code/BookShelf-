# 13_TD-22_login login画面

検索パターン: `login\.blade|会員登録ボタン|デザインUI|デザイン優先|モック|提供Blade|frozen`

```
CLAUDE.md-9-
CLAUDE.md-10-仕様の食い違いが発生した場合、以下の優先順位で判断する。**上位が常に勝つ。**
CLAUDE.md-11-
CLAUDE.md:12:1. Bladeモック実体（`docs/00_正本/Bladeモック参照.md` 参照。basic/advancedブランチ、frozen）
CLAUDE.md-13-2. 要件シート.xlsx（`docs/00_正本/要件シート.xlsx`）
CLAUDE.md-14-3. 01_機能仕様書/ 配下のmd
CLAUDE.md-15-4. 02_発注書/ 配下のmd
--
docs/00_正本/Bladeモック参照.md:1:# Bladeモック参照
docs/00_正本/Bladeモック参照.md-2-
docs/00_正本/Bladeモック参照.md:3:このファイルはBladeモック本体を置かない。参照情報のみを1枚にまとめたもの。
docs/00_正本/Bladeモック参照.md:4:モック本体はGitHubリポジトリ側にある。
docs/00_正本/Bladeモック参照.md-5-
docs/00_正本/Bladeモック参照.md-6-## リポジトリ
docs/00_正本/Bladeモック参照.md-7-
--
docs/00_正本/Bladeモック参照.md-13-
docs/00_正本/Bladeモック参照.md-14-| ブランチ | 用途 | 状態 |
docs/00_正本/Bladeモック参照.md-15-|---|---|---|
docs/00_正本/Bladeモック参照.md:16:| `basic` | 基本機能の正本 | frozen（改変禁止） |
docs/00_正本/Bladeモック参照.md:17:| `advanced` | 応用機能の正本 | frozen（改変禁止） |
docs/00_正本/Bladeモック参照.md-18-
docs/00_正本/Bladeモック参照.md:19:## frozen宣言
docs/00_正本/Bladeモック参照.md-20-
docs/00_正本/Bladeモック参照.md:21:両ブランチとも改変禁止。要件シート・機能仕様書・発注書の記述とBladeモックが食い違った場合、**Bladeモックが優先する**（`CLAUDE.md` 0章の正本優先順位1位）。
docs/00_正本/Bladeモック参照.md-22-
docs/00_正本/Bladeモック参照.md-23-## Cloneコマンド
docs/00_正本/Bladeモック参照.md-24-
--
docs/01_機能仕様書/auth.md-3-| 項目 | 内容 |
docs/01_機能仕様書/auth.md-4-|---|---|
docs/01_機能仕様書/auth.md-5-| 対象発注書 | 01_土台認証（migration・Model・リレーション・Fortify認証） |
docs/01_機能仕様書/auth.md:6:| 正本 | 要件シート.xlsx シート3/4/5/7/8/9/10/11/12 ＋ Bladeモック `basic`（frozen） |
docs/01_機能仕様書/auth.md-7-| 適用範囲 | 基本要件のみ。★応用（Sanctum・reading_plans・通知）は第5〜6週に別途 |
docs/01_機能仕様書/auth.md-8-| 完成条件 | 本書だけで発注書01が書ける（他ファイル参照不要） |
docs/01_機能仕様書/auth.md-9-| 版 | v1（2026-09-01・面談②のSoftDelete確定を反映済み） |
--
docs/01_機能仕様書/auth.md-88-
docs/01_機能仕様書/auth.md-89----
docs/01_機能仕様書/auth.md-90-
docs/01_機能仕様書/auth.md:91:## 3. 画面契約（Bladeモック実測・改変禁止）
docs/01_機能仕様書/auth.md-92-
docs/01_機能仕様書/auth.md:93:### PG12 ログイン（`auth/login.blade.php`）
docs/01_機能仕様書/auth.md-94-
docs/01_機能仕様書/auth.md-95-| 要素 | 実測仕様 |
docs/01_機能仕様書/auth.md-96-|---|---|
--
docs/01_機能仕様書/auth.md-336-| ログイン画面の「会員登録」ボタン | `register` 画面 | — | — | — |
docs/01_機能仕様書/auth.md-337-| 「ログアウト」ボタン | `books.index`（Fortify標準） | **なし** | — | 未認証時は `auth` ミドルウェアで到達不能（ボタン自体が表示されない） |
docs/01_機能仕様書/auth.md-338-
docs/01_機能仕様書/auth.md:339:認証系はいずれも独自 flash を持たない。`auth/login.blade.php` と `auth/register.blade.php` に `session()` を読むコードが存在しないため、設定しても描画されない。
docs/01_機能仕様書/auth.md-340-
docs/01_機能仕様書/auth.md-341-### ミドルウェアによるアクセス制御の一覧
docs/01_機能仕様書/auth.md-342-
--
docs/01_機能仕様書/auth.md-354-- Fortify のルートを自分で `routes/web.php` に書き足さないこと。二重登録になる。
docs/01_機能仕様書/auth.md-355-- `RouteServiceProvider::HOME` の変更を忘れると、ログイン済みで `/login` を開いたときのリダイレクト先が Laravel 標準の既定値 `/home`（本アプリには存在しないURL）になり、404 になる。
docs/01_機能仕様書/auth.md-356-- migration は §6 の順番で作成する。順番を誤ると FK 作成時に失敗する。
docs/01_機能仕様書/auth.md:357:- Blade は改変不要（認証画面はモックの契約どおり）。
docs/01_機能仕様書/auth.md-358-
docs/01_機能仕様書/auth.md-359----
docs/01_機能仕様書/auth.md-360-
--
docs/01_機能仕様書/auth.md-389-
docs/01_機能仕様書/auth.md-390-## 11. テスト観点（要件シート シート10）
docs/01_機能仕様書/auth.md-391-
docs/01_機能仕様書/auth.md:392:**全体要件（共通）**: 全テスト通過。`sail artisan test --coverage` で基本機能のみ60%超を目標（応用込みでは80%以上）。外部APIテストは `Http::fake()` でモック化する（応用のISBN検索で使用）。
docs/01_機能仕様書/auth.md-393-
docs/01_機能仕様書/auth.md-394-### 機能テスト `tests/Feature/ScreenAccessTest.php`
docs/01_機能仕様書/auth.md-395-
--
docs/01_機能仕様書/books.md-3-| 項目 | 内容 |
docs/01_機能仕様書/books.md-4-|---|---|
docs/01_機能仕様書/books.md-5-| 対象発注書 | 02_書籍CRUD ／ 07_検索ソート ／ 08_ISBN検索（一部が 01_土台認証・06_基本テスト・14_応用テストに波及） |
docs/01_機能仕様書/books.md:6:| 正本 | 要件シート.xlsx シート5/7/8/9/10/11/12 ＋ Bladeモック `basic` / `advanced`（frozen） |
docs/01_機能仕様書/books.md-7-| 適用範囲 | 基本要件（書籍CRUD・論理削除・復元）と ★応用要件（検索/フィルタ/ソート・ISBN検索・ISBN/出版日のnullable化） |
docs/01_機能仕様書/books.md-8-| 完成条件 | 本書だけで発注書02・07・08が書ける（他ファイル参照不要） |
docs/01_機能仕様書/books.md-9-
--
docs/01_機能仕様書/books.md-170-
docs/01_機能仕様書/books.md-171----
docs/01_機能仕様書/books.md-172-
docs/01_機能仕様書/books.md:173:## 3. 画面契約（Bladeモック実測・改変禁止部分）
docs/01_機能仕様書/books.md-174-
docs/01_機能仕様書/books.md-175-| 画面ID | Bladeファイル | 渡す変数 | Blade が要求する属性・リレーション | flashスロット |
docs/01_機能仕様書/books.md-176-|---|---|---|---|---|
--
docs/01_機能仕様書/books.md-405-
docs/01_機能仕様書/books.md-406-### 10-1. `books/show.blade.php` への追記（Blade改変が必要な唯一の箇所）
docs/01_機能仕様書/books.md-407-
docs/01_機能仕様書/books.md:408:frozen 宣言のあるモックだが、**削除済み状態の表示は元のモックに定義自体が存在しない**（404用Bladeも存在しない）。既存の記述を書き換えるのではなく、以下4点を追記する。既存のマークアップ・クラス名・レイアウトは変更しないこと。
docs/01_機能仕様書/books.md-409-
docs/01_機能仕様書/books.md-410-1. `session('success')` ブロックの直後に、`@if($book->trashed())` で囲んだバナー「この本は削除されました」を追加する。
docs/01_機能仕様書/books.md-411-2. お気に入りボタンの `@auth` ブロック全体を `@if(! $book->trashed())` で囲む。
--
docs/01_機能仕様書/books.md-536-
docs/01_機能仕様書/books.md-537-| # | 検証観点 |
docs/01_機能仕様書/books.md-538-|---|---|
docs/01_機能仕様書/books.md:539:| F-I1 | 正常系 — `Http::fake()` で Google Books API の成功応答をモックし、`GET /books/isbn/{isbn}` が200で `title` / `author` / `description` / `image_url` / `published_date` の各キーを持つJSONを返す |
docs/01_機能仕様書/books.md-540-| F-I2 | ISBN形式不正 — 13桁でない値を渡すと422で `{"error": "ISBNは13桁の数字で入力してください"}` が返る |
docs/01_機能仕様書/books.md:541:| F-I3 | 該当書籍なし — Google Books API が該当0件を返す応答をモックすると404で `{"error": "該当する書籍が見つかりませんでした"}` が返る |
docs/01_機能仕様書/books.md:542:| F-I4 | 通信エラー — Google Books API が接続失敗または5xxを返す応答をモックすると502で `{"error": "書籍情報の取得に失敗しました"}` が返る |
docs/01_機能仕様書/books.md-543-| F-I5 | エラー応答の形状統一 — 422・404・502のいずれも `error` キーを持つJSONであり、フロント側のJSが `data.error` で分岐できる |
docs/01_機能仕様書/books.md-544-| F-I6 | 認可 — 未ログインで `GET /books/isbn/{isbn}` にアクセスすると `/login` へリダイレクトされる |
docs/01_機能仕様書/books.md-545-| F-I7 | 外部APIへの実通信がないこと — 全テストが `Http::fake()` 配下で実行され、実際の Google Books API を呼び出していない |
--
docs/01_機能仕様書/favorites.md-3-| 項目 | 内容 |
docs/01_機能仕様書/favorites.md-4-|---|---|
docs/01_機能仕様書/favorites.md-5-| 対象発注書 | 03_レビュー・お気に入り・いいね（お気に入りの部分） |
docs/01_機能仕様書/favorites.md:6:| 正本 | 要件シート.xlsx シート5/7/9/10/11/12 ＋ Bladeモック `basic`（frozen） |
docs/01_機能仕様書/favorites.md-7-| 適用範囲 | 基本要件のみ |
docs/01_機能仕様書/favorites.md-8-| 完成条件 | 本書だけで発注書03のお気に入り部分が書ける（他ファイル参照不要） |
docs/01_機能仕様書/favorites.md-9-| 版 | v1（2026-09-01・面談②のSoftDelete確定を反映済み） |
--
docs/01_機能仕様書/favorites.md-54-
docs/01_機能仕様書/favorites.md-55----
docs/01_機能仕様書/favorites.md-56-
docs/01_機能仕様書/favorites.md:57:## 3. 画面契約（Bladeモック実測・改変禁止部分）
docs/01_機能仕様書/favorites.md-58-
docs/01_機能仕様書/favorites.md-59-### PG10 お気に入り一覧（`favorites/index.blade.php`）
docs/01_機能仕様書/favorites.md-60-
--
docs/01_機能仕様書/favorites.md-147-
docs/01_機能仕様書/favorites.md-148-## 8. 実装上の注意（CC向け）
docs/01_機能仕様書/favorites.md-149-
docs/01_機能仕様書/favorites.md:150:- 一覧の並び順は `books.created_at` の降順（新しい順）。要件シート・Bladeモックとも指定がないため、書籍一覧（PG01）の「最新順」に揃えている。`latest()` ではなく `latest('books.created_at')` と**テーブル名込みで指定**すること（ピボットとの結合クエリになるため）。
docs/01_機能仕様書/favorites.md:151:- Blade は改変不要。お気に入り機能はモックの契約どおりに実装できる。
docs/01_機能仕様書/favorites.md-152-- `toggle()` は追加・解除のどちらでも例外を投げない。業務エラーの分岐を書く必要はない。
docs/01_機能仕様書/favorites.md-153-
docs/01_機能仕様書/favorites.md-154----
--
docs/01_機能仕様書/genres.md-3-| 項目 | 内容 |
docs/01_機能仕様書/genres.md-4-|---|---|
docs/01_機能仕様書/genres.md-5-| 対象発注書 | 04_ジャンルランキング（ジャンル部分） |
docs/01_機能仕様書/genres.md:6:| 正本 | 要件シート.xlsx シート5/7/8/9/10/11/12 ＋ Bladeモック `basic`（frozen） |
docs/01_機能仕様書/genres.md-7-| 適用範囲 | 基本要件のみ |
docs/01_機能仕様書/genres.md-8-| 完成条件 | 本書だけで発注書04のジャンル部分が書ける（他ファイル参照不要） |
docs/01_機能仕様書/genres.md-9-| 版 | v1（2026-09-01・面談②のSoftDelete確定を反映済み） |
--
docs/01_機能仕様書/genres.md-58-
docs/01_機能仕様書/genres.md-59----
docs/01_機能仕様書/genres.md-60-
docs/01_機能仕様書/genres.md:61:## 3. 画面契約（Bladeモック実測・改変禁止）
docs/01_機能仕様書/genres.md-62-
docs/01_機能仕様書/genres.md-63-| 画面ID | Bladeファイル | 渡す変数 | Blade が要求する属性 | flashスロット |
docs/01_機能仕様書/genres.md-64-|---|---|---|---|---|
--
docs/01_機能仕様書/genres.md-174-
docs/01_機能仕様書/genres.md-175-## 9. 実装上の注意（CC向け）
docs/01_機能仕様書/genres.md-176-
docs/01_機能仕様書/genres.md:177:- `genres/index` の一覧順は `orderBy('id')`（シーダー投入順）。要件シート・Bladeモックとも並び順の指定がないため、最も単純で決定的な順序を採用している。
docs/01_機能仕様書/genres.md-178-- `genres/show` の書籍一覧は `books.created_at` の降順（新しい順）。書籍一覧（PG01）の「最新順」と揃えている。
docs/01_機能仕様書/genres.md-179-- `$genre->books()` は SoftDeletes の影響を受ける。**表示（books_count・一覧）は生存書籍のみ、削除可否判定は `withTrashed()` 付き**という使い分けを取り違えないこと。
docs/01_機能仕様書/genres.md:180:- Blade は改変不要。ジャンル機能はモックの契約どおりに実装できる。
docs/01_機能仕様書/genres.md-181-
docs/01_機能仕様書/genres.md-182----
docs/01_機能仕様書/genres.md-183-
--
docs/01_機能仕様書/notifications.md-3-| 項目 | 内容 |
docs/01_機能仕様書/notifications.md-4-|---|---|
docs/01_機能仕様書/notifications.md-5-| 対象発注書 | 12_通知バッチ |
docs/01_機能仕様書/notifications.md:6:| 正本 | 要件シート.xlsx シート5/7/9/10/12 ＋ Bladeモック `advanced`（frozen） |
docs/01_機能仕様書/notifications.md-7-| 適用範囲 | 応用要件。通知一覧の表示・既読化、読書計画リマインダー通知、お知らせ送信の日次バッチ、notifications テーブル、テスト観点 |
docs/01_機能仕様書/notifications.md-8-| 完成条件 | 本書だけで発注書12が書ける。ただし読書計画の状態モデル・状態更新バッチ（0:00）は reading_plans.md が持つ。本書はお知らせ送信バッチ（7:00）と通知本体を持つ |
docs/01_機能仕様書/notifications.md-9-
--
docs/01_機能仕様書/notifications.md-44-
docs/01_機能仕様書/notifications.md-45----
docs/01_機能仕様書/notifications.md-46-
docs/01_機能仕様書/notifications.md:47:## 2. 画面契約（Bladeモック実測・改変禁止）
docs/01_機能仕様書/notifications.md-48-
docs/01_機能仕様書/notifications.md-49-| 画面ID | Bladeファイル | 渡す変数 | Blade が要求する属性 | flash |
docs/01_機能仕様書/notifications.md-50-|---|---|---|---|---|
--
docs/01_機能仕様書/public_api.md-3-| 項目 | 内容 |
docs/01_機能仕様書/public_api.md-4-|---|---|
docs/01_機能仕様書/public_api.md-5-| 対象発注書 | 05_公開APIシーディング ／ 10_Sanctum |
docs/01_機能仕様書/public_api.md:6:| 正本 | 要件シート.xlsx シート3/7/8/9/10/12/13 ＋ Bladeモック `basic` / `advanced`（frozen） |
docs/01_機能仕様書/public_api.md-7-| 適用範囲 | 基本要件（認証なしCRUD）と ★応用要件（Sanctumトークン認証の後付け） |
docs/01_機能仕様書/public_api.md-8-| 完成条件 | 本書だけで発注書05・10が書ける（他ファイル参照不要） |
docs/01_機能仕様書/public_api.md-9-
--
docs/01_機能仕様書/public_api.md-396-
docs/01_機能仕様書/public_api.md-397-**トークンの入手経路**
docs/01_機能仕様書/public_api.md-398-
docs/01_機能仕様書/public_api.md:399:トークン発行用のAPIエンドポイントは**新設しない**。要件シート シート13 のエンドポイント一覧は AP01〜AP05 の5本で確定しており、Bladeモックにもトークン発行・管理の画面が存在しないため、発行手段はアプリケーションの機能に含めない。
docs/01_機能仕様書/public_api.md-400-
docs/01_機能仕様書/public_api.md-401-| 用途 | 手段 |
docs/01_機能仕様書/public_api.md-402-|---|---|
--
docs/01_機能仕様書/ranking.md-3-| 項目 | 内容 |
docs/01_機能仕様書/ranking.md-4-|---|---|
docs/01_機能仕様書/ranking.md-5-| 対象発注書 | 04_ジャンルランキング（ランキング部分） |
docs/01_機能仕様書/ranking.md:6:| 正本 | 要件シート.xlsx シート5/7/10/12 ＋ Bladeモック `basic`（frozen） |
docs/01_機能仕様書/ranking.md-7-| 適用範囲 | 基本要件のみ |
docs/01_機能仕様書/ranking.md-8-| 完成条件 | 本書だけで発注書04のランキング部分が書ける（他ファイル参照不要） |
docs/01_機能仕様書/ranking.md-9-| 版 | v1（2026-09-01・面談②のSoftDelete確定を反映済み） |
--
docs/01_機能仕様書/ranking.md-67-
docs/01_機能仕様書/ranking.md-68----
docs/01_機能仕様書/ranking.md-69-
docs/01_機能仕様書/ranking.md:70:## 3. 画面契約（Bladeモック実測・改変禁止）
docs/01_機能仕様書/ranking.md-71-
docs/01_機能仕様書/ranking.md-72-### PG11 ランキング（`ranking/index.blade.php`）
docs/01_機能仕様書/ranking.md-73-
--
docs/01_機能仕様書/ranking.md-127-- N+1 は発生しない構造（`withAvg` / `withCount` はサブクエリ1回で解決する）。`with('reviews')` を追加してはならない。全レビュー行をメモリに載せることになり、シート3の Eloquent 要件に反する。
docs/01_機能仕様書/ranking.md-128-- 書籍を論理削除するとランキングから即座に消え、復元すると再び現れる。この往復が要件どおりに動くことを実機で確認すること。
docs/01_機能仕様書/ranking.md-129-- `limit(10)` は `take(10)` でも可。どちらでも `$rankedBooks` は Collection になる。
docs/01_機能仕様書/ranking.md:130:- Blade は改変不要。ランキング機能はモックの契約どおりに実装できる。
docs/01_機能仕様書/ranking.md-131-
docs/01_機能仕様書/ranking.md-132----
docs/01_機能仕様書/ranking.md-133-
--
docs/01_機能仕様書/reading_plans.md-3-| 項目 | 内容 |
docs/01_機能仕様書/reading_plans.md-4-|---|---|
docs/01_機能仕様書/reading_plans.md-5-| 対象発注書 | 11_読書計画CRUD（日次バッチによる自動失効は 12_通知バッチ と連動。notifications.md も参照） |
docs/01_機能仕様書/reading_plans.md:6:| 正本 | 要件シート.xlsx シート5/7/8/9/10/11/12 ＋ Bladeモック `advanced`（frozen） |
docs/01_機能仕様書/reading_plans.md-7-| 適用範囲 | 応用要件。読書計画の一覧・作成・編集・削除・読了操作、状態遷移、期限管理、認可、reading_plans テーブル、ReadingPlanSeeder、テスト観点 |
docs/01_機能仕様書/reading_plans.md-8-| 完成条件 | 本書だけで発注書11が書ける（他ファイル参照不要。ただし日次バッチの通知発火は notifications.md がバッチ本体を持つ） |
docs/01_機能仕様書/reading_plans.md-9-
--
docs/01_機能仕様書/reading_plans.md-117-
docs/01_機能仕様書/reading_plans.md-118----
docs/01_機能仕様書/reading_plans.md-119-
docs/01_機能仕様書/reading_plans.md:120:## 3. 画面契約（Bladeモック実測・改変禁止）
docs/01_機能仕様書/reading_plans.md-121-
docs/01_機能仕様書/reading_plans.md-122-| 画面ID | Bladeファイル | 渡す変数 | Blade が要求する属性・メソッド | flash |
docs/01_機能仕様書/reading_plans.md-123-|---|---|---|---|---|
--
docs/01_機能仕様書/reports.md-3-| 項目 | 内容 |
docs/01_機能仕様書/reports.md-4-|---|---|
docs/01_機能仕様書/reports.md-5-| 対象発注書 | 09_読書レポート |
docs/01_機能仕様書/reports.md:6:| 正本 | 要件シート.xlsx シート5/7/10/12 ＋ Bladeモック `advanced`（frozen） |
docs/01_機能仕様書/reports.md-7-| 適用範囲 | 応用要件。ログインユーザーの読書統計（4種）の集計と表示、テスト観点 |
docs/01_機能仕様書/reports.md-8-| 完成条件 | 本書だけで発注書09が書ける（他ファイル参照不要） |
docs/01_機能仕様書/reports.md-9-
--
docs/01_機能仕様書/reports.md-39-
docs/01_機能仕様書/reports.md-40----
docs/01_機能仕様書/reports.md-41-
docs/01_機能仕様書/reports.md:42:## 2. 画面契約（Bladeモック実測・改変禁止）
docs/01_機能仕様書/reports.md-43-
docs/01_機能仕様書/reports.md-44-| 画面ID | Bladeファイル | 渡す変数 | Blade が要求する構造 |
docs/01_機能仕様書/reports.md-45-|---|---|---|---|
--
docs/01_機能仕様書/reviews_likes.md-3-| 項目 | 内容 |
docs/01_機能仕様書/reviews_likes.md-4-|---|---|
docs/01_機能仕様書/reviews_likes.md-5-| 対象発注書 | 03_レビュー・お気に入り・いいね（レビューといいねの部分） |
docs/01_機能仕様書/reviews_likes.md:6:| 正本 | 要件シート.xlsx シート5/7/8/9/10/11/12 ＋ Bladeモック `basic`（frozen） |
docs/01_機能仕様書/reviews_likes.md-7-| 適用範囲 | 基本要件のみ |
docs/01_機能仕様書/reviews_likes.md-8-| 完成条件 | 本書だけで発注書03のレビュー・いいね部分が書ける（他ファイル参照不要） |
docs/01_機能仕様書/reviews_likes.md-9-| 版 | v1（2026-09-01・面談②のSoftDelete確定を反映済み） |
--
docs/01_機能仕様書/reviews_likes.md-64-
docs/01_機能仕様書/reviews_likes.md-65----
docs/01_機能仕様書/reviews_likes.md-66-
docs/01_機能仕様書/reviews_likes.md:67:## 3. 画面契約（Bladeモック実測・改変禁止部分）
docs/01_機能仕様書/reviews_likes.md-68-
docs/01_機能仕様書/reviews_likes.md-69-### PG02 書籍詳細（`books/show.blade.php`）内のレビュー領域
docs/01_機能仕様書/reviews_likes.md-70-
--
docs/01_機能仕様書/reviews_likes.md-213-
docs/01_機能仕様書/reviews_likes.md-214-## 10. 実装上の注意（CC向け）
docs/01_機能仕様書/reviews_likes.md-215-
docs/01_機能仕様書/reviews_likes.md:216:- 書籍詳細のレビュー一覧は `created_at` の降順（新しい順）。要件シート・Bladeモックとも並び順の指定がないため、書籍一覧の「最新順」に揃えている。
docs/01_機能仕様書/reviews_likes.md-217-- N+1 回避のため、書籍詳細では `reviews.user` と `reviews.likedByUsers` を eager load する（`BookController@show`。books.md §2）。
docs/01_機能仕様書/reviews_likes.md-218-- `Auth::user()->likedReviews` は Blade がレビュー1件ごとに `contains()` を呼ぶ形なので、ユーザー側のリレーションを1回ロードすれば追加クエリは発生しない。
docs/01_機能仕様書/reviews_likes.md-219-- 中間テーブルは timestamps を持たないので `withTimestamps()` は付けない。
docs/01_機能仕様書/reviews_likes.md:220:- Blade は改変不要。レビュー・いいね機能はモックの契約どおりに実装できる（削除済み書籍まわりの Blade 追記は books.md §10-1 の管轄）。
docs/01_機能仕様書/reviews_likes.md-221-
docs/01_機能仕様書/reviews_likes.md-222----
docs/01_機能仕様書/reviews_likes.md-223-
--
docs/02_発注書/発注書_01_土台認証.md:1:status: confirmed complete（デザインUI優先例外の影響なし）
docs/02_発注書/発注書_01_土台認証.md-2-
docs/02_発注書/発注書_01_土台認証.md-3-# 発注書 01 — 土台＋認証
docs/02_発注書/発注書_01_土台認証.md-4-
docs/02_発注書/発注書_01_土台認証.md-5-**対象走行**: 走行①（第3週）
docs/02_発注書/発注書_01_土台認証.md:6:**出典**: 要件シート.xlsx シート3・4・5・7・8・10・11・12 ／ Bladeモック `Preparedblade-mockcase-BookShelf` basic ブランチ実測 ／ BookShelf 削除連動 土台設計確定書
docs/02_発注書/発注書_01_土台認証.md-7-**対の検品表**: `docs/03_検品表/01_土台認証.md`
docs/02_発注書/発注書_01_土台認証.md-8-
docs/02_発注書/発注書_01_土台認証.md-9----
--
docs/02_発注書/発注書_01_土台認証.md-30-| テストコード（Unit / Feature） | 走行⑥ |
docs/02_発注書/発注書_01_土台認証.md-31-| 型宣言・PHPDoc・Collection化 | 走行⑬（シート3で応用スコープと明示） |
docs/02_発注書/発注書_01_土台認証.md-32-| Sanctum / personal_access_tokens | 走行⑩ |
docs/02_発注書/発注書_01_土台認証.md:33:| Blade テンプレートの改変 | **恒久的に禁止**（モックは frozen） |
docs/02_発注書/発注書_01_土台認証.md-34-
docs/02_発注書/発注書_01_土台認証.md-35-### この発注書の完成条件
docs/02_発注書/発注書_01_土台認証.md-36-
--
docs/02_発注書/発注書_01_土台認証.md-129-
docs/02_発注書/発注書_01_土台認証.md-130-- `Fortify::updateUserProfileInformationUsing` / `updateUserPasswordsUsing` / `resetUserPasswordsUsing` の行は削除する。
docs/02_発注書/発注書_01_土台認証.md-131-- `RateLimiter::for('two-factor', ...)` の行は削除する。
docs/02_発注書/発注書_01_土台認証.md:132:- ビュー名は `auth.login` / `auth.register`。basic ブランチの `resources/views/auth/login.blade.php` `register.blade.php` に一致する（実測）。
docs/02_発注書/発注書_01_土台認証.md-133-
docs/02_発注書/発注書_01_土台認証.md-134-#### (e) 未使用 Action の削除
docs/02_発注書/発注書_01_土台認証.md-135-
--
docs/02_発注書/発注書_01_土台認証.md-277-
docs/02_発注書/発注書_01_土台認証.md-278-### 3-3. モデル
docs/02_発注書/発注書_01_土台認証.md-279-
docs/02_発注書/発注書_01_土台認証.md:280:リレーション名は **Blade モックが直接呼んでいる名前で固定**されている（実測）。改名禁止。
docs/02_発注書/発注書_01_土台認証.md-281-
docs/02_発注書/発注書_01_土台認証.md-282-| モデル | メソッド | Blade 側の呼び出し（実測箇所） |
docs/02_発注書/発注書_01_土台認証.md-283-|---|---|---|
--
docs/02_発注書/発注書_01_土台認証.md-368-}
docs/02_発注書/発注書_01_土台認証.md-369-```
docs/02_発注書/発注書_01_土台認証.md-370-
docs/02_発注書/発注書_01_土台認証.md:371:**`published_date` に `$casts` を設定しない。** 根拠（Tier A・モック実測）: `books/_form.blade.php` が `<input type="date" value="{{ old('published_date', $book->published_date ?? '') }}">` で生値をそのまま date 入力の value に流している。`date` キャストを付けると Carbon の文字列化で `2012-06-23 00:00:00` になり、date 入力の value 形式（`YYYY-MM-DD`）を満たさず初期値が空になる。`books/show.blade.php` の `{{ $book->published_date }}` も同様。
docs/02_発注書/発注書_01_土台認証.md-372-
docs/02_発注書/発注書_01_土台認証.md-373-**平均評価のアクセサ（`getAverageRatingAttribute` 等）を作らない。** `books/index.blade.php` と `ranking/index.blade.php` は `$book->reviews_avg_rating` と `$book->reviews_count` を参照している（実測）。これは `withAvg('reviews', 'rating')` / `withCount('reviews')` が生成する集約属性名であり、アクセサではない。レビュー0件時は `reviews_avg_rating` が `null` になり、Blade 側の `@if($book->reviews_avg_rating)` で非表示に落ちる。
docs/02_発注書/発注書_01_土台認証.md-374-
--
docs/02_発注書/発注書_01_土台認証.md-523-
docs/02_発注書/発注書_01_土台認証.md-524-## 変更履歴（今回のまとめ出力時に確認したこと）
docs/02_発注書/発注書_01_土台認証.md-525-
docs/02_発注書/発注書_01_土台認証.md:526:- 本発注書はテーブル・Fortify設定を扱う文書であり、ログイン・会員登録画面のボタン構成を具体的に規定する記述がない（ビュー名の束縛のみ）。したがって`決定記録_デザインUI優先例外.md`の対象範囲外であり、変更不要と確認した。該当の矛盾は`検品表01_土台認証.md`側（G-1・G-11）にのみ存在していたため、そちらは別途修正済み。
docs/02_発注書/発注書_01_土台認証.md-527-- `差し戻し指示_走行①.md`（`Fortify::authenticateUsing()`除去）は、本発注書§3-1(d)の元々の指定どおりに実装を戻す差し戻しであり、本発注書の記述自体に誤りはなかったため変更不要。
--
docs/02_発注書/発注書_02_書籍CRUD.md:1:status: patched（デザインUI優先例外により★評価表示関連を修正）
docs/02_発注書/発注書_02_書籍CRUD.md-2-
docs/02_発注書/発注書_02_書籍CRUD.md-3-# 発注書 02 — 書籍CRUD
docs/02_発注書/発注書_02_書籍CRUD.md-4-
--
docs/02_発注書/発注書_02_書籍CRUD.md-36-
docs/02_発注書/発注書_02_書籍CRUD.md-37-### この発注書の完成条件
docs/02_発注書/発注書_02_書籍CRUD.md-38-
docs/02_発注書/発注書_02_書籍CRUD.md:39:`docs/03_検品表/02_書籍CRUD.md` の全行がYESになること。具体的には、書籍の一覧・詳細・登録・編集・削除（論理削除）・復元がBladeモックの契約どおりに動作し、削除済み書籍の詳細ページが404にならず「この本は削除されました」バナーとともに表示され、登録者本人にのみ復元操作が可能であること。
docs/02_発注書/発注書_02_書籍CRUD.md-40-
docs/02_発注書/発注書_02_書籍CRUD.md-41----
docs/02_発注書/発注書_02_書籍CRUD.md-42-
--
docs/02_発注書/発注書_02_書籍CRUD.md-101-
docs/02_発注書/発注書_02_書籍CRUD.md-102-`index` の `with('genres')` はN+1回避のため必須。
docs/02_発注書/発注書_02_書籍CRUD.md-103-
docs/02_発注書/発注書_02_書籍CRUD.md:104:> **【2026-09-03改訂】** `index`は元々`withAvg('reviews', 'rating')`も付与していたが、実機検品でのデザインUI優先の例外（`決定記録_デザインUI優先例外.md`項目1）により、書籍一覧カードの★評価表示自体を廃止したため、対応する集約クエリも撤去した。書籍詳細（`show`）・ランキング（`RankingController`）の平均評価表示・集計には影響しない。
docs/02_発注書/発注書_02_書籍CRUD.md-105-
docs/02_発注書/発注書_02_書籍CRUD.md-106----
docs/02_発注書/発注書_02_書籍CRUD.md-107-
docs/02_発注書/発注書_02_書籍CRUD.md:108:## 3. 画面契約（Bladeモック実測・改変禁止）
docs/02_発注書/発注書_02_書籍CRUD.md-109-
docs/02_発注書/発注書_02_書籍CRUD.md-110-| 画面ID | Bladeファイル | 渡す変数 | 必須の属性・リレーション | flashスロット |
docs/02_発注書/発注書_02_書籍CRUD.md-111-|---|---|---|---|---|
--
docs/02_発注書/発注書_02_書籍CRUD.md-114-| PG03 | `books/create.blade.php` + `_form.blade.php` | `$genres` | `$genres->isEmpty()` `$genre->id` `$genre->name` | なし（`$errors`と`old()`のみ） |
docs/02_発注書/発注書_02_書籍CRUD.md-115-| PG04 | `books/edit.blade.php` + `_form.blade.php` | `$book`, `$genres` | 上記＋`$book->genres->pluck('id')` | なし |
docs/02_発注書/発注書_02_書籍CRUD.md-116-
docs/02_発注書/発注書_02_書籍CRUD.md:117:> **【2026-09-03改訂】** PG01から`reviews_avg_rating`を削除。デザインUI（要件シート シート6・書籍一覧画像）に★評価表示が存在しないため、`決定記録_デザインUI優先例外.md`項目1に基づき撤去した（frozen宣言のあるBladeモック自体には元々★評価表示があったため、本来は§0の正本優先順位ではモックが勝つ場面だが、デザインUIを優先する例外を適用）。加えて、書籍一覧のページネーションは`resources/views/vendor/pagination/tailwind.blade.php`を`vendor:publish`した上で左下グルーピング配置に変更済み（同例外項目3）。この変更はページネーション機能を提供する全画面（ジャンル詳細等）に共通で反映される。
docs/02_発注書/発注書_02_書籍CRUD.md-118-
docs/02_発注書/発注書_02_書籍CRUD.md-119-フォーム項目（`_form.blade.php`実測）: `title` / `author` / `isbn` / `published_date`(date) / `description`(textarea) / `image_url` / `genres[]`(checkbox・複数)。必須マーク`*`は title / author / ISBN-13 / 出版日 / ジャンル。ISBN欄の注記「13桁のISBNコードを入力してください」、placeholder `9784000000000`。
docs/02_発注書/発注書_02_書籍CRUD.md-120-
--
docs/02_発注書/発注書_02_書籍CRUD.md-206-
docs/02_発注書/発注書_02_書籍CRUD.md-207-### 8-1. `books/show.blade.php` への追記（Blade改変が必要な唯一の箇所）
docs/02_発注書/発注書_02_書籍CRUD.md-208-
docs/02_発注書/発注書_02_書籍CRUD.md:209:frozen宣言のあるモックだが、削除済み状態の表示は元のモックに定義自体が存在しない。**既存の記述を書き換えず、以下4点のみ追記する**。
docs/02_発注書/発注書_02_書籍CRUD.md-210-
docs/02_発注書/発注書_02_書籍CRUD.md-211-1. `session('success')` ブロック直後に、`@if($book->trashed())` で囲んだバナー「この本は削除されました」を追加
docs/02_発注書/発注書_02_書籍CRUD.md-212-2. お気に入りボタンの `@auth` ブロック全体を `@if(! $book->trashed())` で囲む
--
docs/02_発注書/発注書_02_書籍CRUD.md-250-
docs/02_発注書/発注書_02_書籍CRUD.md-251-## 11. 禁止事項
docs/02_発注書/発注書_02_書籍CRUD.md-252-
docs/02_発注書/発注書_02_書籍CRUD.md:253:1. `resources/` 配下のBlade / CSS / JSを、本書§8-1に明記した4点以外は1文字も変更しない。**（2026-09-03注記: この禁止は走行②着手時点のもの。実機検品後にデザインUI優先の例外で`books/index.blade.php`・ページネーションコンポーネント等に別途変更が加わっている。詳細は`決定記録_デザインUI優先例外.md`参照。以後この発注書を参照する際は、本注記と§2・§3の改訂箇所を優先すること）**
docs/02_発注書/発注書_02_書籍CRUD.md-254-2. 本発注書に明記されていないバリデーションロジック・認可ロジックを独自に追加しない（発注書の指定と異なる実装をする場合は、仮決めせず`QUESTIONS.md`に記録して停止する）。
docs/02_発注書/発注書_02_書籍CRUD.md-255-3. `migrate:fresh` を要するmigration変更をしない（テーブル定義は走行①で確定済み）。
docs/02_発注書/発注書_02_書籍CRUD.md-256-4. 「やらないこと」表に挙げた成果物を先取りして作らない。
--
docs/02_発注書/発注書_02_書籍CRUD.md-280-
docs/02_発注書/発注書_02_書籍CRUD.md-281-## 変更履歴（今回のまとめ出力時に確認したこと）
docs/02_発注書/発注書_02_書籍CRUD.md-282-
docs/02_発注書/発注書_02_書籍CRUD.md:283:- **§2 index行・§3 PG01行**: `withAvg('reviews','rating')`／`reviews_avg_rating`を削除。デザインUI優先の例外（`決定記録_デザインUI優先例外.md`項目1・3）を反映。
docs/02_発注書/発注書_02_書籍CRUD.md-284-- **§11-1**: 上記変更が§8-1の「4点以外は変更しない」という当時の禁止事項と表面上矛盾するため、時点の異なる決定であることを明記する注記を追加。
docs/02_発注書/発注書_02_書籍CRUD.md-285-- B-2（未認証時302リダイレクト）に関するcurl/CSRFの検証方法の変更は、`検品表02_B2・検品表03_C4_修正パッチ.md`側の変更であり、本発注書のルーティング仕様（§1）自体に変更はない。
--
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-36-
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-37-### この発注書の完成条件
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-38-
docs/02_発注書/発注書_03_レビューお気に入りいいね.md:39:`docs/03_検品表/03_レビューお気に入りいいね.md` の全行がYESになること。レビューの投稿・編集・削除、レビューへのいいねトグル、お気に入りトグルと一覧がBladeモックの契約どおりに動作し、削除済み書籍下でもレビュー・いいね操作（投稿者本人の編集削除・いいね）が引き続き機能すること。
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-40-
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-41----
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-42-
--
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-98-
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-99----
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-100-
docs/02_発注書/発注書_03_レビューお気に入りいいね.md:101:## 3. 画面契約（Bladeモック実測・改変禁止）
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-102-
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-103-### PG02 書籍詳細（`books/show.blade.php`）内のレビュー・いいね領域
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-104-
--
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-140-| 解除ボタン | `favorites.toggle`へのPOSTフォーム（赤いハートアイコン）。ストレッチリンクより前面（`relative z-10`）に配置し、リンクに埋もれずクリック可能を維持する |
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-141-| flashスロット | **なし** |
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-142-
docs/02_発注書/発注書_03_レビューお気に入りいいね.md:143:> **【2026-09-03改訂】** 元のモック実測は「タイトルのみがリンク」だったが、片倉より「書籍一覧（PG01）に合わせカード全体をクリック可能にしたい」との指摘を受け、カード全面クリックに変更した。デザインUIとモックの相違ではなく片倉の直接指摘によるUX改善のため、`決定記録_デザインUI優先例外.md`の正本上書き例外（項目1・3・4・5・7）とは区分が異なる（同記録の項目6参照）。
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-144-
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-145-一覧に`genres`は表示されないため`with('genres')`は不要。
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-146-
--
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-246-- 書籍詳細のレビュー一覧は `created_at` の降順（新しい順）。
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-247-- N+1回避のため、書籍詳細では `reviews.user` と `reviews.likedByUsers` を eager load する（`BookController@show`。走行②の管轄だが未実装なら本走行で確認すること）。
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-248-- 中間テーブルは timestamps を持たないので `withTimestamps()` は付けない。
docs/02_発注書/発注書_03_レビューお気に入りいいね.md:249:- Blade は改変不要。レビュー・いいね・お気に入り機能はモックの契約どおりに実装できる。**（2026-09-03注記: 「改変不要」は走行③着手時点のもの。後日の実機検品でお気に入り一覧のカードリンク方式に変更が入っている。§3 PG10参照）**
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-250-
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-251----
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-252-
--
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-279-
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-280-## 変更履歴（今回のまとめ出力時に確認したこと）
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-281-
docs/02_発注書/発注書_03_レビューお気に入りいいね.md:282:- **§3 PG10行**: お気に入り一覧のカード全面クリック化（ストレッチリンク方式）を反映。片倉の直接UX指摘によるもので、`決定記録_デザインUI優先例外.md`の正本上書き5項目とは別区分（同記録項目6参照）。
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-283-- **§9・§10**: 「Blade改変不要」「1文字も変更しない」の記述が上記変更と矛盾するため、時点の異なる決定であることを示す注記を追加。
docs/02_発注書/発注書_03_レビューお気に入りいいね.md-284-- C-4（未認証時302リダイレクト）の確認方法変更は`検品表03_C4修正パッチ`側の変更であり、本発注書のルーティング仕様（§1）自体に変更はない。
--
docs/02_発注書/発注書_04_ジャンルランキング.md:1:status: confirmed complete（デザインUI優先例外の影響なし）
docs/02_発注書/発注書_04_ジャンルランキング.md-2-
docs/02_発注書/発注書_04_ジャンルランキング.md-3-# 発注書 04 — ジャンル・ランキング
docs/02_発注書/発注書_04_ジャンルランキング.md-4-
--
docs/02_発注書/発注書_04_ジャンルランキング.md-33-
docs/02_発注書/発注書_04_ジャンルランキング.md-34-### この発注書の完成条件
docs/02_発注書/発注書_04_ジャンルランキング.md-35-
docs/02_発注書/発注書_04_ジャンルランキング.md:36:`docs/03_検品表/04_ジャンルランキング.md` の全行がYESになること。ジャンルの一覧・詳細・登録・編集・削除（削除制限の二重防御込み）とランキングTOP10の集計・表示がBladeモックの契約どおりに動作すること。
docs/02_発注書/発注書_04_ジャンルランキング.md-37-
docs/02_発注書/発注書_04_ジャンルランキング.md-38----
docs/02_発注書/発注書_04_ジャンルランキング.md-39-
--
docs/02_発注書/発注書_04_ジャンルランキング.md-98-
docs/02_発注書/発注書_04_ジャンルランキング.md-99----
docs/02_発注書/発注書_04_ジャンルランキング.md-100-
docs/02_発注書/発注書_04_ジャンルランキング.md:101:## 3. 画面契約（Bladeモック実測・改変禁止）
docs/02_発注書/発注書_04_ジャンルランキング.md-102-
docs/02_発注書/発注書_04_ジャンルランキング.md-103-### ジャンル
docs/02_発注書/発注書_04_ジャンルランキング.md-104-
--
docs/02_発注書/発注書_04_ジャンルランキング.md-227-- `$genre->books()`はSoftDeletesの影響を受ける。**表示（books_count・一覧）は生存書籍のみ、削除可否判定は`withTrashed()`付き**という使い分けを取り違えないこと。
docs/02_発注書/発注書_04_ジャンルランキング.md-228-- ランキングは`withAvg`/`withCount`でサブクエリ1回に解決するため、N+1は発生しない。`with('reviews')`を追加してはならない（全レビュー行をメモリに載せることになる）。
docs/02_発注書/発注書_04_ジャンルランキング.md-229-- 書籍を論理削除するとランキングから即座に消え、復元すると再び現れる。この往復を実機で確認すること。
docs/02_発注書/発注書_04_ジャンルランキング.md:230:- Blade は改変不要。ジャンル・ランキング機能はモックの契約どおりに実装できる。
docs/02_発注書/発注書_04_ジャンルランキング.md-231-
docs/02_発注書/発注書_04_ジャンルランキング.md-232----
docs/02_発注書/発注書_04_ジャンルランキング.md-233-
--
docs/02_発注書/発注書_04_ジャンルランキング.md-261-
docs/02_発注書/発注書_04_ジャンルランキング.md-262-## 変更履歴（今回のまとめ出力時に確認したこと）
docs/02_発注書/発注書_04_ジャンルランキング.md-263-
docs/02_発注書/発注書_04_ジャンルランキング.md:264:- ジャンル・ランキング画面のいずれも書籍一覧（PG01）の★評価表示・ページネーション位置の変更対象に含まれていない。ランキングの星表示・小数点表示（§3）は`RankingController`独自の`withAvg`呼び出しに基づいており、`決定記録_デザインUI優先例外.md`が対象とした書籍一覧側の変更（項目1・3）の影響を受けない。よって本発注書は変更不要と確認した。
--
docs/02_発注書/発注書_05_公開APIシーディング.md:1:status: confirmed complete（デザインUI優先例外の影響なし）
docs/02_発注書/発注書_05_公開APIシーディング.md-2-
docs/02_発注書/発注書_05_公開APIシーディング.md-3-# 発注書 05 — 公開API・シーディング
docs/02_発注書/発注書_05_公開APIシーディング.md-4-
--
docs/02_発注書/発注書_05_公開APIシーディング.md-392-
docs/02_発注書/発注書_05_公開APIシーディング.md-393-## 変更履歴（今回のまとめ出力時に確認したこと）
docs/02_発注書/発注書_05_公開APIシーディング.md-394-
docs/02_発注書/発注書_05_公開APIシーディング.md:395:- APIレスポンスの`average_rating`（§3・§5）はAPI専用の集計処理（`Api\V1\BookController`独自の`withAvg`）に基づくものであり、Web画面の書籍一覧（PG01）から★評価表示を撤去した`決定記録_デザインUI優先例外.md`項目1の対象外。API側の`average_rating`は変更なく提供され続ける。
docs/02_発注書/発注書_05_公開APIシーディング.md-396-- §8-3のBookSeeder`description`は元々具体的な文言を指定しておらず自由記述欄であるため、実機検品での書籍説明文の書き直し（同記録項目7）はこの発注書の記述と抵触しない。よって本発注書は変更不要と確認した。
--
docs/02_発注書/発注書_06_基本テスト.md:1:status: confirmed complete（デザインUI優先例外の影響なし）
docs/02_発注書/発注書_06_基本テスト.md-2-
docs/02_発注書/発注書_06_基本テスト.md-3-# 発注書 06 — 基本テスト
docs/02_発注書/発注書_06_基本テスト.md-4-
--
docs/02_発注書/発注書_06_基本テスト.md-218-
docs/02_発注書/発注書_06_基本テスト.md-219-## 変更履歴（今回のまとめ出力時に確認したこと）
docs/02_発注書/発注書_06_基本テスト.md-220-
docs/02_発注書/発注書_06_基本テスト.md:221:- U-B4（`withAvg('reviews','rating')`による平均評価算出）は`Book`モデル自体の集計能力を検証する単体テスト観点であり、`BookController@index`から`withAvg`呼び出しを撤去した`決定記録_デザインUI優先例外.md`項目1の影響を受けない（モデルは引き続き`withAvg`をサポートしており、コントローラー側で呼ぶかどうかとは別の話）。
docs/02_発注書/発注書_06_基本テスト.md-222-- 実機検品で発覚したフロントエンドビルドの回帰（`app.css`空・`app.js`のimport解決失敗）は、PHPUnitのFeature/Unitテストがコンパイル済みアセットの生成を検証しないため、本発注書のいずれの観点にも該当しない。将来的にこの種の回帰を自動検知したい場合は、本発注書に`sail npm run build`の成功確認を追加する改訂を検討すること（現時点では未追加）。
docs/02_発注書/発注書_06_基本テスト.md-223-- 上記いずれも本発注書の記述と抵触しないため、変更不要と確認した。
--
docs/02_発注書/発注書_07_検索フィルタソート.md-82-]);
docs/02_発注書/発注書_07_検索フィルタソート.md-83-```
docs/02_発注書/発注書_07_検索フィルタソート.md-84-
docs/02_発注書/発注書_07_検索フィルタソート.md:85:**重要（Bladeモック実測で判明した必須事項）**: `books/index.blade.php`のジャンルフィルタ用セレクトは`$genres`という変数（全ジャンルの一覧）を前提に`@foreach($genres ?? [] as $genre)`で選択肢を描画している。`genres`をビューへ渡し忘れると、フォーム自体は表示されるが選択肢が1件も出ず、ジャンルフィルタが実質使用不能になる（`?? []`があるため500エラーにはならず、検品で見た目上気づきにくい穴になる点に注意）。順序の指定はモック・要件シートともに無いため`Genre::all()`でよい。
docs/02_発注書/発注書_07_検索フィルタソート.md-86-
docs/02_発注書/発注書_07_検索フィルタソート.md-87-補足:
docs/02_発注書/発注書_07_検索フィルタソート.md-88-
--
docs/02_発注書/発注書_09_読書レポート.md-3-# 発注書 09 — マイ読書レポート
docs/02_発注書/発注書_09_読書レポート.md-4-
docs/02_発注書/発注書_09_読書レポート.md-5-**対象走行**: 走行⑨（第6週）
docs/02_発注書/発注書_09_読書レポート.md:6:**出典**: 要件シート.xlsx シート3（コード品質基準）／シート7 R49（マイ読書レポート画面）／シート9 R11・R12（★BookSeeder・★ReviewSeeder）／シート10 R26（★マイ読書レポート テスト観点）／シート12（テーブル仕様・ER図） ／ Bladeモック `Preparedblade-mockcase-BookShelf` advancedブランチ実測（`reports/index.blade.php` の `$stats` 変数契約）
docs/02_発注書/発注書_09_読書レポート.md-7-**対の検品表**: `docs/03_検品表/09_読書レポート.md`
docs/02_発注書/発注書_09_読書レポート.md-8-**前提**: 走行①〜⑧が合格・確定済み。`layouts/navigation.blade.php` は発注書12まで意図的に未移入のため、本画面はヘッダーのナビゲーションメニューにまだ表示されない。動作確認は `/reports` への直接URLアクセスで行う。
docs/02_発注書/発注書_09_読書レポート.md-9-
--
docs/02_発注書/発注書_09_読書レポート.md-13-
docs/02_発注書/発注書_09_読書レポート.md-14-### やること
docs/02_発注書/発注書_09_読書レポート.md-15-
docs/02_発注書/発注書_09_読書レポート.md:16:1. `Bladeモック参照.md` の手順でadvancedブランチをcloneし、`resources/views/reports/index.blade.php` の1ファイルのみをコピーする（他のBladeファイルには触れない）。
docs/02_発注書/発注書_09_読書レポート.md-17-2. `ReportController@index`（Webコントローラー、新規作成）の実装。
docs/02_発注書/発注書_09_読書レポート.md-18-3. ルーティング追加: `GET /reports` → `reports.index`（`auth` ミドルウェア必須）。
docs/02_発注書/発注書_09_読書レポート.md-19-4. `$stats` 配列の集計ロジックの実装（下記§2）。
--
docs/02_発注書/発注書_11_読書計画CRUD.md-3-# 発注書 11 — 読書計画CRUD＋自動失効バッチ
docs/02_発注書/発注書_11_読書計画CRUD.md-4-
docs/02_発注書/発注書_11_読書計画CRUD.md-5-**対象走行**: 走行⑪（第6週）
docs/02_発注書/発注書_11_読書計画CRUD.md:6:**出典**: 要件シート.xlsx シート3 R33・R34・R36（★Schedule+ConsoleCommand・★DB Transaction・★PHP Enum）／シート7 R57〜R66（★読書計画一覧〜編集の各画面）・R69（★日次バッチ処理）／シート8 R19（★読書計画作成・編集バリデーション）／シート9 R13（★ReadingPlanSeeder）／シート10 R28・R29・R31（★読書計画／★読書計画（期限変更）／★自動失効バッチ テスト観点）／シート11 DR09（★読書計画データ要件）／シート12（reading_plansテーブル） ／ 機能仕様書 `docs/01_機能仕様書/reading_plans.md` ／ Bladeモック `Preparedblade-mockcase-BookShelf` advancedブランチ実測（`reading-plans/{index,create,edit}.blade.php` の変数契約、面談準備資料「advanced Blade契約サマリ」PG15〜PG17）
docs/02_発注書/発注書_11_読書計画CRUD.md-7-**対の検品表**: `docs/03_検品表/11_読書計画CRUD.md`
docs/02_発注書/発注書_11_読書計画CRUD.md-8-**前提**: 走行①〜⑩が合格・確定済み。`layouts/navigation.blade.php` は発注書12まで意図的に未移入のため、本画面群はヘッダーのナビゲーションメニューにまだ表示されない。動作確認は `/reading-plans` への直接URLアクセスで行う。通知機能（発注書12）はまだ存在しないため、本走行の自動失効バッチは状態更新のみを行い、通知の発火は行わない。
docs/02_発注書/発注書_11_読書計画CRUD.md-9-
--
docs/02_発注書/発注書_11_読書計画CRUD.md-13-
docs/02_発注書/発注書_11_読書計画CRUD.md-14-### やること
docs/02_発注書/発注書_11_読書計画CRUD.md-15-
docs/02_発注書/発注書_11_読書計画CRUD.md:16:1. `Bladeモック参照.md` の手順でadvancedブランチをcloneし、`resources/views/reading-plans/index.blade.php`・`create.blade.php`・`edit.blade.php` の3ファイルをコピーする。
docs/02_発注書/発注書_11_読書計画CRUD.md-17-2. `reading_plans` テーブルのマイグレーション作成・実行。
docs/02_発注書/発注書_11_読書計画CRUD.md-18-3. `App\Enums\ReadingPlanStatus` Enumの作成。
docs/02_発注書/発注書_11_読書計画CRUD.md-19-4. `ReadingPlan` モデルの作成（`User`・`Book` へのリレーション、`status` のEnumキャスト、`book()` の `withTrashed()`）。
--
docs/02_発注書/発注書_11_読書計画CRUD.md-280-```
docs/02_発注書/発注書_11_読書計画CRUD.md-281-
docs/02_発注書/発注書_11_読書計画CRUD.md-282-- ルートパラメータ名は `{plan}` とし、ルートモデル紐付けの変数名も `$plan` にする（Bladeが `route('reading-plans.edit', $plan)` の形で参照している名前に合わせる）。
docs/02_発注書/発注書_11_読書計画CRUD.md:283:- 7本のみ。`show` ルートは作らない（Bladeモックに詳細画面が存在しないため）。
docs/02_発注書/発注書_11_読書計画CRUD.md-284-- 全ルートに `auth` ミドルウェアを適用する。
docs/02_発注書/発注書_11_読書計画CRUD.md-285-
docs/02_発注書/発注書_11_読書計画CRUD.md-286----
--
docs/02_発注書/発注書_12_通知バッチ.md-3-# 発注書 12 — 通知＋リマインダーバッチ
docs/02_発注書/発注書_12_通知バッチ.md-4-
docs/02_発注書/発注書_12_通知バッチ.md-5-**対象走行**: 走行⑫（第6週）
docs/02_発注書/発注書_12_通知バッチ.md:6:**出典**: 要件シート.xlsx シート3 R33・R35・R36（★Schedule+ConsoleCommand・★Notification facade・★PHP Enum）／シート7 R67〜R69（★通知一覧画面・★既読化・★日次バッチ処理）／シート9 R13（★ReadingPlanSeeder、二重シナリオ）／シート10 R30（★リマインダーバッチ テスト観点）／シート11 DR10（★通知データ要件）／シート12（notificationsテーブル） ／ Bladeモック `Preparedblade-mockcase-BookShelf` advancedブランチ実測（`notifications/index.blade.php`・`layouts/navigation.blade.php` の変数契約、面談準備資料「advanced Blade契約サマリ」PG17・通知ベルバッジ）
docs/02_発注書/発注書_12_通知バッチ.md-7-**対の検品表**: `docs/03_検品表/12_通知バッチ.md`
docs/02_発注書/発注書_12_通知バッチ.md-8-**前提**: 走行①〜⑪が合格・確定済み。発注書11で `reading_plans` テーブル・0:00の自動失効バッチ（`reading-plans:expire`）が実装済みであること。
docs/02_発注書/発注書_12_通知バッチ.md-9-
--
docs/02_発注書/発注書_12_通知バッチ.md-13-
docs/02_発注書/発注書_12_通知バッチ.md-14-### やること
docs/02_発注書/発注書_12_通知バッチ.md-15-
docs/02_発注書/発注書_12_通知バッチ.md:16:1. `Bladeモック参照.md` の手順でadvancedブランチをcloneし、`resources/views/notifications/index.blade.php` をコピーする。
docs/02_発注書/発注書_12_通知バッチ.md-17-2. **最終ステップとして** `resources/views/layouts/navigation.blade.php` をコピーする（決定台帳D4参照。本走行の完了時点で `reports.index`・`reading-plans.index`・`notifications.index` の3ルートが揃うため、このタイミングで初めて安全に移入できる）。
docs/02_発注書/発注書_12_通知バッチ.md-18-3. `php artisan notifications:table` によるnotificationsテーブルのマイグレーション発行・実行。
docs/02_発注書/発注書_12_通知バッチ.md-19-4. `User` モデルへの `Notifiable` トレイトの追加。
--
docs/02_発注書/発注書_12_通知バッチ.md-228-## 6. ナビゲーション移入（本走行の最終ステップ）
docs/02_発注書/発注書_12_通知バッチ.md-229-
docs/02_発注書/発注書_12_通知バッチ.md-230-```bash
docs/02_発注書/発注書_12_通知バッチ.md:231:# Bladeモック参照.md の手順でadvancedブランチをcloneした上で実行する
docs/02_発注書/発注書_12_通知バッチ.md-232-cp /tmp/blade-advanced/resources/views/notifications/index.blade.php resources/views/notifications/index.blade.php
docs/02_発注書/発注書_12_通知バッチ.md-233-cp /tmp/blade-advanced/resources/views/layouts/navigation.blade.php resources/views/layouts/navigation.blade.php
docs/02_発注書/発注書_12_通知バッチ.md-234-```
--
docs/02_発注書/発注書_14_応用テスト.md-107-## 4. テストの方式（正本確定値）
docs/02_発注書/発注書_14_応用テスト.md-108-
docs/02_発注書/発注書_14_応用テスト.md-109-- カバレッジ: 合格条件はカバレッジの数値ではなく、説明のつかない未カバー行がゼロであること（CLAUDE.md 13-A-4）。アプリが使う機能の未カバー行はすべてテストで潰す。残してよいのは除外リストに理由付きで載せた行のみ。`sail artisan test --coverage` の出力は中略せず全クラスの行を採取する。
docs/02_発注書/発注書_14_応用テスト.md:110:- 外部APIテスト（ISBN検索）: `Illuminate\Support\Facades\Http` の `Http::fake()` でモック化し、実際のGoogle Books APIを呼ばない。
docs/02_発注書/発注書_14_応用テスト.md-111-- ISBN検索のエラー応答キーは `message` で統一済み。テストはエラー応答を `message` キーで確認する（422「ISBNは13桁の数字で入力してください」／404「該当する書籍が見つかりませんでした」／502「書籍情報の取得に失敗しました」）。
docs/02_発注書/発注書_14_応用テスト.md-112-- Sanctum認証テストは `Laravel\Sanctum\Sanctum::actingAs($user)` を使用する。
docs/02_発注書/発注書_14_応用テスト.md-113-- 自動失効バッチ・リマインダーバッチのテストは、`Carbon::setTestNow()` またはシード済みデータを用い、実行タイミングに依存しない形で書く。
--
docs/02_発注書/発注書_15_エラー画面日本語化.md-82-
docs/02_発注書/発注書_15_エラー画面日本語化.md-83-### 2-1. errorsビューの配置
docs/02_発注書/発注書_15_エラー画面日本語化.md-84-
docs/02_発注書/発注書_15_エラー画面日本語化.md:85:`resources/views/errors/` を作成し、403・404・419・500・503 の5ファイルを配置する。各ファイルは §1-5 の見出し・本文を表示し、§1-6 の通り独立HTMLとする。スタイルは既存モックのTailwindCSSのトーンに合わせ、中央寄せの1カラムとする。CSSは `@vite(['resources/css/app.css'])` を読み込んでよい（これはビルド済みCSSの静的読み込みでありAuth・DB依存を持たない）。
docs/02_発注書/発注書_15_エラー画面日本語化.md-86-
docs/02_発注書/発注書_15_エラー画面日本語化.md-87-### 2-2. Handler への Web 向け例外変換の追加
docs/02_発注書/発注書_15_エラー画面日本語化.md-88-
--
docs/02_発注書/発注書_15_エラー画面日本語化.md-146-</html>
docs/02_発注書/発注書_15_エラー画面日本語化.md-147-```
docs/02_発注書/発注書_15_エラー画面日本語化.md-148-
docs/02_発注書/発注書_15_エラー画面日本語化.md:149:`@vite` の対象・CSSクラスは既存モックの記法に合わせる。
docs/02_発注書/発注書_15_エラー画面日本語化.md-150-
docs/02_発注書/発注書_15_エラー画面日本語化.md-151----
docs/02_発注書/発注書_15_エラー画面日本語化.md-152-
--
docs/03_検品表/01_土台認証.md:1:status: patched (H-2は提出版で反映済み／G-1・G-11はデザインUI優先例外に合わせて今回修正)
docs/03_検品表/01_土台認証.md-2-
docs/03_検品表/01_土台認証.md-3-# 検品表 01 — 土台＋認証
docs/03_検品表/01_土台認証.md-4-
--
docs/03_検品表/01_土台認証.md-177-| G-8 | 登録後、`users` テーブルにレコードが1件増えており、`password` が平文でなくハッシュ値である | `sail exec mysql mysql -usail -ppassword laravel -e "SELECT id,name,email,LEFT(password,7) FROM users;"` | 差し戻し |
docs/03_検品表/01_土台認証.md-178-| G-9 | 登録直後にログイン状態になっている（自動ログイン） | `/login` にアクセスすると `302` `/books` に飛ばされる | 差し戻し |
docs/03_検品表/01_土台認証.md-179-| G-10 | 同じメールアドレスで再度登録すると「このメールアドレスは既に登録されています」が表示される | 実機操作（一度ログアウトしてから） | 差し戻し |
docs/03_検品表/01_土台認証.md:180:| G-11 | `/login` が崩れずに表示され、「メールアドレス」「パスワード」の2項目と「ログイン」ボタンがある（**会員登録ボタン・リンクは表示されない**） | ブラウザで `http://localhost/login` | 差し戻し |
docs/03_検品表/01_土台認証.md-181-| G-12 | 誤ったパスワードでログインするとログイン画面に戻り、**「メールアドレスまたはパスワードが正しくありません」** が表示される（英語メッセージでない） | 実機操作 | 差し戻し |
docs/03_検品表/01_土台認証.md-182-| G-13 | 正しいメール・パスワードでログインすると `302` が返り、`Location` ヘッダが `/books` である | 開発者ツール Network タブ | 差し戻し |
docs/03_検品表/01_土台認証.md-183-| G-14 | ログイン済みの状態で `/register` にアクセスすると `302` `/books` にリダイレクトされる | 実機操作 | 差し戻し |
--
docs/03_検品表/01_土台認証.md-190-| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
docs/03_検品表/01_土台認証.md-191-|---|---|---|---|
docs/03_検品表/01_土台認証.md-192-| H-1 | `sail bin pint --test` が `No fixable issues were found` を出力する | コマンド出力 | 差し戻し |
docs/03_検品表/01_土台認証.md:193:| H-2 | Bladeモック（`resources/views/`）が frozen 宣言後に改変されていない | モック basic ブランチを一時ディレクトリへ再clone → `diff -r <一時clone>/resources/views resources/views` の出力が空（`docs/03_検品表/検品表01_H2修正パッチ.md` により差し替え） | **停止**（モックは frozen。改変は正本違反） |
docs/03_検品表/01_土台認証.md-194-| H-3 | `composer.json` に `laravel-lang/*` 系パッケージが入っていない | `grep -n "laravel-lang" composer.json` が0件 | **停止**（シート4の明示的禁止） |
docs/03_検品表/01_土台認証.md-195-| H-4 | 設定値（DB接続情報・パスワード等）がコードに直書きされていない | 追加・変更したファイルの目視 | 差し戻し |
docs/03_検品表/01_土台認証.md-196-| H-5 | クラス名がアッパーキャメル、メソッド・変数が camelCase、マイグレーションファイル名が snake_case、テーブル名が snake_case 複数形（`book_genre` は Laravel 規約のアルファベット順単数形連結のため例外的に正しい） | 追加ファイルの目視 | 差し戻し |
--
docs/03_検品表/01_土台認証.md-229-## 変更履歴（今回のまとめ出力時に確認したこと）
docs/03_検品表/01_土台認証.md-230-
docs/03_検品表/01_土台認証.md-231-- **H-2**: `検品表01_H2修正パッチ.md`の内容は提出版に既に反映済みだった（差し戻し不要）。
docs/03_検品表/01_土台認証.md:232:- **G-1・G-11（今回新たに修正）**: `決定記録_デザインUI優先例外.md`の項目4（ログイン画面の会員登録ボタン削除）・項目5（会員登録画面のログイン導線をテキストリンク化）と矛盾していたため、判定条件を実装後の正しい状態に合わせて更新した。この2行は元々「モックとデザインUIの相違」を扱う趣旨の行ではなく、単に走行①時点のBladeモック契約をそのまま条件文にしていたため、後発の例外（走行④以降の実機検品で確定）が反映されずに取り残されていた。
--
docs/03_検品表/02_書籍CRUD.md-81-| F-5 | お気に入りボタンが非表示になっている | 実機 | 差し戻し |
docs/03_検品表/02_書籍CRUD.md-82-| F-6 | 新規レビュー投稿フォームが非表示で「削除済みの書籍にはレビューを投稿できません」に差し替わっている | 実機 | 差し戻し |
docs/03_検品表/02_書籍CRUD.md-83-| F-7 | 登録者本人にのみ「復元する」ボタンが表示される（別ユーザーでは出ない） | 実機（2ユーザーで確認） | 差し戻し |
docs/03_検品表/02_書籍CRUD.md:84:| F-8 | §8-1に明記した4点以外、`books/show.blade.php` の既存マークアップ・クラス名・レイアウトが変更されていない | モックリポジトリ(basic)を一時cloneし、変更箇所を目視で4点に限定できるか確認 | 差し戻し |
docs/03_検品表/02_書籍CRUD.md-85-
docs/03_検品表/02_書籍CRUD.md-86-## G. 復元（restore）
docs/03_検品表/02_書籍CRUD.md-87-
--
docs/04_検品結果/01_土台認証_結果.md-53-
docs/04_検品結果/01_土台認証_結果.md-54-$ sail bin pint --test                            → PASS（68 files）
docs/04_検品結果/01_土台認証_結果.md-55-
docs/04_検品結果/01_土台認証_結果.md:56:# H-2 新方式（モック正本突合）
docs/04_検品結果/01_土台認証_結果.md:57:$ git clone --depth 1 -b basic <モックリポジトリ> /tmp/mock-basic-check
docs/04_検品結果/01_土台認証_結果.md-58-$ diff -r /tmp/mock-basic-check/resources/views resources/views  → 出力なし・RC=0
docs/04_検品結果/01_土台認証_結果.md-59-  （views数 mock/本番 = 31/31 完全一致）
docs/04_検品結果/01_土台認証_結果.md-60-```
--
docs/04_検品結果/01_土台認証_結果.md-184-| No. | 判定 | 根拠 |
docs/04_検品結果/01_土台認証_結果.md-185-|---|---|---|
docs/04_検品結果/01_土台認証_結果.md-186-| H-1 | YES | `sail bin pint --test` = PASS（68 files、指摘0件）。Pint v1.x は clean 時「PASS」表示（CLAUDE.md §4 で版差を許容） |
docs/04_検品結果/01_土台認証_結果.md:187:| **H-2** | **YES** | **【新方式】** モック basic ブランチを再cloneし `diff -r <clone>/resources/views resources/views` → 出力なし・RC=0（31/31ファイル完全一致）。frozen 正本と直接突合し、Bladeモックの改変なしを確認 |
docs/04_検品結果/01_土台認証_結果.md-188-| H-3 | YES | composer.json に laravel-lang 0件 |
docs/04_検品結果/01_土台認証_結果.md-189-| H-4 | YES | 追加/変更ファイルにDB接続情報・パスワード等の直書きなし |
docs/04_検品結果/01_土台認証_結果.md-190-| H-5 | YES | クラス=PascalCase、メソッド/変数=camelCase、マイグレ名=snake_case、テーブル=snake_case複数形、`book_genre` は規約どおり |
--
docs/04_検品結果/01_土台認証_結果.md-202-## H-2 検証方法の差し替えについて（記録）
docs/04_検品結果/01_土台認証_結果.md-203-
docs/04_検品結果/01_土台認証_結果.md-204-- 旧 H-2 は `git diff main --stat -- resources/` を検証手段としていたが、`検品表01_H2修正パッチ.md` により差し替えられた。
docs/04_検品結果/01_土台認証_結果.md:205:- 差し替え理由: (1) `resources/views/` は untracked のため git 差分ではモック改変を検知できない（偽陰性）、(2) ビルドエントリポイント（app.css/app.js）の環境設定変更をモック改変と誤検知する（偽陽性・初回検品で実際に発生）。
docs/04_検品結果/01_土台認証_結果.md:206:- 新方式は frozen 正本（`Preparedblade-mockcase-BookShelf` basic ブランチ）との `diff -r` 直接突合。本再検品で **RC=0・31ファイル完全一致** を確認し、偽陽性/偽陰性なく H-2=YES を確定した。
docs/04_検品結果/01_土台認証_結果.md:207:- （合否非該当の補足）`resources/js/bootstrap.js` は main の `c2eaa2c` が削除済みで feature branch 側に残るブランチ間 divergence があるが、Bladeモック本体ではないため新 H-2 の対象外。マージ前に feature を最新 main へ rebase/merge すれば解消する。
docs/04_検品結果/01_土台認証_結果.md-208-
docs/04_検品結果/01_土台認証_結果.md-209-## 経緯（本結果に至るまで）
docs/04_検品結果/01_土台認証_結果.md-210-
docs/04_検品結果/01_土台認証_結果.md-211-1. **初回検品（`c40b3b7`）**: YES 88 / NO 2 で不合格。NO は H-2（旧方式で resources 差分検知）と、`Fortify::authenticateUsing()` の発注書§3-1(d)/§177 逸脱（所見）。
docs/04_検品結果/01_土台認証_結果.md-212-2. **差し戻し対応（`e07e926`）**: `boot()` から authenticateUsing クロージャと専用 import（User/Hash/Validator）を除去し Fortify 標準ログインへ復帰。メール形式不正時も `failed`（メールアドレスまたはパスワードが正しくありません）に一本化。`grep authenticateUsing`=0、`app/Http/Requests/` 不在を確認（CLAUDE.md §15 完了条件充足）。
docs/04_検品結果/01_土台認証_結果.md:213:3. **H-2 裁定＋検証方法差し替え**: 片倉が Tailwind/Alpine 環境を main へコミット（`c2eaa2c`）。さらに `検品表01_H2修正パッチ.md` で H-2 をモック正本突合方式へ変更。本再検品で新方式 YES を確定。
docs/04_検品結果/01_土台認証_結果.md-214-
docs/04_検品結果/01_土台認証_結果.md-215-## 結論
docs/04_検品結果/01_土台認証_結果.md-216-
--
docs/04_検品結果/2026-09-27_ISBN検索取得項目調査.md-293-
docs/04_検品結果/2026-09-27_ISBN検索取得項目調査.md-294-事実：出版日入力欄は `<input type="date" id="published_date">`。HTMLの date 型は YYYY-MM-DD 形式の値のみ受け付け、"2012" や "2012-06" を .value に代入しても表示されない。JS line83 の正規表現ガードはこのdate型入力の制約と整合する形になっている。
docs/04_検品結果/2026-09-27_ISBN検索取得項目調査.md-295-
docs/04_検品結果/2026-09-27_ISBN検索取得項目調査.md:296:#### 2-4. resources/ の変更確認（フロントは提供済みモック・本件で変更していないことの確認）
docs/04_検品結果/2026-09-27_ISBN検索取得項目調査.md-297-
docs/04_検品結果/2026-09-27_ISBN検索取得項目調査.md-298-実行コマンド：
docs/04_検品結果/2026-09-27_ISBN検索取得項目調査.md-299-```
--
docs/04_検品結果/2026-09-27_シード要件と読書レポート閲覧条件調査.md-194-## 未確認・保留
docs/04_検品結果/2026-09-27_シード要件と読書レポート閲覧条件調査.md-195-- DB実データ（seed 実行後の users.id・yamada の実レビュー件数・reviews 総件数）は未確認。権限境界により artisan migrate/db:seed 等の状態変更コマンドを実行していないため、シーダーコードからの静的確認にとどめた。
docs/04_検品結果/2026-09-27_シード要件と読書レポート閲覧条件調査.md-196-- シート9 D10（6本列挙）と 発注書11 L351（ReadingPlanSeeder 追加で7本）の食い違いを最終的にどちらの列挙で採点するかは、判定側（片倉／チャット）の裁定事項として保留。
docs/04_検品結果/2026-09-27_シード要件と読書レポート閲覧条件調査.md:197:- 要件シート内の画像（xl/media/*.png、シート5画面設計・シート6デザインUI）は本調査で画像内容を確認していない。読書レポートの「専用デザイン」有無はシート7テキスト・reports.md・Blade 実測で確認した範囲。
docs/04_検品結果/2026-09-27_シード要件と読書レポート閲覧条件調査.md-198-
docs/04_検品結果/2026-09-27_シード要件と読書レポート閲覧条件調査.md-199-## worktree情報
docs/04_検品結果/2026-09-27_シード要件と読書レポート閲覧条件調査.md-200-- ブランチ名：fix/book-search-and-isbn-ui
--
docs/04_検品結果/実機検品_UI修正_結果.md-1-status: fixed
docs/04_検品結果/実機検品_UI修正_結果.md-2-
docs/04_検品結果/実機検品_UI修正_結果.md:3:# 実機検品 UI修正 結果（デザインUI優先の6項目）
docs/04_検品結果/実機検品_UI修正_結果.md-4-
docs/04_検品結果/実機検品_UI修正_結果.md-5-**実施日**: 2026-09-03
docs/04_検品結果/実機検品_UI修正_結果.md-6-**ブランチ**: `fix/design-ui-corrections`
docs/04_検品結果/実機検品_UI修正_結果.md-7-**対象**: 実機検品で片倉が指摘したUI 6項目
docs/04_検品結果/実機検品_UI修正_結果.md:8:**判定の前提（重要）**: 本修正は §0 の正本優先順位（1位=frozen Bladeモック ＞ 2位=要件シート/デザインUI）に対する**例外的上書き**である。指摘6項目はすべて frozen Bladeモックと一致しデザインUI（シート6画像）と相違していたため、片倉の裁定「デザインUI優先で修正」に基づき Bladeモックを上書きした。判定はチャット側で行う。
docs/04_検品結果/実機検品_UI修正_結果.md-9-
docs/04_検品結果/実機検品_UI修正_結果.md-10----
docs/04_検品結果/実機検品_UI修正_結果.md-11-
docs/04_検品結果/実機検品_UI修正_結果.md-12-## 0. 正本の裁定（この修正の根拠）
docs/04_検品結果/実機検品_UI修正_結果.md-13-
docs/04_検品結果/実機検品_UI修正_結果.md:14:- 調査の結果、指摘6項目の現行実装は frozen Bladeモック（basicブランチ）と**バイト一致**、デザインUI（要件シート シート6の画面画像）とは**相違**していた。
docs/04_検品結果/実機検品_UI修正_結果.md:15:- §0 のルール上は Bladeモック（1位）が勝つため、本来は QUESTIONS.md へ退避して停止する事案。
docs/04_検品結果/実機検品_UI修正_結果.md:16:- 片倉へ確認し、**「デザインUI優先で修正（Bladeモックを上書き）」**の裁定を得た（AskUserQuestion）。書籍説明は**全11冊を書籍内容に忠実に書き直す**裁定。
docs/04_検品結果/実機検品_UI修正_結果.md-17-- 以降の変更はこの裁定に基づく。
docs/04_検品結果/実機検品_UI修正_結果.md-18-
docs/04_検品結果/実機検品_UI修正_結果.md-19----
docs/04_検品結果/実機検品_UI修正_結果.md-20-
docs/04_検品結果/実機検品_UI修正_結果.md-21-## 1. 書籍一覧：レビュー（★評価）を削除
docs/04_検品結果/実機検品_UI修正_結果.md-22-
docs/04_検品結果/実機検品_UI修正_結果.md:23:デザインUI（シート6・書籍一覧画像）にカード上の★評価表示は存在しない。
docs/04_検品結果/実機検品_UI修正_結果.md-24-
docs/04_検品結果/実機検品_UI修正_結果.md-25-- View: `resources/views/books/index.blade.php` から `reviews_avg_rating` の★☆ブロックを削除。
docs/04_検品結果/実機検品_UI修正_結果.md-26-- Controller: `app/Http/Controllers/BookController.php::index` の未使用となった `->withAvg('reviews', 'rating')` を削除。
--
docs/04_検品結果/実機検品_UI修正_結果.md-34-## 2. 書籍一覧：文字を太く（preference）
docs/04_検品結果/実機検品_UI修正_結果.md-35-
docs/04_検品結果/実機検品_UI修正_結果.md-36-- `h3` タイトルを `font-bold` → `font-extrabold`、著者を `font-medium` に変更。
docs/04_検品結果/実機検品_UI修正_結果.md:37:- 注記: デザインUIのタイトルはむしろ semibold 相当で現行 `font-bold` より軽い。本項目はデザインUI準拠ではなく片倉の主観指定（「もう少し太い」）に基づく調整のため、太さの程度は再実機で要確認。
docs/04_検品結果/実機検品_UI修正_結果.md-38-
docs/04_検品結果/実機検品_UI修正_結果.md-39-証拠:
docs/04_検品結果/実機検品_UI修正_結果.md-40-```
--
docs/04_検品結果/実機検品_UI修正_結果.md-44-
docs/04_検品結果/実機検品_UI修正_結果.md-45-## 3. 書籍一覧：ページネーションの色味・位置
docs/04_検品結果/実機検品_UI修正_結果.md-46-
docs/04_検品結果/実機検品_UI修正_結果.md:47:- デザインUIは「Showing 1 to 10 of 11 results」＋枠付き数字を**左下にまとめて**配置。デフォルトのTailwindページネータは `justify-between` で左右に分離していた。
docs/04_検品結果/実機検品_UI修正_結果.md-48-- `php artisan vendor:publish --tag=laravel-pagination` で公開し、`resources/views/vendor/pagination/tailwind.blade.php` を修正:
docs/04_検品結果/実機検品_UI修正_結果.md-49-  - 外側 `<nav>`: `justify-between` → `justify-start`
docs/04_検品結果/実機検品_UI修正_結果.md-50-  - デスクトップ用 `<div>`: `sm:flex-1 ... sm:justify-between` → `sm:flex sm:items-center sm:justify-start sm:gap-4`
docs/04_検品結果/実機検品_UI修正_結果.md:51:- 色味はデフォルトのグレー基調（`text-gray-*` / `border-gray-300`）でデザインUIと同系。青系は不使用。
docs/04_検品結果/実機検品_UI修正_結果.md-52-
docs/04_検品結果/実機検品_UI修正_結果.md-53-証拠（実機HTML・左寄せグルーピング）:
docs/04_検品結果/実機検品_UI修正_結果.md-54-```
--
docs/04_検品結果/実機検品_UI修正_結果.md-57-sm:gap-4
docs/04_検品結果/実機検品_UI修正_結果.md-58-```
docs/04_検品結果/実機検品_UI修正_結果.md-59-
docs/04_検品結果/実機検品_UI修正_結果.md:60:## 4. ログイン画面：会員登録ボタンを削除
docs/04_検品結果/実機検品_UI修正_結果.md-61-
docs/04_検品結果/実機検品_UI修正_結果.md:62:デザインUI（シート6・ログイン画像）は「ログイン」ボタンのみ。
docs/04_検品結果/実機検品_UI修正_結果.md-63-
docs/04_検品結果/実機検品_UI修正_結果.md:64:- `resources/views/auth/login.blade.php` から `会員登録` リンク（`route('register')` ボタン）を削除。ログインボタンのみ右寄せで残す。
docs/04_検品結果/実機検品_UI修正_結果.md-65-
docs/04_検品結果/実機検品_UI修正_結果.md-66-証拠:
docs/04_検品結果/実機検品_UI修正_結果.md-67-```
--
docs/04_検品結果/実機検品_UI修正_結果.md-71-
docs/04_検品結果/実機検品_UI修正_結果.md-72-## 5. 会員登録画面：ログインボタン → 「アカウントをお持ちの方」リンク
docs/04_検品結果/実機検品_UI修正_結果.md-73-
docs/04_検品結果/実機検品_UI修正_結果.md:74:デザインUI（シート6・会員登録画像）は下線付きテキストリンク「アカウントをお持ちの方」＋登録ボタン。
docs/04_検品結果/実機検品_UI修正_結果.md-75-
docs/04_検品結果/実機検品_UI修正_結果.md-76-- `resources/views/auth/register.blade.php` の `route('login')` へのボタン風リンクを、テキスト `アカウントをお持ちの方`・クラス `text-sm text-gray-600 underline hover:text-gray-900` の**リンク**に変更。
docs/04_検品結果/実機検品_UI修正_結果.md-77-
--
docs/04_検品結果/実機検品_UI修正_結果.md-107-## 7. 書籍詳細：説明文を全11冊 書き直し（①は画像通り）
docs/04_検品結果/実機検品_UI修正_結果.md-108-
docs/04_検品結果/実機検品_UI修正_結果.md-109-- `database/seeders/BookSeeder.php` の11冊の `description` を各書籍の内容に忠実な文へ書き直し。
docs/04_検品結果/実機検品_UI修正_結果.md:110:- ①「吾輩は猫である」はデザインUI（シート6・書籍詳細画像）の文言と一言一句同一にした。
docs/04_検品結果/実機検品_UI修正_結果.md-111-- 反映のため `migrate:fresh --seed` を実行（`firstOrCreate` はISBN既存時に更新しないため）。
docs/04_検品結果/実機検品_UI修正_結果.md-112-
docs/04_検品結果/実機検品_UI修正_結果.md-113-証拠（①の実データ）:
--
docs/04_検品結果/実機検品_UI修正_結果.md-115-$ sail artisan tinker --execute="echo App\Models\Book::find(1)->description;"
docs/04_検品結果/実機検品_UI修正_結果.md-116-中学校の英語教師である珍野苦沙弥の家に飼われている猫である「吾輩」の視点から、珍野一家や、そこに出入りする人々の様子を風刺的に描いた作品。
docs/04_検品結果/実機検品_UI修正_結果.md-117-```
docs/04_検品結果/実機検品_UI修正_結果.md:118:（デザインUI画像の①説明文と同一）
docs/04_検品結果/実機検品_UI修正_結果.md-119-
docs/04_検品結果/実機検品_UI修正_結果.md-120-全11件の書き直し後の値:
docs/04_検品結果/実機検品_UI修正_結果.md-121-```
--
docs/04_検品結果/実機検品_UI修正_結果.md-137-
docs/04_検品結果/実機検品_UI修正_結果.md-138-## 8. 付随して修復したフロントエンドビルドの回帰（要報告）
docs/04_検品結果/実機検品_UI修正_結果.md-139-
docs/04_検品結果/実機検品_UI修正_結果.md:140:アセット再ビルド時に、指摘とは別の**既存の環境回帰**が発覚したため修復した（デザインUIを実機へ反映するには再ビルドが必須のため）。
docs/04_検品結果/実機検品_UI修正_結果.md-141-
docs/04_検品結果/実機検品_UI修正_結果.md-142-- `resources/css/app.css` が **0バイト**（`@tailwind` ディレクティブ消失）→ ビルドがCSSを生成できていなかった。
docs/04_検品結果/実機検品_UI修正_結果.md-143-- `resources/js/app.js` が `import './bootstrap'` のみで、`resources/js/bootstrap.js` は**存在せず**ビルドが失敗（`Could not resolve "./bootstrap"`）。Alpine起動コードも消失。
docs/04_検品結果/実機検品_UI修正_結果.md:144:- 修復方針: frozen Bladeモック（basicブランチ）の frontend 構成に一致させた。
docs/04_検品結果/実機検品_UI修正_結果.md:145:  - `app.css` → `@tailwind base; @tailwind components; @tailwind utilities;`（モックと同一）
docs/04_検品結果/実機検品_UI修正_結果.md:146:  - `app.js` → `import Alpine ...; window.Alpine = Alpine; Alpine.start();`（モックと同一・bootstrap非依存）
docs/04_検品結果/実機検品_UI修正_結果.md:147:  - 未使用の `bootstrap.js`（今回一時作成したもの）は削除（モックに存在しないため）。
docs/04_検品結果/実機検品_UI修正_結果.md-148-- 再ビルド結果:
docs/04_検品結果/実機検品_UI修正_結果.md-149-```
docs/04_検品結果/実機検品_UI修正_結果.md-150-$ sail npm run build
--
docs/04_検品結果/実機検品_UI修正_結果.md-171- M database/seeders/BookSeeder.php
docs/04_検品結果/実機検品_UI修正_結果.md-172- M resources/css/app.css
docs/04_検品結果/実機検品_UI修正_結果.md-173- M resources/js/app.js
docs/04_検品結果/実機検品_UI修正_結果.md:174: M resources/views/auth/login.blade.php
docs/04_検品結果/実機検品_UI修正_結果.md-175- M resources/views/auth/register.blade.php
docs/04_検品結果/実機検品_UI修正_結果.md-176- M resources/views/books/index.blade.php
docs/04_検品結果/実機検品_UI修正_結果.md-177- M resources/views/favorites/index.blade.php
--
docs/04_検品結果/実機検品_UI修正_結果.md-182-
docs/04_検品結果/実機検品_UI修正_結果.md-183-## 11. 追加修正：ページネーションの色（ダークモード自動発動の無効化）
docs/04_検品結果/実機検品_UI修正_結果.md-184-
docs/04_検品結果/実機検品_UI修正_結果.md:185:再実機で「アプリのページネーションがダーク色、モックはライト色」と指摘。原因は、Tailwindページネータの `dark:bg-gray-800` 等の `dark:` バリアントが、`tailwind.config.js` に `darkMode` 未設定（＝デフォルト `media`）のため、閲覧環境のOSダークモードで自動発動していたこと。モックはライト専用デザインで、アプリにダークモード切替も存在しない。
docs/04_検品結果/実機検品_UI修正_結果.md-186-
docs/04_検品結果/実機検品_UI修正_結果.md:187:- 修正: `tailwind.config.js` に `darkMode: 'class'` を追加。これにより全 `dark:` バリアント（ビュー全体で115箇所）は `.dark` クラス配下でのみ有効となり、`.dark` を付与しない本アプリでは常にライト表示になる（ページネーションに限らずアプリ全体がモックのライトデザインに一致）。
docs/04_検品結果/実機検品_UI修正_結果.md-188-
docs/04_検品結果/実機検品_UI修正_結果.md-189-証拠（再ビルド後CSS）:
docs/04_検品結果/実機検品_UI修正_結果.md-190-```
--
docs/04_検品結果/実機検品_UI修正_結果.md-192-0
docs/04_検品結果/実機検品_UI修正_結果.md-193-（＝OSダークモードによる自動ダーク配色は生成されない。dark:はすべて「.dark クラス」ゲート化）
docs/04_検品結果/実機検品_UI修正_結果.md-194-```
docs/04_検品結果/実機検品_UI修正_結果.md:195:ページネータのライト配色クラス（`bg-white` / `border-gray-300` / `text-gray-500`）がそのまま適用され、モック（ライト）と同系になる。
docs/04_検品結果/実機検品_UI修正_結果.md-196-
docs/04_検品結果/実機検品_UI修正_結果.md-197----
docs/04_検品結果/実機検品_UI修正_結果.md-198-
docs/04_検品結果/実機検品_UI修正_結果.md-199-## 10. 再実機検品の観点
docs/04_検品結果/実機検品_UI修正_結果.md-200-
docs/04_検品結果/実機検品_UI修正_結果.md-201-- 書籍一覧: ★評価が消えていること／タイトルの太さ（font-extrabold）が意図通りか／ページネーションが左下グルーピングか。
docs/04_検品結果/実機検品_UI修正_結果.md:202:- ログイン: 会員登録ボタンが無いこと。
docs/04_検品結果/実機検品_UI修正_結果.md-203-- 会員登録: 「アカウントをお持ちの方」リンクからログインへ遷移できること。
docs/04_検品結果/実機検品_UI修正_結果.md-204-- お気に入り一覧: 表紙画像クリックで詳細へ遷移すること。
docs/04_検品結果/実機検品_UI修正_結果.md:205:- 書籍詳細①: 説明文がデザインUI画像と一致すること。
docs/04_検品結果/実機検品_UI修正_結果.md-206-- 項目2（文字の太さ）は主観指定のため、程度の可否を要確認。
--
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-110-
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-111-grep コマンド（`docs/00_正本/`）：
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-112-```
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md:113:$ grep -rniE "isbn|Google Books|自動入力|volumes" docs/00_正本/Bladeモック参照.md
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-114-（下記「未確認・保留」に記載。要件シート.xlsx はバイナリのため grep 対象外）
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-115-```
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-116-
--
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-301-
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-302-## 未確認・保留
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-303-- 成功系（HTTP 200・実データで title/author/description/image_url/published_date が埋まる）の実疎通：実 API が keyless quota 超過で HTTP 429 を返すため、実データでの200応答を採取できなかった。②のコード上は 200 で該当5キーを組み立てる分岐が存在するが、実データでの200到達は本走行では未確認。
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md:304:- 該当0件（totalItems=0 → 404）の実疎通：同じく 429 で `failed()` が先に true になるため、totalItems=0 経路（404）を実データで踏めなかった。テスト（`tests/Feature/IsbnSearchTest.php` の Http::fake モック）側でのみ確認される範囲。
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-305-- ブラウザ実機での fetch → 各フィールド自動代入の目視確認は未実施（本走行はコード引用と tinker/curl の実疎通に限定）。
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-306-- `docs/00_正本/片倉_菖さん_新模擬案件_Bookshelf_要件シート (1).xlsx`：バイナリのため grep で内容を確認できていない。シート10 R25・シート8 R17 等の原文は未確認。
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md:307:- `docs/00_正本/Bladeモック参照.md` の ISBN検索記述有無は本ファイルでは grep 出力を貼っておらず未提示。
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-308-
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-309-## worktree情報
docs/04_検品結果/検証_ISBN自動入力_正本と実疎通.md-310-- ブランチ名：fix/book-search-and-isbn-ui
--
docs/04_検品結果/検証_要件シートISBN採点要件.md-49-name="シート3 開発プロセス"      r:id=rId3  -> sheet3.xml
docs/04_検品結果/検証_要件シートISBN採点要件.md-50-name="シート4 環境構築手順"      r:id=rId4  -> sheet4.xml
docs/04_検品結果/検証_要件シートISBN採点要件.md-51-name="シート5 画面設計"          r:id=rId5  -> sheet5.xml
docs/04_検品結果/検証_要件シートISBN採点要件.md:52:name="シート6 デザインUI"        r:id=rId6  -> sheet6.xml
docs/04_検品結果/検証_要件シートISBN採点要件.md-53-name="シート7 機能要件"          r:id=rId7  -> sheet7.xml
docs/04_検品結果/検証_要件シートISBN採点要件.md-54-name="シート8 バリデーションルール" r:id=rId8 -> sheet8.xml
docs/04_検品結果/検証_要件シートISBN採点要件.md-55-name="シート9 シーディング要件"  r:id=rId9  -> sheet9.xml
--
docs/04_検品結果/証拠_11_読書計画CRUD.md-648-```
docs/04_検品結果/証拠_11_読書計画CRUD.md-649-$ git log --oneline -- resources/views/layouts/navigation.blade.php
docs/04_検品結果/証拠_11_読書計画CRUD.md-650-78e739c feat: リマインダー通知＋7:00日次バッチ＋ナビ移入を実装 (発注書12)
docs/04_検品結果/証拠_11_読書計画CRUD.md:651:6a346be chore: 走行①のBladeモック・docs・CLAUDE.md等を確定
docs/04_検品結果/証拠_11_読書計画CRUD.md-652-```
docs/04_検品結果/証拠_11_読書計画CRUD.md-653-```
docs/04_検品結果/証拠_11_読書計画CRUD.md-654-$ for c in c9dcf13 2fe2f65 294d5f9; do git show --name-only --format="" $c | grep -c navigation.blade.php; done
--
docs/04_検品結果/証拠_⑪.md-670-```
docs/04_検品結果/証拠_⑪.md-671-（すべて A＝新規追加。既存Bladeの M（変更）なし。）
docs/04_検品結果/証拠_⑪.md-672-
docs/04_検品結果/証拠_⑪.md:673:移入3枚の advanced モック一致確認：
docs/04_検品結果/証拠_⑪.md-674-実行コマンド：
docs/04_検品結果/証拠_⑪.md-675-```
docs/04_検品結果/証拠_⑪.md-676-$ git clone --depth 1 -b advanced https://github.com/coachtech-prepared-file/Preparedblade-mockcase-BookShelf.git /tmp/blade-advanced
--
docs/04_検品結果/証拠_⑫.md-361-link /reports count=2
docs/04_検品結果/証拠_⑫.md-362-```
docs/04_検品結果/証拠_⑫.md-363-
docs/04_検品結果/証拠_⑫.md:364:### 移入ファイルとadvancedモックの一致
docs/04_検品結果/証拠_⑫.md-365-
docs/04_検品結果/証拠_⑫.md-366-```
docs/04_検品結果/証拠_⑫.md-367-$ diff /tmp/blade-advanced/resources/views/notifications/index.blade.php resources/views/notifications/index.blade.php
--
docs/04_検品結果/証拠_⑫.md-369-$ diff /tmp/blade-advanced/resources/views/layouts/navigation.blade.php resources/views/layouts/navigation.blade.php
docs/04_検品結果/証拠_⑫.md-370-EXIT_NAV=0
docs/04_検品結果/証拠_⑫.md-371-```
docs/04_検品結果/証拠_⑫.md:372:（差分なし＝byte一致。/tmp/blade-advanced は Bladeモック参照.md の advanced ブランチclone）
docs/04_検品結果/証拠_⑫.md-373-
docs/04_検品結果/証拠_⑫.md-374----
docs/04_検品結果/証拠_⑫.md-375-
--
docs/04_検品結果/証拠_修正再検品.md-187-```
docs/04_検品結果/証拠_修正再検品.md-188-b23ead2 fix: 削除済み書籍詳細のバナー・復元ボタン・投稿抑止を復元 (検品⑨ E-2 / 片倉裁定)
docs/04_検品結果/証拠_修正再検品.md-189-63ccbd8 feat: 書籍CRUD（一覧/詳細/登録/編集/論理削除/復元）を実装
docs/04_検品結果/証拠_修正再検品.md:190:6a346be chore: 走行①のBladeモック・docs・CLAUDE.md等を確定
docs/04_検品結果/証拠_修正再検品.md-191-
docs/04_検品結果/証拠_修正再検品.md-192-15:            @if ($book->trashed())
docs/04_検品結果/証拠_修正再検品.md-193-17:                    この本は削除されました
--
docs/04_検品結果/証拠_修正再検品.md-249-```
docs/04_検品結果/証拠_修正再検品.md-250-
docs/04_検品結果/証拠_修正再検品.md-251-### 参考（証拠として明記可）
docs/04_検品結果/証拠_修正再検品.md:252:advanced モック（frozen 正本#1）の show.blade にはこのバナーが無い。§9-3・BookCrudTest とモックの食い違いは片倉裁定により「バナー必須」で確定済み。現 show.blade はバナーを保持している。
docs/04_検品結果/証拠_修正再検品.md-253-
docs/04_検品結果/証拠_修正再検品.md-254----
docs/04_検品結果/証拠_修正再検品.md-255-
--
docs/04_検品結果/調査_エラー処理と乖離.md-74-c264da2 feat: ISBN検索とisbn/published_dateのnullable化を実装 (発注書08)
docs/04_検品結果/調査_エラー処理と乖離.md-75-c4fb811 feat: 公開API(v1) 5本とシーディング6本を実装
docs/04_検品結果/調査_エラー処理と乖離.md-76-63ccbd8 feat: 書籍CRUD（一覧/詳細/登録/編集/論理削除/復元）を実装
docs/04_検品結果/調査_エラー処理と乖離.md:77:6a346be chore: 走行①のBladeモック・docs・CLAUDE.md等を確定
docs/04_検品結果/調査_エラー処理と乖離.md-78-c40b3b7 feat: 土台構築とFortifyによるセッション認証を実装
docs/04_検品結果/調査_エラー処理と乖離.md-79-```
docs/04_検品結果/調査_エラー処理と乖離.md-80-
--
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-158-
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-159----
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-160-
docs/04_検品結果/調査_書籍一覧登録Blade現状.md:161:### 参考: 正本 Bladeモック参照.md
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-162-
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-163-実行コマンド：
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-164-```
docs/04_検品結果/調査_書籍一覧登録Blade現状.md:165:$ cat docs/00_正本/Bladeモック参照.md
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-166-```
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-167-出力（抜粋の生引用）：
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-168-```
--
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-170-https://github.com/coachtech-prepared-file/Preparedblade-mockcase-BookShelf.git
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-171-
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-172-## ブランチ
docs/04_検品結果/調査_書籍一覧登録Blade現状.md:173:| basic    | 基本機能の正本 | frozen（改変禁止） |
docs/04_検品結果/調査_書籍一覧登録Blade現状.md:174:| advanced | 応用機能の正本 | frozen（改変禁止） |
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-175-
docs/04_検品結果/調査_書籍一覧登録Blade現状.md:176:## frozen宣言
docs/04_検品結果/調査_書籍一覧登録Blade現状.md:177:両ブランチとも改変禁止。... Bladeモックが優先する（CLAUDE.md 0章の正本優先順位1位）。
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-178-```
docs/04_検品結果/調査_書籍一覧登録Blade現状.md:179:（モック本体はローカルに無く、GitHub の basic/advanced ブランチにある旨のみ記載。書籍一覧・登録モックの具体ファイル名の記述はこのファイルには無い。ローカルに clone された `blade-basic` / `blade-advanced` ディレクトリは確認範囲に存在しなかった。）
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-180-
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-181-## 未確認・保留
docs/04_検品結果/調査_書籍一覧登録Blade現状.md:182:- Bladeモック本体（basic/advanced ブランチ）はローカルに clone されておらず、モック側の index/create の実体（検索フォーム・ISBN自動入力セクションの有無）は本調査では直接参照できていない。片倉指摘の「デザイン（正しい姿）」との一字一致比較はモック本体の取得が必要。
docs/04_検品結果/調査_書籍一覧登録Blade現状.md:183:- `docs/00_正本/Bladeモック参照.md` には書籍一覧・登録モックの具体ファイルパスの記述が無いため、どのファイルが対応モックかは未確認。
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-184-
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-185-## worktree情報
docs/04_検品結果/調査_書籍一覧登録Blade現状.md-186-- ブランチ名：`feature/14-advanced-tests`
終了コード: 0
```
