# 検品表 07 公開API

- 対象の発注書: `docs/02_発注書/発注書_07_公開API.md`（この番号で何を作るかを書いた実装指示書）
- 共通の規則: `docs/共通ルール.md` の2章（証拠を集める条件・判定語を書かない原則・判定の主体・期待値の基準・確認方法の書き方・差し戻し）
- 材料: 機能仕様書 7章（公開API）、全体仕様書 4章（No 005〜009・049）・8章（公開APIの一覧・エラー・呼び出し回数）、技術決定書 TD-07・TD-08・TD-11・TD-20
- 担当ルート: No 005〜009・049 ／ API: AP01〜AP05（画面は持たない）
- 補足: トークンを保存するテーブル `personal_access_tokens` の構造は 11 データの検品表で確認する
- 確認方法と期待値に出てくる `$` で始まる名前は、下の「確認に使うもの」の準備で値を入れた変数である。期待値はその変数の値に置き換えて照合する
- 行の見方: 「確認方法」を実行して得た生の結果が「期待値」と一致すれば YES、一致しなければ NO と判定する。判定は共通ルール2-3に従い、証拠を集めたセッションの外で行う

## 確認に使うもの

- 確認の前に `sail artisan migrate:fresh --seed` を実行し、初期データの直後の状態から始める。行は上から順に確認する（前の行の操作が後の行の前提になる）
- アプリにはトークンを発行する画面も処理も無い。検品用のトークンは tinker で発行する（手動の確認でコマンドを使って発行する方式）
- JSON の読み取りには `python3` を使う
- 呼び出し回数の上限を確かめる節は、他の節の呼び出しが数に入らないよう最後に置いている。直前に1分以上待ってから始める

```bash
export LC_ALL=C.UTF-8
BASE=http://localhost            # アプリのURL（Sail の既定値）
STAMP=$(date +%s)
G=/tmp/jarG; rm -f $G            # 一度もログインしない利用者の cookie 保存先
tk()    { sail artisan tinker --execute="$1"; }
uid()   { tk "echo App\Models\User::where('email','$1$STAMP@example.com')->value('id');"; }
token_from() { curl -s -c "$1" -b "$1" "$BASE$2" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"$//'; }
login_as() { curl -s -o /dev/null -c "$1" -b "$1" -X POST "$BASE/register" \
  --data-urlencode "_token=$(token_from "$1" /register)" --data-urlencode "name=検品会員$2" \
  --data-urlencode "email=$2$STAMP@example.com" --data-urlencode "password=password123" \
  --data-urlencode "password_confirmation=password123"; }
page()  { curl -s -c "$1" -b "$1" "$BASE$2"; }
has()   { page "$1" "$2" | grep -cF -- "$3"; }            # 本文に $3 を含む行の数
near()  { page "$1" "$2" | grep -B3 -A3 -F -- "$3"; }      # $3 の前後3行
st()    { curl -s -o /dev/null -w '%{http_code} %{redirect_url}\n' -c "$1" -b "$1" "$BASE$2"; }
# submit <cookie> <トークンを取る画面> <POST|PUT|PATCH|DELETE> <送信先> [項目=値 ...]
# 送信結果を "STATUS=… LOCATION=…" で出力し、移動先（移動が無ければ応答そのもの）の本文を /tmp/last.html に保存する
submit() {
  local jar=$1 form=$2 method=$3 action=$4; shift 4
  local args=(--data-urlencode "_token=$(token_from "$jar" "$form")" --data-urlencode "_method=$method") kv
  for kv in "$@"; do args+=(--data-urlencode "$kv"); done
  local res; res=$(curl -s -o /tmp/resp.html -w '%{http_code} %{redirect_url}' -c "$jar" -b "$jar" -e "$BASE$form" -X POST "$BASE$action" "${args[@]}")
  local loc=${res#* }
  echo "STATUS=${res%% *} LOCATION=$loc"
  if [ -n "$loc" ]; then curl -s -c "$jar" -b "$jar" "$loc" > /tmp/last.html; else cp /tmp/resp.html /tmp/last.html; fi
}
```

