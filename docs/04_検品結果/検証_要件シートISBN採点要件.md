# 検証: 要件シートにおける ISBN検索/Google Books API/自動入力 の要件・検証観点の所在

## やったこと（事実のみ）
- `docs/00_正本/` 配下の xlsx を `find` で特定し、/tmp に展開して各ワークシートのセル本文（inlineStr, 数値実体参照）を PHP でデコード抽出した。
- ISBN・Google・自動入力・自動補完・バーコード を含むセルを、workbook.xml のシート名（機能要件=シート7 / テスト要件=シート10 等）と対応づけて原文引用した。

## 変更ファイル
なし（xlsx 展開先は /tmp のみ。リポジトリ内に生成物なし）。

```
$ git status --porcelain
?? .claude/
```
（`.claude/` は本作業前から存在する未追跡ディレクトリ。要件シート・実装・その他リポジトリ内ファイルへの変更なし）

現在のブランチ:
```
$ git branch --show-current
fix/book-search-and-isbn-ui
```

## 証拠（生の実行結果のみ）

### 対象ファイルの特定

実行コマンド：
```
$ ls -la "docs/00_正本/要件シート.xlsx"  ; find docs -iname "*.xlsx"
```
出力：
```
ls: cannot access 'docs/00_正本/要件シート.xlsx': No such file or directory
docs/00_正本/片倉_菖さん_新模擬案件_Bookshelf_要件シート (1).xlsx
```
（指定パスのファイルは存在せず、実体は上記名のファイル）

### xlsx 展開とシート名一覧

実行コマンド：
```
$ mkdir -p /tmp/youken && unzip -o "docs/00_正本/片倉_菖さん_新模擬案件_Bookshelf_要件シート (1).xlsx" -d /tmp/youken
$ ls /tmp/youken/xl/  ;  ls /tmp/youken/xl/worksheets/
$ grep -aoE '<sheet [^>]*/>' /tmp/youken/xl/workbook.xml
```
出力（シート名。共有文字列 sharedStrings.xml は存在せず、各セルは inlineStr（数値実体参照）で格納）：
```
name="シート1 ターム内容"        r:id=rId1  -> sheet1.xml
name="シート2 あなたのタスク"    r:id=rId2  -> sheet2.xml
name="シート3 開発プロセス"      r:id=rId3  -> sheet3.xml
name="シート4 環境構築手順"      r:id=rId4  -> sheet4.xml
name="シート5 画面設計"          r:id=rId5  -> sheet5.xml
name="シート6 デザインUI"        r:id=rId6  -> sheet6.xml
name="シート7 機能要件"          r:id=rId7  -> sheet7.xml
name="シート8 バリデーションルール" r:id=rId8 -> sheet8.xml
name="シート9 シーディング要件"  r:id=rId9  -> sheet9.xml
name="シート10 テスト要件"       r:id=rId10 -> sheet10.xml
name="シート11 データ要件"       r:id=rId11 -> sheet11.xml
name="シート12 テーブル仕様書"   r:id=rId12 -> sheet12.xml
name="シート13 API仕様書"        r:id=rId13 -> sheet13.xml
```
（rId は sheet番号と一致。_rels/workbook.xml.rels で確認済み）

### セル抽出スクリプト（inlineStr の数値実体参照をデコード）

```
$ php /tmp/dump.php <worksheet.xml> 'ISBN|Google|自動入力|自動補完|バーコード'
  # 各 <c r="..."> の <t> を集約し html_entity_decode して、上記語を含むセルを [セル番地] 本文 で出力
```
（ホスト php 8.4.24 を使用。sail 不要）

---

### シート7 機能要件（sheet7）— ISBN検索 は「機能要件」として定義。列見出しは C5=画面名/D5=操作/E5=URL・メソッド/F5=完了条件/G5=基本・応用/H5=成功時遷移/I5=確定フラッシュ/J5=失敗時挙動/K5=認可失敗時

セル原文（そのまま引用）：
```
[D31] ★ ISBN（13桁）を入力して「ISBN検索」ボタンを押す

[E31] GET /books/isbn/{isbn}

[F31] 13桁ISBN→Google Books API から書籍情報を取得し、フォームに自動入力される
エラー時は適切なレスポンスを返すこと

[G31] ★ 応用

[H31] ―（JSONレスポンスを返すのみ。画面遷移なし）

[I31] ―（API）

[J31] isbn形式不正: 422 JSON {error: 'ISBNは13桁の数字で入力してください'}／Google Books APIに該当書籍が無い場合: 404 JSON {error: '該当する書籍が見つかりませんでした'}／Google Books API自体の通信エラー: 502 JSON {error: '書籍情報の取得に失敗しました'}

[K31] 未認証: /loginへリダイレクト（書籍登録・編集画面内の補助機能のため、親画面と同じ認可）

[F29] 認証時→タイトル・著者・ISBN・出版日・説明・画像URLの入力欄と、全ジャンル一覧のチェックボックス（複数選択可）が表示される（応用時は「ISBN書籍情報自動入力フォーム」も含む）
```

### シート5 画面設計（sheet5）

```
[H20] Blade提供済み（Advancedブランチ）。基本機能に加え、ISBN検索フォームを追加。
```

### シート1 ターム内容（sheet1）— 応用機能の位置づけ

