status: fixed

# 実機検品 UI修正 結果（デザインUI優先の6項目）

**実施日**: 2026-09-03
**ブランチ**: `fix/design-ui-corrections`
**対象**: 実機検品で片倉が指摘したUI 6項目
**判定の前提（重要）**: 本修正は §0 の正本優先順位（1位=frozen Bladeモック ＞ 2位=要件シート/デザインUI）に対する**例外的上書き**である。指摘6項目はすべて frozen Bladeモックと一致しデザインUI（シート6画像）と相違していたため、片倉の裁定「デザインUI優先で修正」に基づき Bladeモックを上書きした。判定はチャット側で行う。

---

## 0. 正本の裁定（この修正の根拠）

- 調査の結果、指摘6項目の現行実装は frozen Bladeモック（basicブランチ）と**バイト一致**、デザインUI（要件シート シート6の画面画像）とは**相違**していた。
- §0 のルール上は Bladeモック（1位）が勝つため、本来は QUESTIONS.md へ退避して停止する事案。
- 片倉へ確認し、**「デザインUI優先で修正（Bladeモックを上書き）」**の裁定を得た（AskUserQuestion）。書籍説明は**全11冊を書籍内容に忠実に書き直す**裁定。
- 以降の変更はこの裁定に基づく。

---

## 1. 書籍一覧：レビュー（★評価）を削除

デザインUI（シート6・書籍一覧画像）にカード上の★評価表示は存在しない。

- View: `resources/views/books/index.blade.php` から `reviews_avg_rating` の★☆ブロックを削除。
- Controller: `app/Http/Controllers/BookController.php::index` の未使用となった `->withAvg('reviews', 'rating')` を削除。

証拠（実機レンダリング）:
```
$ curl -s http://localhost:8022/books | grep -c '★\|☆'
0
```

## 2. 書籍一覧：文字を太く（preference）

- `h3` タイトルを `font-bold` → `font-extrabold`、著者を `font-medium` に変更。
- 注記: デザインUIのタイトルはむしろ semibold 相当で現行 `font-bold` より軽い。本項目はデザインUI準拠ではなく片倉の主観指定（「もう少し太い」）に基づく調整のため、太さの程度は再実機で要確認。

証拠:
```
$ curl -s http://localhost:8022/books | grep -o 'font-extrabold' | head -1
font-extrabold
```

## 3. 書籍一覧：ページネーションの色味・位置

- デザインUIは「Showing 1 to 10 of 11 results」＋枠付き数字を**左下にまとめて**配置。デフォルトのTailwindページネータは `justify-between` で左右に分離していた。
- `php artisan vendor:publish --tag=laravel-pagination` で公開し、`resources/views/vendor/pagination/tailwind.blade.php` を修正:
  - 外側 `<nav>`: `justify-between` → `justify-start`
  - デスクトップ用 `<div>`: `sm:flex-1 ... sm:justify-between` → `sm:flex sm:items-center sm:justify-start sm:gap-4`
- 色味はデフォルトのグレー基調（`text-gray-*` / `border-gray-300`）でデザインUIと同系。青系は不使用。

証拠（実機HTML・左寄せグルーピング）:
```
$ curl -s http://localhost:8022/books | grep -o 'justify-start\|sm:gap-4' | sort -u
justify-start
sm:gap-4
```

## 4. ログイン画面：会員登録ボタンを削除

デザインUI（シート6・ログイン画像）は「ログイン」ボタンのみ。

- `resources/views/auth/login.blade.php` から `会員登録` リンク（`route('register')` ボタン）を削除。ログインボタンのみ右寄せで残す。

証拠:
```
$ curl -s http://localhost:8022/login | grep -c '会員登録'
0
```

## 5. 会員登録画面：ログインボタン → 「アカウントをお持ちの方」リンク

デザインUI（シート6・会員登録画像）は下線付きテキストリンク「アカウントをお持ちの方」＋登録ボタン。

- `resources/views/auth/register.blade.php` の `route('login')` へのボタン風リンクを、テキスト `アカウントをお持ちの方`・クラス `text-sm text-gray-600 underline hover:text-gray-900` の**リンク**に変更。

