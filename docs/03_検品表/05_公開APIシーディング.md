status: draft

# 検品表 05 — 公開API・シーディング

**対象発注書**: `docs/02_発注書/05_公開APIシーディング.md`
**出力先**: `docs/04_検品結果/05_公開APIシーディング_結果.md`

## 検品セッションの実施条件

- 実装を行ったCCセッションとは別の、文脈ゼロの新規セッションで実施する。
- 渡してよいのは①`CLAUDE.md`（自動読込） ②本検品表と対の発注書 ③実装済みコードの3点のみ。
- 全行に判定（YES/NO）と根拠を必ず添える。根拠なしのYESは無効。
- 1行でもNOがあれば走行⑤は不合格。走行⑥に進まない。

## 検品前に一度だけ実行するコマンド（出力を結果mdに貼る）

```bash
git branch --show-current
git log --oneline -5
sail artisan migrate:fresh --seed
sail exec mysql mysql -usail -ppassword laravel -e "SELECT COUNT(*) FROM users;"
sail exec mysql mysql -usail -ppassword laravel -e "SELECT COUNT(*) FROM genres;"
sail exec mysql mysql -usail -ppassword laravel -e "SELECT COUNT(*) FROM books;"
sail exec mysql mysql -usail -ppassword laravel -e "SELECT COUNT(*) FROM reviews;"
sail exec mysql mysql -usail -ppassword laravel -e "SELECT COUNT(*) FROM favorites;"
sail exec mysql mysql -usail -ppassword laravel -e "SELECT COUNT(*) FROM review_likes;"
sail artisan route:list --path=api
sail bin pint --test
```

---

## A. シーディング（採点直結）

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| A-1 | `sail artisan db:seed` がエラーなく完了する | コマンド出力 | 差し戻し |
| A-2 | users 5件・genres 10件・books 11件・reviews 32件が投入されている | COUNT結果 | 差し戻し |
| A-3 | `db:seed` を2回実行しても重複が発生しない（`firstOrCreate`/`syncWithoutDetaching`が使われている） | 2回実行してCOUNTが変わらないことを確認 | 差し戻し |
| A-4 | GenreSeederの投入順・書籍データ・ユーザーデータが要件シート シート9の記載と1文字違わず一致する | 発注書§8と実データを突合 | 差し戻し |
| A-5 | `id=3`の書籍が「リーダブルコード」、`genre_id=3`が「技術書」になっている | DB確認 | 差し戻し |
| A-6 | 各書籍に`description`と`image_url`（`https://placehold.co/200x300/e2e8f0/475569?text={番号}`形式）が設定されている | DB確認 | 差し戻し |
| A-7 | 各レビューのいいねが自分のレビューを除いて0〜3人分投入されている | DB確認（サンプル抽出） | 差し戻し |

## B. AP01 書籍一覧

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| B-1 | パラメータなしで200が返り、`per_page=10`のページネーションが既定で適用される | `curl` | 差し戻し |
| B-2 | `keyword`/`genre_id`/`page`/`per_page`のいずれかが不正な場合422が返る（エラー文言が発注書§3と一致） | `curl` | 差し戻し |
| B-3 | レスポンスに`description`が含まれない | `curl`のJSON確認 | 差し戻し |
| B-4 | `genres`/`average_rating`/`reviews_count`が各書籍に含まれる | 同上 | 差し戻し |
| B-5 | 該当0件でも`data: []`で200が返る | `curl` | 差し戻し |
| B-6 | ページ送りで検索条件が維持される（`withQueryString()`） | `curl`で`links`確認 | 差し戻し |

## C. AP02 書籍詳細

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| C-1 | 存在するIDで200が返り、`reviews`に投稿者名・評価・コメント・投稿日時が含まれる | `curl` | 差し戻し |
| C-2 | `comment`がnullのレビューを含んでも正しく返る（keyが落ちない） | `curl` | 差し戻し |
| C-3 | `created_at`がISO8601・`+09:00`オフセット付きで返る | `curl` | 差し戻し |
| C-4 | 存在しないIDで404、`{"message": "指定された書籍が見つかりません。"}`が返る（`errors`キーなし） | `curl` | 差し戻し |
| C-5 | 論理削除済みの書籍IDを指定すると404が返る | `curl`（書籍を削除してから確認） | 差し戻し |

## D. AP03 書籍登録

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| D-1 | 正しいデータで201が返り、レスポンスに`reviews`キーが含まれない | `curl` | 差し戻し |
| D-2 | レスポンスに`user_id`が含まれない | `curl` | 差し戻し |
| D-3 | バリデーション違反で422、`{"message": "入力内容に誤りがあります。", "errors": {...}}`形式で返る | `curl` | 差し戻し |
| D-4 | `genres`配列で指定したジャンルのみが`book_genre`にsyncされる | DB確認 | 差し戻し |

## E. AP04 書籍更新

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| E-1 | 正しいデータで200が返る | `curl` | 差し戻し |
| E-2 | ISBN一意性チェックが自身のレコードを除外する（同じISBNのまま更新してもエラーにならない） | `curl` | 差し戻し |
| E-3 | 存在しないIDで404、バリデーション違反で422が返る | `curl` | 差し戻し |

## F. AP05 書籍削除

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| F-1 | 204（本文なし）が返る | `curl -i` | 差し戻し |
| F-2 | 対象書籍が物理削除ではなく論理削除される（`deleted_at`が入る） | DB確認 | 差し戻し |
| F-3 | 存在しないIDで404が返る | `curl` | 差し戻し |
| F-4 | 削除後、`reviews`/`favorites`/`book_genre`のレコードが保持される | DB確認 | 差し戻し |

## G. コード品質・スコープ厳守

| No. | 判定条件（YES/NO） | 確認方法 | 違反時処置 |
|---|---|---|---|
| G-1 | `sail bin pint --test` が「No fixable issues were found」 | コマンド出力 | 差し戻し |
| G-2 | バリデーションが`Api\V1`名前空間のFormRequestに分離されている | ソース確認 | 差し戻し |
| G-3 | API側にPolicy（`$this->authorize()`）が1箇所も呼ばれていない | ソース確認 | 差し戻し |
| G-4 | `Api\V1\BookController`と`BookController`（Web版）が別クラスとして両方存在する | ソース確認 | 差し戻し |
| G-5 | 発注書に明記されていない独自ロジックが追加されていない | ソース確認 | 差し戻し |
| G-6 | migration が変更されていない（`personal_access_tokens`が作られていない） | `git diff main -- database/migrations/` | 差し戻し |
| G-7 | `QUESTIONS.md` に走行⑤由来の未解消行がない、または全て記録されている | `QUESTIONS.md` 確認 | 差し戻し |
