# 07 公開API 判定結果

- 対象の発注書: `docs/02_発注書/発注書_07_公開API.md`
- 対象の検品表: `docs/03_検品表/07_公開API.md`
- 判定に使った証拠: `docs/04_検品結果/証拠_07_公開API.md`
- 判定した実装コードの識別番号（証拠の HEAD）: `cfe33a37b686b49f8796cc7c3194b08d14fb5fc5`（判定時の `git rev-parse HEAD` も同じ値）
- 判定日: 2026-10-05
- 読み替え: 証拠冒頭の記載に従い、期待値の `http://localhost` を `http://localhost:8022` に読み替えた
- 変数の値（証拠「準備で値が入った変数」「全行の確認の後の変数」より。トークンの値は書かない）: `BASE=http://localhost:8022`、`G2=10`、`GID=1`、`SEED=1`、`STAMP=1791167060`、`UA=6`、`ID1=12`、`ID2=13`

## 判定の前の確認

| 確認 | 結果 |
|---|---|
| 検品表の行数 `grep -cE '^\| [0-9]{2}-[0-9]+ '` | 78 |
| 証拠の節の数 `grep -c '^## 行 '` | 78 |
| 証拠の HEAD と判定時の HEAD | どちらも `cfe33a37b686b49f8796cc7c3194b08d14fb5fc5` |

## JSON として読み込んだ照合（07-08・07-54・07-55・07-58）

証拠の「得られたもの」の1行を `json.loads` で読み込み、期待値の辞書と比べた。

実行コマンド:
```
$ for r in 224 687 697 727; do s=$(sed -n "${r}p" docs/04_検品結果/証拠_07_公開API.md); echo "line $r:"; python3 -c 'import sys,json; d=json.loads(sys.argv[1]); print(json.dumps(d, ensure_ascii=False)); print(d=={"message":sys.argv[2]})' "$s" "<行ごとの期待文言>"; done
```

出力:
```
line 224:
{"message": "認証が必要です。"}
True
line 687:
{"message": "指定された書籍が見つかりません。"}
True
line 697:
{"message": "指定された書籍が見つかりません。"}
True
line 727:
{"message": "この操作を実行する権限がありません。"}
True
```

（証拠の224行目は 07-08、687行目は 07-54、697行目は 07-55、727行目は 07-58 の「得られたもの」）

## 行ごとの判定