```bash
# api <メソッド> <パス> [トークン] [JSON本文] — 応答の本文を /tmp/api.json に保存し、ステータスを出力する
api() { local m=$1 p=$2 t=$3 b=$4; local h=(-H 'Accept: application/json' -H 'Content-Type: application/json'); [ -n "$t" ] && h+=(-H "Authorization: Bearer $t");
        curl -s -o /tmp/api.json -w '%{http_code}\n' -X "$m" "${h[@]}" ${b:+--data "$b"} "$BASE$p"; }
# jget <Pythonの添字> — /tmp/api.json の値を JSON で出力する（例: jget "['data'][0]['title']"）
jget() { python3 -c "import sys,json; d=json.load(open('/tmp/api.json')); print(json.dumps(eval('d'+sys.argv[1]), ensure_ascii=False))" "$1"; }
jexpr() { python3 -c "import sys,json; d=json.load(open('/tmp/api.json')); print(json.dumps(eval(sys.argv[1]), ensure_ascii=False))" "$1"; }   # 例: jexpr "len(d['data'])"
jkeys() { python3 -c "import sys,json; d=json.load(open('/tmp/api.json')); print(' '.join(sorted(eval('d'+sys.argv[1]).keys())))" "$1"; }
token() { tk "echo App\Models\User::where('email','$1$STAMP@example.com')->first()->createToken('inspection')->plainTextToken;" | tail -1; }

login_as /tmp/jarA A; login_as /tmp/jarB B
TA=$(token A); TB=$(token B); UA=$(uid A)
GID=$(tk 'echo App\Models\Genre::orderBy("id")->value("id");')
G2=$(tk 'echo App\Models\Genre::orderByDesc("id")->value("id");')
SEED=$(tk 'echo App\Models\Review::orderBy("id")->value("book_id");')   # レビューが付いている初期データの書籍
body() { echo "{\"title\":\"$1\",\"author\":\"API著者\",\"isbn\":\"$2\",\"published_date\":\"2021-03-04\",\"genres\":[$3]}"; }
```

## 1. ルートと認証のかけ方

| 行 | 条件 | 確認方法 | 期待値 |
|---|---|---|---|
| 07-01 | 公開APIの5本が、メソッド・URI・処理の組で登録されている: `GET /api/v1/books`→`Api\V1\BookController@index`、`POST /api/v1/books`→`Api\V1\BookController@store`、`GET /api/v1/books/{book}`→`Api\V1\BookController@show`、`PUT /api/v1/books/{book}`→`Api\V1\BookController@update`、`DELETE /api/v1/books/{book}`→`Api\V1\BookController@destroy` | `sail artisan route:list --path=api/v1 --except-vendor` | 左の5組がすべて出力に含まれる（欠落が0本） |
| 07-02 | 書き込みの3本（`POST /api/v1/books`・`PUT /api/v1/books/{book}`・`DELETE /api/v1/books/{book}`）に `auth:sanctum` が付いている | `tk 'foreach (Route::getRoutes() as $r) { if (str_starts_with($r->uri(), "api/v1")) echo implode("・", array_diff($r->methods(), ["HEAD"])), " ", $r->uri(), " ", implode(",", $r->middleware()), "\n"; }'` | POST・PUT・DELETE の3行の末尾に `auth:sanctum` がある |
| 07-03 | 参照の2本（`GET /api/v1/books`・`GET /api/v1/books/{book}`）に `auth:sanctum` が付いていない | 同上 | GET の2行の末尾に `auth:sanctum` が無い |
| 07-04 | アプリのコードにトークンを発行する処理が無い | `grep -rn "createToken" app routes resources` | 出力なし |
| 07-05 | `GET /sanctum/csrf-cookie`（No 049）が登録されている | `sail artisan route:list --path=sanctum` | `sanctum/csrf-cookie` と `sanctum.csrf-cookie` を含む行が出力される |
| 07-06 | `/sanctum/csrf-cookie` をアプリのコードから呼んでいない | `grep -rn "csrf-cookie" app routes resources` | 出力なし |

## 2. AP03 書籍登録 `POST /api/v1/books`