```
[D8]（システム概要・応用機能で追加される要素）
・ISBN-13によるGoogle Books API連携（書籍情報自動取得）

[D7]（フェーズ2: 応用機能の実装）
… 高度な検索・フィルタ、ISBN検索（Google Books API連携）、マイ読書レポート、公開APIへの Sanctum 認証追加を実装します。
```

### シート10 テスト要件（sheet10）— ISBN検索 の該当セルと、その検証観点セルの中身

シート10 の性格（C3/D5/D6 原文）：
```
[C3] このプロジェクトで実装するべきテストの一覧です。
赤字（★マーク）は応用要件です。基本要件実装後に着手してください。
各カテゴリで具体的に何をテストすべきか（テストケースの設計）は、自分で考えてください。
設計したテストケースは面談内でコーチにレビューしてもらいましょう。

[D5] ・テストが全て通過すること。
[D6] ・sail artisan test --coverage コマンドで表示されるテストカバレッジが60%超を目指すこと。
※採点は機能カテゴリ単位で行われるため、各カテゴリで期待される検証観点を網羅するようテストケースを設計・実装してください。
```

ISBN検索カテゴリの行（E列=カテゴリ名 / F列=基本・応用 / G列=検証観点詳細）：
```
[E25] ★ ISBN検索
[F25] ★ 応用
```
G25（検証観点詳細セル）の中身 — 実行コマンドと出力：
```
$ grep -aoE '<c r="[A-Z]+25"[^>]*>' /tmp/youken/xl/worksheets/sheet10.xml
<c r="C25" s="69" t="n">
<c r="D25" s="279" t="n">
<c r="E25" s="280" t="inlineStr">
<c r="F25" s="280" t="inlineStr">
<c r="G25" s="264" t="n">
```
（G25 は t="n" かつ本文 <t> なし＝空セル。書籍CRUD行 G14・API行 G21 のような「何をテストするか」の観点本文は ISBN検索行には記述されていない）

比較のため、観点本文が入っている他カテゴリの G 列（原文）：
```
[G14] 登録（…books.showへ遷移し「書籍を登録しました」…）を確認する。登録バリデーション（title/author/isbn/published_date未入力、isbn不正・重複、genre未選択…）を確認する。… 削除済みISBNの一意性（削除済みの本と同じISBNで新規登録しようとすると一意性エラーになる）を確認する。…

[G21] AP01一覧・正常系…／AP03新規登録（正しいデータで201、バリデーション違反で422…）／AP04更新・正常系（…ISBN一意性チェックは自身のレコードを除外する）を確認する。…
```
（＝ISBN の桁数・重複・一意性は「書籍CRUD」「公開API」カテゴリの観点として記述あり。「ISBN検索（Google Books連携・自動入力）」という応用カテゴリ自体の観点本文は G25 空欄）

### シート8 バリデーションルール（sheet8）— ISBN の桁数・一意性文言（機能要件側）

```
[E5] …ISBNの桁数と一意性を担保すること…
[H5] …ISBNを入力してください／ISBNは13桁の数字で入力してください／このISBNは既に登録されています…
[H6] isbn: ISBNは13桁の数字で入力してください／このISBNは既に登録されています
```
（これは書籍CRUD/API登録・更新の isbn バリデーション文言。Google Books 連携・自動入力の記述ではない）

### シート13 API仕様書（sheet13）— ISBN は登録/更新APIの入力項目として言及

```
[D19] isbn: required, string, regex:/^[0-9]{13}$/, unique:books,isbn / ISBNを入力してください／ISBNは13桁の数字で入力してください／このISBNは既に登録されています …
[D22] isbn: required, string, regex:/^[0-9]{13}$/, unique:books,isbn,{book},id（自身のレコードを除外）…
```
（AP03/AP04 の isbn 入力バリデーション。Google Books API 検索エンドポイント自体はシート13には無く、シート7 F31/J31 に記載）

### 「Google Books API を使う」「13桁」「自動入力」達成基準の原文（採点/検証観点の粒度で書かれているか）

- 「Google Books API から書籍情報を取得」「フォームに自動入力される」= シート7 F31（機能要件・完了条件列）に原文あり。
- 「13桁」= シート7 D31/F31、シート8、シート13 に原文あり（regex:/^[0-9]{13}$/ 含む）。
- ISBN形式不正/該当なし/通信エラーの 422/404/502 とエラーJSON文言 = シート7 J31（失敗時挙動列）に原文あり。
- シート10（テスト要件）ISBN検索行 G25 は空欄で、ISBN検索固有の検証観点本文の明示記述なし（テストケース設計は C3 のとおり実装者に委ねる方針）。

## 未確認・保留
- 文字化け・展開不能は発生しなかった（unzip 成功、php で数値実体参照をデコード済み）。
- シート10 のセルはセル結合・スタイル参照を含むが、本作業では inlineStr の <t> 本文のみを抽出した。結合セルの視覚的な行対応（E25 ラベルに対する詳細列が G25 か H 列以降か）は、G25 が空である事実（grep 出力）までを証拠とし、これ以上の視覚的レイアウト判定は保留する。

## worktree情報
- ブランチ名：fix/book-search-and-isbn-ui
- 本体へのマージ：本ファイルは検品証拠であり実装マージ対象外。要件シート・実装コードへの変更なし。

## 判定はしない
本ファイルは YES/NO・PASS/FAIL・採点対象である/ない等の判定を含まない。原文引用と grep 出力の事実のみを示す。判定は片倉／チャット側が行う。
