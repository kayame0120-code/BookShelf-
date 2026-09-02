status: draft

# 検品表 02 — 書籍CRUD

**対象発注書**: `docs/02_発注書/02_書籍CRUD.md`
**出力先**: `docs/04_検品結果/02_書籍CRUD_結果.md`

## 検品セッションの実施条件

- 実装を行ったCCセッションとは別の、文脈ゼロの新規セッションで実施する。
- 検品セッションに渡してよいのは次の3点のみ: ①`CLAUDE.md`（自動読込） ②本検品表と対の発注書 ③実装済みコード。実装時の会話ログ・設計の経緯は渡さない。
- 全行に「判定（YES/NO）」と「根拠（コマンド出力の該当行、または画面で確認した内容）」を必ず添える。**根拠なしのYESは無効。**
- 1行でもNOがあれば走行②は不合格。走行③に進まない。

## 検品前に一度だけ実行するコマンド（出力を結果mdに貼る）

```bash
git branch --show-current
git log --oneline -5
sail artisan route:list --path=books
sail bin pint --test
```

---

## A. ルーティング

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| A-1 | `route:list` に `books.index` `books.create` `books.store` `books.show` `books.edit` `books.update` `books.destroy` `books.restore` の8本がすべて存在する | `sail artisan route:list --path=books` | 差し戻し |
| A-2 | `/` が `BookController@index` に束ねられ、名前が付いていない | `route:list` に `/` の行があり `Name` 列が空 | 差し戻し |
| A-3 | `/books/create` の定義が `/books/{book}` より前にある（`route:list`で`books.create`が404にならない） | `curl -I http://localhost/books/create`（未ログイン時は302で`/login`へ） | 差し戻し |
| A-4 | `books.show` と `books.restore` に `withTrashed()` が付いている | ソース確認（`grep withTrashed routes/web.php`） | 差し戻し |
| A-5 | `books.edit` `books.update` `books.destroy` に `withTrashed()` が**付いていない** | 同上 | 差し戻し |

## B. コントローラー・認証

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| B-1 | `books.index` `books.show` は未ログインで200が返る | `curl -I` | 差し戻し |
| B-2 | `books.create` `books.store` `books.edit` `books.update` `books.destroy` `books.restore` は未ログインで`/login`へ302リダイレクトされる | `curl -I` | 差し戻し |
| B-3 | 一覧・詳細画面でクエリが重複発行されていない（N+1がない） | `DB::listen`またはdebugbarでクエリ数確認。一覧はページあたり定数回 | 差し戻し |

## C. 登録（store）

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| C-1 | 全項目を正しく入力して登録すると `books.show` へ遷移し「書籍を登録しました」が表示される | 実機 | 差し戻し |
| C-2 | title/author/isbn/published_date 未入力、isbn不正（13桁でない）、isbn重複、genre未選択の各ケースで`back()`+`$errors`によりエラー文言が表示される | 実機（本書§4のメッセージと一致すること） | 差し戻し |
| C-3 | 選択したジャンルのみが `book_genre` に sync される | DB確認 | 差し戻し |
| C-4 | 登録は `user_id: Auth::id()` で保存される | DB確認 | 差し戻し |

## D. 編集（edit/update）

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| D-1 | 登録者本人はedit/updateを実行でき、更新後 `books.show` へ「書籍を更新しました」で遷移する | 実機 | 差し戻し |
| D-2 | 登録者本人以外がedit/updateにアクセスすると403 | 実機（別ユーザーでログイン） | 差し戻し |
| D-3 | ISBN一意性チェックは自身のレコードを除外する（同じISBNのまま更新してもエラーにならない） | 実機 | 差し戻し |
| D-4 | 更新時もジャンルの選択が `sync()` で反映される | DB確認 | 差し戻し |

## E. 削除（destroy）と論理削除

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| E-1 | 登録者本人が削除すると `books.index` へ「書籍を削除しました」で遷移する | 実機 | 差し戻し |
| E-2 | 登録者本人以外が削除しようとすると403 | 実機 | 差し戻し |
| E-3 | 削除後、`books` テーブルにレコードが物理的に残っており `deleted_at` が入っている（物理削除されていない） | `SELECT * FROM books WHERE id=? ` を`sail exec mysql`で直接確認 | 差し戻し |
| E-4 | 削除後、`reviews` / `favorites` / `book_genre` のレコードが保持されている（消えていない） | DB確認 | 差し戻し |
| E-5 | 削除済み書籍が一覧・ランキングから除外される | 実機 | 差し戻し |
| E-6 | 削除済み書籍のISBNで新規登録しようとすると一意性エラーになる（`withTrashed()`除外していない） | 実機 | 差し戻し |

## F. 削除済み書籍詳細ページ

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| F-1 | 削除済み書籍の詳細URLに直接アクセスしても404にならず表示される | 実機 | 差し戻し |
| F-2 | ページ上部に「この本は削除されました」バナーが表示される | 実機 | 差し戻し |
| F-3 | レビュー・評価一覧がそのまま表示される（本人以外の分も含む） | 実機 | 差し戻し |
| F-4 | 編集・削除ボタンが非表示になっている | 実機 | 差し戻し |
| F-5 | お気に入りボタンが非表示になっている | 実機 | 差し戻し |
| F-6 | 新規レビュー投稿フォームが非表示で「削除済みの書籍にはレビューを投稿できません」に差し替わっている | 実機 | 差し戻し |
| F-7 | 登録者本人にのみ「復元する」ボタンが表示される（別ユーザーでは出ない） | 実機（2ユーザーで確認） | 差し戻し |
| F-8 | §8-1に明記した4点以外、`books/show.blade.php` の既存マークアップ・クラス名・レイアウトが変更されていない | モックリポジトリ(basic)を一時cloneし、変更箇所を目視で4点に限定できるか確認 | 差し戻し |

## G. 復元（restore）

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| G-1 | 登録者本人が「復元する」を押すと通常状態に戻り、一覧・ランキングに再表示される | 実機 | 差し戻し |
| G-2 | 復元後、`deleted_at` が NULL に戻っている | DB確認 | 差し戻し |
| G-3 | 登録者本人以外が `PATCH /books/{book}/restore` に直接アクセスすると403になる | 実機（別ユーザーで直URL） | 差し戻し |
| G-4 | 未削除の書籍に対して `restore` を実行しても403になる（`BookPolicy::restore`が`trashed()`を条件に含めている） | 実機 | 差し戻し |

## H. コード品質・スコープ厳守

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| H-1 | `sail bin pint --test` が「No fixable issues were found」 | コマンド出力 | 差し戻し |
| H-2 | バリデーションが `StoreBookRequest` / `UpdateBookRequest` に分離されている（コントローラー内に`validate()`が書かれていない） | ソース確認 | 差し戻し |
| H-3 | 認可が `BookPolicy` に実装され、コントローラーで `$this->authorize()` が呼ばれている（コントローラー内で `if ($book->user_id !== Auth::id())` 等の手書き判定をしていない） | ソース確認 | 差し戻し |
| H-4 | 発注書に明記されていない独自ロジック（例: 独自の削除確認ステップ、発注書にないバリデーション項目、発注書にない画面）が追加されていない | ソース確認・実機 | 差し戻し |
| H-5 | migration が変更されていない（`git diff main -- database/migrations/` が空） | `git diff` | 差し戻し |
| H-6 | `QUESTIONS.md` に走行②由来の未解消行がない、または全て記録されている | `QUESTIONS.md` 確認 | 差し戻し（未記録なら追記させる） |