証拠:
```
$ curl -s http://localhost:8022/register | grep -o 'アカウントをお持ちの方' | head -1
アカウントをお持ちの方
$ curl -s http://localhost:8022/register | grep -c '>ログイン<'
0
```

## 6. お気に入り一覧：書籍詳細への動線（カード全体をクリック可能に）

書籍一覧はカード全体が `<a>` でどこでもクリックで詳細へ遷移する。お気に入り一覧はカード内にお気に入りトグルの `<form>` があるためカード全体を `<a>` で包めず、当初はタイトル・画像のみリンクにしていた。片倉より「書籍一覧に合わせカード全体をクリック可能に」との指摘。

- 対応: **ストレッチリンク**方式に変更（`resources/views/favorites/index.blade.php`）。
  - カード `<div>` を `relative` にし、カード全体を覆う透明リンク `<a href="{{ route('books.show', $book) }}" class="absolute inset-0 z-0" aria-label="◯◯ の詳細">` を配置。カードのどこをクリックしても詳細へ遷移する。
  - お気に入りトグルの `<form>` に `relative z-10` を付与し、オーバーレイリンクより前面に出してクリック可能を維持（`<a>` 内に `<form>` をネストしない構成）。
  - タイトルは `<span class="text-blue-600">` に変更（クリックはオーバーレイが担う）。

証拠（山田太郎でログインし `/favorites` を取得）:
```
$ curl -s -b cj.txt http://localhost:8022/favorites | grep -o '<a href="http://localhost:8022/books/[0-9]*" class="absolute inset-0 z-0"[^>]*>' | head -3
<a href="http://localhost:8022/books/1" class="absolute inset-0 z-0" aria-label="吾輩は猫である の詳細">
<a href="http://localhost:8022/books/2" class="absolute inset-0 z-0" aria-label="人を動かす の詳細">
<a href="http://localhost:8022/books/3" class="absolute inset-0 z-0" aria-label="リーダブルコード の詳細">

$ curl -s -b cj.txt http://localhost:8022/favorites | grep -c 'novalidate class="relative z-10"'
4
（＝カード4枚すべてに全面オーバーレイリンク＋前面ハートボタン）
```

## 7. 書籍詳細：説明文を全11冊 書き直し（①は画像通り）

- `database/seeders/BookSeeder.php` の11冊の `description` を各書籍の内容に忠実な文へ書き直し。
- ①「吾輩は猫である」はデザインUI（シート6・書籍詳細画像）の文言と一言一句同一にした。
- 反映のため `migrate:fresh --seed` を実行（`firstOrCreate` はISBN既存時に更新しないため）。

証拠（①の実データ）:
```
$ sail artisan tinker --execute="echo App\Models\Book::find(1)->description;"
中学校の英語教師である珍野苦沙弥の家に飼われている猫である「吾輩」の視点から、珍野一家や、そこに出入りする人々の様子を風刺的に描いた作品。
```
（デザインUI画像の①説明文と同一）

全11件の書き直し後の値:
```
$ sail artisan tinker --execute="App\Models\Book::all(['id','description'])->each(fn($b)=>print($b->id.':'.$b->description.PHP_EOL));"
1:中学校の英語教師である珍野苦沙弥の家に飼われている猫である「吾輩」の視点から、珍野一家や、そこに出入りする人々の様子を風刺的に描いた作品。
2:人を動かすための原則を、豊富な逸話を交えて説いた自己啓発の古典。相手の立場に立ち、誠実な関心を寄せることの大切さを伝える。
3:他人が読んで理解しやすいコードを書くための実践的なテクニックを、命名・コメント・制御フローなどの具体例とともに解説する。
4:主体性を発揮する、終わりを思い描くことから始めるなど、人格を磨き成功へと導く7つの習慣を体系的にまとめた自己啓発書。
5:江戸っ子気質で正義感の強い新米教師が、赴任先の四国の中学校で個性的な同僚や生徒たちと繰り広げる騒動を痛快に描いた小説。
6:認知革命・農業革命・科学革命という3つの革命を軸に、ホモ・サピエンスがいかにして地球の支配者となったかを壮大に描き出す。
7:読みやすく保守しやすい「クリーンなコード」を書くための原則を、命名・関数・クラス・テストなど多角的な観点から解説する技術書。
8:アドラー心理学の考え方を哲人と青年の対話形式で解き明かし、対人関係の悩みから解放されて自由に生きる道を示す一冊。
9:売れない若手芸人の主人公と破天荒な先輩芸人との交流を通して、芸人としての生き方と青春の葛藤を描いた芥川賞受賞作。
10:人が陥りがちな10の思い込みを指摘し、データと事実に基づいて世界を正しく読み解くための思考法を説く。
11:規格化された輸送用コンテナの登場が物流コストを劇的に下げ、世界経済とグローバル化を一変させた歴史を描くノンフィクション。
```

