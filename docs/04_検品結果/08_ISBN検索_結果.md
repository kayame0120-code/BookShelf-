status: final

# 検品結果 08 — ISBN検索

**対象発注書**: `docs/02_発注書/08_ISBN検索.md`
**対象検品表**: `docs/03_検品表/08_ISBN検索.md`
**証拠ファイル**: `docs/04_検品結果/証拠_⑦_⑧.md`（追記反映版）
**対象ブランチ**: `feature/07-08-search-isbn`
**対象コミット**: `c264da2`（feat: ISBN検索とisbn/published_dateのnullable化を実装）
**前提コマンド確認**: `sail artisan migrate:fresh --seed` 正常完了／`sail bin pint --test` → `PASS ......... 108 files`

---

## A. ISBN検索・正常系

収集時点で本物のGoogle Books APIが`HTTP 429`を返したため、発注書08§3/§4の設計（採点はHttp::fake配下）に基づきHttp::fakeで代替確認。実API 429の生出力（`real API HTTP status=429 totalItems=(none)`）も併記され、代替の理由が明示されている。

| No. | 判定 | 根拠 |
|---|---|---|
| A-1 | YES（fake代替・理由明記済み） | `status=200`、`title`「リーダブルコード」・`author`「Dustin Boswell」・`published_date`「2012-06-23」等 |
| A-2 | YES | `keys=["title","author","description","image_url","published_date"]` |
| A-3 | YES | `author=山田太郎、田中花子、佐藤次郎` |

## B. エラー系・実装方式

| No. | 判定 | 根拠 |
|---|---|---|
| B-1 | YES | `status=422` / `body={"error":"ISBNは13桁の数字で入力してください"}` |
| B-2 | YES（fake代替・理由明記済み） | `status=404` / `body={"error":"該当する書籍が見つかりませんでした"}` |
| B-3 | YES | ソース全文：`ConnectionException`捕捉分岐・`$response->failed()`分岐とも502＋規定メッセージ。fakeでの両分岐実行結果も添付 |
| B-4 | YES | 禁止手段grep`(出現なし)`、`use Illuminate\Support\Facades\Http;`確認 |
| B-5 | YES | 有効/無効ISBNとも`HTTP 302 -> .../login` |
| B-6 | YES | `route:list`出力で`books.searchByIsbn`が`books.show`より前 |

## C. booksテーブルのnullable化

| No. | 判定 | 根拠 |
|---|---|---|
| C-1 | YES | `isbn rule: ["nullable",...]`、`Validator空ISBN/空出版日 fails=false`、`DB登録後: id=14 isbn=NULL published_date=NULL`（rollback済み） |
| C-2 | YES | C-1と同一証拠でpublished_date側も確認 |
| C-3 | YES | 既存書籍id1を`isbn=NULL published_date=NULL`に更新後rollbackで復元確認 |
| C-4 | YES | show.blade該当行提示のうえ、id1一時null化→実機表示`未登録`→復元 |
| C-5 | **YES** | 追加証拠：id1を一時null化しログイン後`/favorites`を実機表示。カード内`ISBN: 未登録`を確認。復元済み（`restored id1 isbn='9784101010014' pub=1905-01-01`） |
| C-6 | YES | `isbn=123 errors: ["ISBNは13桁の数字で入力してください（入力がある場合のみ）"]` |
| C-7 | YES | `重複ISBN=9784048930598 errors: ["このISBNは既に登録されています"]` |
| C-8 | YES | Bookモデル`casts`に`'published_date' => 'date'`、実機`出版日:</strong> 1905-01-01` |
| C-9 | YES | Store/Update rulesに`required`不含、messagesに`isbn.required`/`published_date.required`不存在（対象範囲のWeb側Request）。C-1のValidator `fails=false`と合わせ確認 |

## D. コード品質・スコープ厳守

| No. | 判定 | 根拠 |
|---|---|---|
| D-1 | YES | `PASS ......... 108 files` |
| D-2 | YES | BookController既存メソッド削除行なし、FormRequest差分は`required→nullable`のみ、Policy・他モデル変更なし |
| D-3 | **YES** | 追加証拠：`git diff main..HEAD -- app/ routes/ database/factories/ database/seeders/`の全文を確認。変更は`BookController`（searchByIsbn新設・index改修）・`StoreBookRequest`・`UpdateBookRequest`・`Book`モデル（`published_date`のcast追加）・`routes/web.php`の5点のみ。Bookモデルのcast追加は一見スコープ外に見えるが、QUESTIONS.md記載のとおり「発注書08§5に従い」追加されたものであり、明記範囲内。factories/seedersは無変更。予定外のロジックは確認されなかった |
| D-4 | YES | migration差分全文：`isbn`・`published_date`の`nullable()->change()`のみ |
| D-5 | YES | 自コミット分のresources差分は空。working tree上のadvanced Blade移入分は区別して明記 |
| D-6 | YES | QUESTIONS.md該当行を確認。走行⑧関連の未解消記録（published_date castの前提変化、07 B-2のシード不整合）が記録済み |

---

## 総合判定：**合格**

### ズレ台帳（判定に影響しないが記録のみ）
- Bookモデルへの`published_date`castが発注書08§5準拠で追加されたが、走行⑧のadvanced Blade移入により裁定前提（show.bladeの表示方法）が変化した点は、片倉さんの確認待ちとしてQUESTIONS.mdに残存。

### 保留
- A-1/A-2/A-3/B-2のGoogle Books実API確認は収集時点で429により未達。レート制限解除後、実API疎通を一度確認することを推奨（現状のfake代替は発注書§3/§4の設計上有効であり、合否は左右しない）。