| 行 | 判定 | 根拠（証拠の「得られたもの」の引用） |
|---|---|---|
| 07-01 | YES | `GET\|HEAD  api/v1/books ... Api\V1\BookController@index` / `POST      api/v1/books ... Api\V1\BookController@store` / `GET\|HEAD  api/v1/books/{book} ... Api\V1\BookController@show` / `PUT       api/v1/books/{book} ... Api\V1\BookController@update` / `DELETE    api/v1/books/{book} ... Api\V1\BookController@destroy`。5組すべてが含まれる |
| 07-02 | YES | `POST api/v1/books api,auth:sanctum` / `PUT api/v1/books/{book} api,auth:sanctum` / `DELETE api/v1/books/{book} api,auth:sanctum` |
| 07-03 | YES | `GET api/v1/books api` / `GET api/v1/books/{book} api`。末尾に `auth:sanctum` が無い |
| 07-04 | YES | （出力は空） |
| 07-05 | YES | `GET\|HEAD       sanctum/csrf-cookie sanctum.csrf-cookie › Laravel\Sanctum › …` |
| 07-06 | YES | （出力は空） |
| 07-07 | YES | `401` |
| 07-08 | YES | `{"message":"認証が必要です。"}`。JSON として読み込んだ値が `{"message": "認証が必要です。"}` で、期待値との比較が `True`（上の節） |
| 07-09 | YES | `401` |
| 07-10 | YES | `201` |
| 07-11 | YES | `6`。`$UA` は `6` |
| 07-12 | YES | `author average_rating description genres id image_url isbn published_date reviews reviews_count title` |
| 07-13 | YES | `"2021-03-04"` |
| 07-14 | YES | 1行目 `{"id": 1, "name": "小説"}`（`$GID` は `1`）、2行目 `小説`。name が2行目と同じ |
| 07-15 | YES | `0` |
| 07-16 | YES | `0` |
| 07-17 | YES | `422` |
| 07-18 | YES | `"入力内容に誤りがあります。"` |
| 07-19 | YES | `["タイトルを入力してください"]` |
| 07-20 | YES | `["著者名を入力してください"]` |
| 07-21 | YES | `["ISBNを入力してください"]` |
| 07-22 | YES | `["出版日を入力してください"]` |
| 07-23 | YES | `["ジャンルを1つ以上選択してください"]` |
| 07-24 | YES | `["タイトルは255文字以内で入力してください"]` |
| 07-25 | YES | `["ISBNは13桁の数字で入力してください"]` |
| 07-26 | YES | `["このISBNは既に登録されています"]` |
| 07-27 | YES | `["出版日は正しい日付形式で入力してください"]` |
| 07-28 | YES | `["説明は1000文字以内で入力してください"]` |
| 07-29 | YES | `["画像URLの形式が正しくありません"]` |
| 07-30 | YES | `{"genres.0": ["選択されたジャンルが存在しません"]}`。`選択されたジャンルが存在しません` を含む |
| 07-31 | YES | `0` |
| 07-32 | YES | `201` |
| 07-33 | YES | `200` |
| 07-34 | YES | `10` |
| 07-35 | YES | `first last next prev` |
| 07-36 | YES | `author average_rating genres id image_url isbn published_date reviews_count title` |
| 07-37 | YES | `["A1791167060-2", "A1791167060-1"]`（`$STAMP` は `1791167060`） |
| 07-38 | YES | `2` |
| 07-39 | YES | `["A1791167060-2"]` |
| 07-40 | YES | `1` |
| 07-41 | YES | `422` |
| 07-42 | YES | `["検索キーワードは255文字以内で入力してください"]` |
| 07-43 | YES | `["指定されたジャンルが存在しません"]` |
| 07-44 | YES | `["ページ番号は1以上の整数で指定してください"]` |
| 07-45 | YES | `["取得件数は1〜100の範囲で指定してください"]` |
| 07-46 | YES | `["取得件数は1〜100の範囲で指定してください"]` |
| 07-47 | YES | `200` |
| 07-48 | YES | `author average_rating description genres id image_url isbn published_date reviews reviews_count title` |
| 07-49 | YES | `comment created_at rating user_name` |
| 07-50 | YES | `"2026-10-05T11:24:20+09:00"`。`"YYYY-MM-DDTHH:MM:SS` の形で始まる |
| 07-51 | YES | 1行目 `2.8`、2行目 `2.8` |
| 07-52 | YES | 1行目 `4`、2行目 `4` |
| 07-53 | YES | `404` |
| 07-54 | YES | `{"message":"指定された書籍が見つかりません。"}`。JSON として読み込んだ値が `{"message": "指定された書籍が見つかりません。"}` で、比較が `True` |
| 07-55 | YES | `{"message":"指定された書籍が見つかりません。"}`。JSON として読み込んだ値が `{"message": "指定された書籍が見つかりません。"}` で、比較が `True` |
| 07-56 | YES | `401` |
| 07-57 | YES | `403` |
| 07-58 | YES | `{"message":"この操作を実行する権限がありません。"}`。JSON として読み込んだ値が `{"message": "この操作を実行する権限がありません。"}` で、比較が `True` |
| 07-59 | YES | `200` |
| 07-60 | YES | `"A1791167060-1u"` |
| 07-61 | YES | `422` |
| 07-62 | YES | `["このISBNは既に登録されています"]` |
| 07-63 | YES | `404` |
| 07-64 | YES | `401` |
| 07-65 | YES | `403` |
| 07-66 | YES | `204` |
| 07-67 | YES | `0` |
| 07-68 | YES | `2026-10-05 11:24:37` |
| 07-69 | YES | `404` |
| 07-70 | YES | `[12]`。`$ID1` は `12` |
| 07-71 | YES | `404` |
| 07-72 | YES | `StoreApiBookRequest.php:5:class StoreApiBookRequest extends ApiFormRequest` / `UpdateApiBookRequest.php:7:class UpdateApiBookRequest extends ApiFormRequest` / `IndexBookRequest.php:5:class IndexBookRequest extends ApiFormRequest` |
| 07-73 | YES | `'message' => '入力内容に誤りがあります。',` / `], 422));` |
| 07-74 | YES | `if ($request->is('api/*')) {` / `return response()->json(['message' => '指定された書籍が見つかりません。'], 404);` / `return response()->json(['message' => '認証が必要です。'], 401);` / `return response()->json(['message' => 'この操作を実行する権限がありません。'], 403);` |
| 07-75 | YES | `app/Http/Resources/BookListResource.php` / `app/Http/Resources/BookResource.php` / `app/Http/Resources/GenreResource.php` / `app/Http/Resources/ReviewResource.php`。エラーの出力は無い |
| 07-76 | YES | `return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());` |
| 07-77 | YES | `X-RateLimit-Limit: 60` |
| 07-78 | YES | `     59 200` / `      1 429` |

## 差し戻しの指示

NO の行は無い。

## 件数

| 行数 | YES | NO | うち根拠不足 |
|---|---|---|---|
| 78 | 78 | 0 | 0 |

総合判定: 合格