---

## 8. 付随して修復したフロントエンドビルドの回帰（要報告）

アセット再ビルド時に、指摘とは別の**既存の環境回帰**が発覚したため修復した（デザインUIを実機へ反映するには再ビルドが必須のため）。

- `resources/css/app.css` が **0バイト**（`@tailwind` ディレクティブ消失）→ ビルドがCSSを生成できていなかった。
- `resources/js/app.js` が `import './bootstrap'` のみで、`resources/js/bootstrap.js` は**存在せず**ビルドが失敗（`Could not resolve "./bootstrap"`）。Alpine起動コードも消失。
- 修復方針: frozen Bladeモック（basicブランチ）の frontend 構成に一致させた。
  - `app.css` → `@tailwind base; @tailwind components; @tailwind utilities;`（モックと同一）
  - `app.js` → `import Alpine ...; window.Alpine = Alpine; Alpine.start();`（モックと同一・bootstrap非依存）
  - 未使用の `bootstrap.js`（今回一時作成したもの）は削除（モックに存在しないため）。
- 再ビルド結果:
```
$ sail npm run build
public/build/assets/app-CwIPWlPu.css  38.71 kB
public/build/assets/app-CcWZX_LD.js   54.11 kB   （従来配信中のJSと同一ハッシュ＝Alpine挙動維持）
✓ built in 970ms
```

---

## 9. 品質チェック

```
$ sail artisan test
Tests:    80 passed (249 assertions)

$ sail bin pint --test
PASS ... 107 files
```

変更ファイル一覧:
```
 M app/Http/Controllers/BookController.php
 M database/seeders/BookSeeder.php
 M resources/css/app.css
 M resources/js/app.js
 M resources/views/auth/login.blade.php
 M resources/views/auth/register.blade.php
 M resources/views/books/index.blade.php
 M resources/views/favorites/index.blade.php
 ?? resources/views/vendor/pagination/   （publish + tailwind.blade.php修正）
```

---

## 11. 追加修正：ページネーションの色（ダークモード自動発動の無効化）

再実機で「アプリのページネーションがダーク色、モックはライト色」と指摘。原因は、Tailwindページネータの `dark:bg-gray-800` 等の `dark:` バリアントが、`tailwind.config.js` に `darkMode` 未設定（＝デフォルト `media`）のため、閲覧環境のOSダークモードで自動発動していたこと。モックはライト専用デザインで、アプリにダークモード切替も存在しない。

- 修正: `tailwind.config.js` に `darkMode: 'class'` を追加。これにより全 `dark:` バリアント（ビュー全体で115箇所）は `.dark` クラス配下でのみ有効となり、`.dark` を付与しない本アプリでは常にライト表示になる（ページネーションに限らずアプリ全体がモックのライトデザインに一致）。

証拠（再ビルド後CSS）:
```
$ grep -c 'prefers-color-scheme: dark' public/build/assets/app-*.css
0
（＝OSダークモードによる自動ダーク配色は生成されない。dark:はすべて「.dark クラス」ゲート化）
```
ページネータのライト配色クラス（`bg-white` / `border-gray-300` / `text-gray-500`）がそのまま適用され、モック（ライト）と同系になる。

---

## 10. 再実機検品の観点

- 書籍一覧: ★評価が消えていること／タイトルの太さ（font-extrabold）が意図通りか／ページネーションが左下グルーピングか。
- ログイン: 会員登録ボタンが無いこと。
- 会員登録: 「アカウントをお持ちの方」リンクからログインへ遷移できること。
- お気に入り一覧: 表紙画像クリックで詳細へ遷移すること。
- 書籍詳細①: 説明文がデザインUI画像と一致すること。
- 項目2（文字の太さ）は主観指定のため、程度の可否を要確認。