| 行 | 条件 | 確認方法 | 期待値 |
|---|---|---|---|
| 07-07 | トークンが無いと 401 | `api POST /api/v1/books '' "$(body X 971$STAMP $GID)"` | `401` |
| 07-08 | 401 の本文が `{"message": "認証が必要です。"}` | `cat /tmp/api.json` | `{"message":"認証が必要です。"}` |
| 07-09 | 無効なトークンでも 401 | `api POST /api/v1/books 'invalid-token' "$(body X 971$STAMP $GID)"` | `401` |
| 07-10 | トークンと正しい入力で 201 | `api POST /api/v1/books $TA "$(body A$STAMP-1 978$STAMP $GID)"` | `201` |
| 07-11 | 登録者がトークンの持ち主（検品会員A）である | `ID1=$(jget "['data']['id']"); tk "echo App\Models\Book::find($ID1)->user_id;"` | `$UA` の値 |
| 07-12 | 返す書籍の項目が `author`・`average_rating`・`description`・`genres`・`id`・`image_url`・`isbn`・`published_date`・`reviews`・`reviews_count`・`title` の11個である | `jkeys "['data']"` | `author average_rating description genres id image_url isbn published_date reviews reviews_count title` |
| 07-13 | `published_date` が `YYYY-MM-DD` の形で返る | `jget "['data']['published_date']"` | `"2021-03-04"` |
| 07-14 | `genres` が `{"id", "name"}` の配列で返る | `jget "['data']['genres'][0]"; tk "echo App\Models\Genre::find($GID)->name;"` | 1行目が `{"id": $GID, "name": "…"}` の形で、name が2行目と同じ |
| 07-15 | レビューが無い書籍の `average_rating` が `0` | `jget "['data']['average_rating']"` | `0` |
| 07-16 | レビューが無い書籍の `reviews_count` が `0` | `jget "['data']['reviews_count']"` | `0` |
| 07-17 | 入力エラーは 422 | `api POST /api/v1/books $TA '{}'` | `422` |
| 07-18 | 422 の `message` が「入力内容に誤りがあります。」 | `jget "['message']"` | `"入力内容に誤りがあります。"` |
| 07-19 | 422 の `errors` に、項目ごとの文言の配列が入る（タイトル） | `jget "['errors']['title']"` | `["タイトルを入力してください"]` |
| 07-20 | 著者が空のとき「著者名を入力してください」 | `jget "['errors']['author']"` | `["著者名を入力してください"]` |
| 07-21 | ISBN が空のとき「ISBNを入力してください」（画面と違い必須） | `jget "['errors']['isbn']"` | `["ISBNを入力してください"]` |
| 07-22 | 出版日が空のとき「出版日を入力してください」（画面と違い必須） | `jget "['errors']['published_date']"` | `["出版日を入力してください"]` |
| 07-23 | ジャンルが空のとき「ジャンルを1つ以上選択してください」 | `jget "['errors']['genres']"` | `["ジャンルを1つ以上選択してください"]` |
| 07-24 | タイトル256文字のとき「タイトルは255文字以内で入力してください」 | `api POST /api/v1/books $TA "$(body $(printf 'a%.0s' {1..256}) 972$STAMP $GID)" > /dev/null; jget "['errors']['title']"` | `["タイトルは255文字以内で入力してください"]` |
| 07-25 | ISBN が数字13桁でないとき「ISBNは13桁の数字で入力してください」 | `api POST /api/v1/books $TA "$(body X 12345 $GID)" > /dev/null; jget "['errors']['isbn']"` | `["ISBNは13桁の数字で入力してください"]` |
| 07-26 | 登録済みの ISBN のとき「このISBNは既に登録されています」 | `api POST /api/v1/books $TA "$(body X 978$STAMP $GID)" > /dev/null; jget "['errors']['isbn']"` | `["このISBNは既に登録されています"]` |
| 07-27 | 出版日が日付でないとき「出版日は正しい日付形式で入力してください」 | `api POST /api/v1/books $TA '{"title":"X","author":"X","isbn":"973'$STAMP'","published_date":"inspection","genres":['$GID']}' > /dev/null; jget "['errors']['published_date']"` | `["出版日は正しい日付形式で入力してください"]` |
| 07-28 | 説明1001文字のとき「説明は1000文字以内で入力してください」 | `api POST /api/v1/books $TA '{"title":"X","author":"X","isbn":"974'$STAMP'","published_date":"2021-01-01","description":"'$(printf 'a%.0s' {1..1001})'","genres":['$GID']}' > /dev/null; jget "['errors']['description']"` | `["説明は1000文字以内で入力してください"]` |
| 07-29 | 画像URLが URL の形でないとき「画像URLの形式が正しくありません」 | `api POST /api/v1/books $TA '{"title":"X","author":"X","isbn":"975'$STAMP'","published_date":"2021-01-01","image_url":"inspection","genres":['$GID']}' > /dev/null; jget "['errors']['image_url']"` | `["画像URLの形式が正しくありません"]` |
| 07-30 | 存在しないジャンルIDのとき「選択されたジャンルが存在しません」 | `api POST /api/v1/books $TA "$(body X 976$STAMP 999999999)" > /dev/null; python3 -c "import json; print(json.dumps(json.load(open('/tmp/api.json'))['errors'], ensure_ascii=False))"` | 出力に `選択されたジャンルが存在しません` を含む |
| 07-31 | 入力エラーのとき、書籍は保存されない | `tk "echo App\Models\Book::where('title','X')->count();"` | `0` |

## 3. AP01 書籍一覧 `GET /api/v1/books`

| 行 | 条件 | 確認方法 | 期待値 |
|---|---|---|---|
| 07-32 | 別のジャンルで2冊目を登録すると 201（以降の行で使う） | `api POST /api/v1/books $TA "$(body A$STAMP-2 979$STAMP $G2)"; ID2=$(jget "['data']['id']")` | `201` |
| 07-33 | トークン無しで 200 | `api GET "/api/v1/books"` | `200` |
| 07-34 | 既定の1ページの件数が10件 | `jget "['meta']['per_page']"` | `10` |
| 07-35 | ページ情報の `links` がある | `jkeys "['links']"` | `first` `last` `next` `prev` を含む |
| 07-36 | 一覧の各書籍の項目が、説明とレビューを含まない9個である | `jkeys "['data'][0]"` | `author average_rating genres id image_url isbn published_date reviews_count title` |
| 07-37 | 新しい順に並ぶ（後に登録した2冊目が先） | `api GET "/api/v1/books?keyword=A$STAMP" > /dev/null; jexpr "[b['title'] for b in d['data']]"` | `["A$STAMP-2", "A$STAMP-1"]` |
| 07-38 | `keyword` はタイトルまたは著者名の部分一致 | `api GET "/api/v1/books?keyword=API%E8%91%97%E8%80%85" > /dev/null; jexpr "len(d['data'])"` | `2`（著者「API著者」の2冊） |
| 07-39 | `genre_id` でジャンルを絞る | `api GET "/api/v1/books?keyword=A$STAMP&genre_id=$G2" > /dev/null; jexpr "[b['title'] for b in d['data']]"` | `["A$STAMP-2"]` |
| 07-40 | `per_page` で件数を変えられる | `api GET "/api/v1/books?per_page=1" > /dev/null; jexpr "len(d['data'])"` | `1` |
| 07-41 | 一覧のパラメータの入力エラーは 422 | `api GET "/api/v1/books?keyword=$(printf 'a%.0s' {1..256})"` | `422` |
| 07-42 | `keyword` 256文字は「検索キーワードは255文字以内で入力してください」 | `jget "['errors']['keyword']"` | `["検索キーワードは255文字以内で入力してください"]` |
| 07-43 | 存在しない `genre_id` は「指定されたジャンルが存在しません」 | `api GET "/api/v1/books?genre_id=999999999" > /dev/null; jget "['errors']['genre_id']"` | `["指定されたジャンルが存在しません"]` |
| 07-44 | `page=0` は「ページ番号は1以上の整数で指定してください」 | `api GET "/api/v1/books?page=0" > /dev/null; jget "['errors']['page']"` | `["ページ番号は1以上の整数で指定してください"]` |
| 07-45 | `per_page=101` は「取得件数は1〜100の範囲で指定してください」 | `api GET "/api/v1/books?per_page=101" > /dev/null; jget "['errors']['per_page']"` | `["取得件数は1〜100の範囲で指定してください"]` |
| 07-46 | `per_page=0` は「取得件数は1〜100の範囲で指定してください」 | `api GET "/api/v1/books?per_page=0" > /dev/null; jget "['errors']['per_page']"` | `["取得件数は1〜100の範囲で指定してください"]` |

## 4. AP02 書籍詳細 `GET /api/v1/books/{book}`

| 行 | 条件 | 確認方法 | 期待値 |
|---|---|---|---|
| 07-47 | トークン無しで 200 | `api GET /api/v1/books/$SEED` | `200` |
| 07-48 | 詳細の項目が11個（説明とレビューを含む） | `jkeys "['data']"` | `author average_rating description genres id image_url isbn published_date reviews reviews_count title` |
| 07-49 | `reviews` の各要素が `user_name`・`rating`・`comment`・`created_at` の4つ | `jkeys "['data']['reviews'][0]"` | `comment created_at rating user_name` |
| 07-50 | `reviews` の `created_at` が ISO 8601 の形 | `jget "['data']['reviews'][0]['created_at']"` | `"YYYY-MM-DDTHH:MM:SS` で始まる文字列 |
| 07-51 | `average_rating` がレビュー平均を小数1桁に丸めた数 | `jget "['data']['average_rating']"; tk "echo round(DB::table('reviews')->where('book_id',$SEED)->avg('rating'), 1);"` | 2行の数が同じ |
| 07-52 | `reviews_count` がレビュー件数 | `jget "['data']['reviews_count']"; tk "echo DB::table('reviews')->where('book_id',$SEED)->count();"` | 2行の数が同じ |
| 07-53 | 存在しないIDは 404 | `api GET /api/v1/books/999999999` | `404` |
| 07-54 | 404 の本文が `{"message": "指定された書籍が見つかりません。"}` | `cat /tmp/api.json` | `{"message":"指定された書籍が見つかりません。"}` |
| 07-55 | `Accept` を付けずに呼んでも、存在しないIDは画面ではなく JSON で返る | `curl -s $BASE/api/v1/books/999999999` | `{"message":"指定された書籍が見つかりません。"}` |

## 5. AP04 書籍更新 `PUT /api/v1/books/{book}`

| 行 | 条件 | 確認方法 | 期待値 |
|---|---|---|---|
| 07-56 | トークンが無いと 401 | `api PUT /api/v1/books/$ID1 '' "$(body A$STAMP-1 978$STAMP $GID)"` | `401` |
| 07-57 | 登録者以外（検品会員B）のトークンでは 403 | `api PUT /api/v1/books/$ID1 $TB "$(body A$STAMP-1 978$STAMP $GID)"` | `403` |
| 07-58 | 403 の本文が `{"message": "この操作を実行する権限がありません。"}` | `cat /tmp/api.json` | `{"message":"この操作を実行する権限がありません。"}` |
| 07-59 | 登録者本人のトークンで、自分の ISBN のまま更新でき 200 | `api PUT /api/v1/books/$ID1 $TA "$(body A$STAMP-1u 978$STAMP $GID)"` | `200` |
| 07-60 | 更新後の書籍が返る | `jget "['data']['title']"` | `"A$STAMP-1u"` |
| 07-61 | 他の書籍の ISBN へ更新しようとすると 422 | `api PUT /api/v1/books/$ID1 $TA "$(body A$STAMP-1u 979$STAMP $GID)"` | `422` |
| 07-62 | 上の応答の文言が「このISBNは既に登録されています」 | `jget "['errors']['isbn']"` | `["このISBNは既に登録されています"]` |
| 07-63 | 存在しないIDは 404 | `api PUT /api/v1/books/999999999 $TA "$(body X 977$STAMP $GID)"` | `404` |

## 6. AP05 書籍削除 `DELETE /api/v1/books/{book}`

| 行 | 条件 | 確認方法 | 期待値 |
|---|---|---|---|
| 07-64 | トークンが無いと 401 | `api DELETE /api/v1/books/$ID2` | `401` |
| 07-65 | 登録者以外のトークンでは 403 | `api DELETE /api/v1/books/$ID2 $TB` | `403` |
| 07-66 | 登録者本人のトークンで 204 | `api DELETE /api/v1/books/$ID2 $TA` | `204` |
| 07-67 | 204 の本文が空 | `wc -c < /tmp/api.json` | `0` |
| 07-68 | 論理削除である（`books.deleted_at` に日時が入り、行は残る） | `tk "echo DB::table('books')->where('id',$ID2)->value('deleted_at');"` | 日時の文字列が1行出力される |
| 07-69 | 削除済みの書籍の詳細は 404 | `api GET /api/v1/books/$ID2` | `404` |
| 07-70 | 削除済みの書籍が一覧に出ない | `api GET "/api/v1/books?keyword=A$STAMP" > /dev/null; jexpr "[b['id'] for b in d['data']]"` | `[$ID1]`（`$ID1` は1冊目のID） |
| 07-71 | 削除済みの書籍の更新は 404 | `api PUT /api/v1/books/$ID2 $TA "$(body A$STAMP-2 979$STAMP $G2)"` | `404` |

## 7. エラー応答の部品

| 行 | 条件 | 確認方法 | 期待値 |
|---|---|---|---|
| 07-72 | `App\Http\Requests\Api\V1\StoreApiBookRequest`・`UpdateApiBookRequest`・`IndexBookRequest` が `App\Http\Requests\Api\V1\ApiFormRequest` を継承している | `grep -n 'extends' app/Http/Requests/Api/V1/StoreApiBookRequest.php app/Http/Requests/Api/V1/UpdateApiBookRequest.php app/Http/Requests/Api/V1/IndexBookRequest.php` | 3行すべてが `extends ApiFormRequest` |
| 07-73 | `App\Http\Requests\Api\V1\ApiFormRequest@failedValidation` が 422 と「入力内容に誤りがあります。」を返す | `sed -n '/function failedValidation/,/^    }/p' app/Http/Requests/Api/V1/ApiFormRequest.php` | 出力に `入力内容に誤りがあります。` と `422` がある |
| 07-74 | `App\Exceptions\Handler@register` が、`api/*` のときに 401・403・404 を JSON で返す記述を持つ | `sed -n '/function register/,/^    }/p' app/Exceptions/Handler.php` | 出力に `api/*` の判定と、`認証が必要です。`・`この操作を実行する権限がありません。`・`指定された書籍が見つかりません。` の3つの文言がある |
| 07-75 | 返す形を作る部品4本 `App\Http\Resources\BookResource`・`BookListResource`・`GenreResource`・`ReviewResource` が存在する | `ls app/Http/Resources/BookResource.php app/Http/Resources/BookListResource.php app/Http/Resources/GenreResource.php app/Http/Resources/ReviewResource.php` | 4つのファイル名が出力され、エラーが0件 |

## 8. 呼び出し回数の上限（1分以上待ってから行う）

| 行 | 条件 | 確認方法 | 期待値 |
|---|---|---|---|
| 07-76 | `App\Providers\RouteServiceProvider@boot` に、1分60回・ログイン中は会員ごと・それ以外はIPごとの上限がある | `sed -n '/function boot/,/^    }/p' app/Providers/RouteServiceProvider.php` | 出力に `perMinute(60)` と、会員のIDまたはIPを `by(` に渡す記述がある |
| 07-77 | 公開APIの応答に、上限が60であることを示す見出しが付く | `sleep 61; curl -s -D - -o /dev/null -H 'Accept: application/json' $BASE/api/v1/books \| grep -i '^x-ratelimit-limit'` | `X-RateLimit-Limit: 60`（大文字小文字は問わない） |
| 07-78 | 同じIPから1分に60回を超えて呼ぶと 429 が返る（直前の1回を含めて61回目） | `for i in $(seq 1 60); do curl -s -o /dev/null -w '%{http_code}\n' -H 'Accept: application/json' $BASE/api/v1/books; done \| sort \| uniq -c` | `59 200` と `1 429` の2行 |
