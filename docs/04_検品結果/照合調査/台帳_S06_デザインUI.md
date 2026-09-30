# 照合台帳 S06 シート6 デザインUI

- 対象xlsx: docs/00_正本/ の要件シート
- シート名: シート6 デザインUI
- セル数: 32 ／ 行数（L）: 34 ／ 画像: 22
- 記入規則: docs/02_発注書/99_要件シート照合調査.md の §6
- このファイルのブロック・L行の削除、並べ替え、L行本文の編集は禁止。「事実:」行の記入と追加のみ行う。

---
### S06!B2
- L1: デザインUI
  - 事実: N/A-記述（シート見出し）

### S06!D3
- L1: 各画面のUI画像を添付してあります。
  - 事実: N/A-記述（シートの説明文。画像添付の案内）
- L2: 模擬案件の実装に入る前に内容を確認し、画面要件の理解に役立ててください。
  - 事実: N/A-記述（受講者向けの作業上の案内文）
- L3: 赤字（★マーク）は応用機能で追加・変更される画面です。
  - 事実: N/A-記述（シート内の表記ルール（赤字・★マーク）の説明）

### S06!D6
- L1: 書籍管理
  - 事実: N/A-記述（画面グループの見出し）

### S06!D7
- L1: 書籍一覧画面
  - 事実: 実行結果.md R12 GET|HEAD books ... books.index › BookController@index
  - 事実: app/Http/Controllers/BookController.php:52 return view('books.index', [
  - 事実: resources/views/books/index.blade.php:4 {{ __('書籍一覧') }}

### S06!L7
- L1: 書籍詳細画面
  - 事実: 実行結果.md R12 GET|HEAD books/{book} ... books.show › BookController@show
  - 事実: app/Http/Controllers/BookController.php:152 return view('books.show', compact('book'));
  - 事実: resources/views/books/show.blade.php:4 {{ $book->title }}

### S06!S7
- L1: 書籍登録画面
  - 事実: 実行結果.md R12 GET|HEAD books/create ... books.create › BookController@create
  - 事実: app/Http/Controllers/BookController.php:122 return view('books.create', compact('genres'));
  - 事実: resources/views/books/create.blade.php:4 {{ __('書籍の登録') }}

### S06!AA7
- L1: 書籍編集画面
  - 事実: 実行結果.md R12 GET|HEAD books/{book}/edit ... books.edit › BookController@edit
  - 事実: app/Http/Controllers/BookController.php:164 return view('books.edit', compact('book', 'genres'));
  - 事実: resources/views/books/edit.blade.php:4 {{ __('書籍の編集') }}

### S06!D50
- L1: ジャンル管理
  - 事実: N/A-記述（画面グループの見出し）

### S06!D51
- L1: ジャンル一覧画面
  - 事実: 実行結果.md R12 GET|HEAD genres ... genres.index › GenreController@index
  - 事実: app/Http/Controllers/GenreController.php:20 return view('genres.index', compact('genres'));
  - 事実: resources/views/genres/index.blade.php:4 ジャンル管理

### S06!L51
- L1: ジャンル詳細画面
  - 事実: 実行結果.md R12 GET|HEAD genres/{genre} ... genres.show › GenreController@show
  - 事実: app/Http/Controllers/GenreController.php:48 return view('genres.show', compact('genre', 'books'));
  - 事実: resources/views/genres/show.blade.php:4 ジャンル: {{ $genre->name }}

### S06!S51
- L1: ジャンル登録画面
  - 事実: 実行結果.md R12 GET|HEAD genres/create ... genres.create › GenreController@create
  - 事実: app/Http/Controllers/GenreController.php:28 return view('genres.create');
  - 事実: resources/views/genres/create.blade.php:4 ジャンル登録

### S06!Z51
- L1: ジャンル編集画面
  - 事実: 実行結果.md R12 GET|HEAD genres/{genre}/edit ... genres.edit › GenreController@edit
  - 事実: app/Http/Controllers/GenreController.php:56 return view('genres.edit', compact('genre'));
  - 事実: resources/views/genres/edit.blade.php:4 ジャンル編集

### S06!D52
- L1: 書籍編集
  - 事実: 実行結果.md R12 GET|HEAD books/{book}/edit ... books.edit › BookController@edit
  - 事実: resources/views/books/edit.blade.php:4 {{ __('書籍の編集') }}

### S06!D74
- L1: レビュー
  - 事実: N/A-記述（画面グループの見出し）

### S06!D75
- L1: レビュー投稿画面
  - 事実: 該当なし grep -rn "reviews.create\|reviews/create" routes/ resources/views/ app/ → 出力0件
  - 事実: 実行結果.md R12 POST books/{book}/reviews reviews.store › ReviewController@store
  - 事実: resources/views/books/show.blade.php:120 <h3 class="font-semibold mb-3">レビューを投稿</h3>
  - 事実: resources/views/books/show.blade.php:121 <form action="{{ route('reviews.store', $book) }}" method="POST" novalidate>

### S06!L75
- L1: レビュー編集画面
  - 事実: 実行結果.md R12 GET|HEAD reviews/{review}/edit . reviews.edit › ReviewController@edit
  - 事実: app/Http/Controllers/ReviewController.php:34 return view('reviews.edit', compact('review'));
  - 事実: resources/views/reviews/edit.blade.php:4 {{ __('レビューの編集') }}

### S06!D98
- L1: お気に入り
  - 事実: N/A-記述（画面グループの見出し）

### S06!D99
- L1: お気に入り登録画面
  - 事実: 該当なし grep -rn "favorites.create\|favorites/create" routes/ resources/views/ app/ → 出力0件
  - 事実: 実行結果.md R12 POST books/{book}/favorites favorites.toggle › FavoriteController@toggle
  - 事実: resources/views/books/show.blade.php:42 <button type="submit" class="text-red-500 hover:text-red-700" title="お気に入りから削除">
  - 事実: resources/views/books/show.blade.php:51 <button type="submit" class="text-gray-400 hover:text-red-500" title="お気に入りに追加">

### S06!L99
- L1: お気に入り一覧画面
  - 事実: 実行結果.md R12 GET|HEAD favorites ....... favorites.index › FavoriteController@index
  - 事実: app/Http/Controllers/FavoriteController.php:19 return view('favorites.index', compact('books'));
  - 事実: resources/views/favorites/index.blade.php:4 {{ __('お気に入り一覧') }}

### S06!D122
- L1: ランキング
  - 事実: N/A-記述（画面グループの見出し）

### S06!D123
- L1: ランキング画面
  - 事実: 実行結果.md R12 GET|HEAD ranking ............ ranking.index › RankingController@index
  - 事実: app/Http/Controllers/RankingController.php:23 return view('ranking.index', compact('rankedBooks'));
  - 事実: resources/views/ranking/index.blade.php:4 {{ __('評価ランキング TOP 10') }}

### S06!D159
- L1: 認証
  - 事実: N/A-記述（画面グループの見出し）

### S06!D160
- L1: 会員登録画面
  - 事実: 実行結果.md R12 GET|HEAD register register › Laravel\Fortify › RegisteredUserController@create
  - 事実: app/Providers/FortifyServiceProvider.php:35 return view('auth.register');
  - 事実: resources/views/auth/register.blade.php:39 {{ __('登録') }}

### S06!L160
- L1: ログイン画面
  - 事実: 実行結果.md R12 GET|HEAD login login › Laravel\Fortify › AuthenticatedSessionController@create
  - 事実: app/Providers/FortifyServiceProvider.php:31 return view('auth.login');
  - 事実: resources/views/auth/login.blade.php:20 {{ __('ログイン') }}

### S06!D182
- L1: ★ 応用機能（変更・追加画面）
  - 事実: N/A-記述（画面グループの見出し）

### S06!D183
- L1: ★ 書籍一覧画面（検索・フィルタ・ソート・CSV追加）
  - 事実: 実行結果.md R12 GET|HEAD books ... books.index › BookController@index
  - 事実: resources/views/books/index.blade.php:4 {{ __('書籍一覧') }}
  - 事実: resources/views/books/index.blade.php:15 <label for="keyword" class="block text-sm font-medium text-gray-700 mb-1">キーワード</label>
  - 事実: resources/views/books/index.blade.php:21 <label for="genre" class="block text-sm font-medium text-gray-700 mb-1">ジャンル</label>
  - 事実: resources/views/books/index.blade.php:31 <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">並び順</label>
  - 事実: app/Http/Controllers/BookController.php:30 $q->where('title', 'like', "%{$keyword}%")
  - 事実: app/Http/Controllers/BookController.php:37 $query->whereHas('genres', function ($q) use ($genre) {
  - 事実: app/Http/Controllers/BookController.php:43 match ($sort) {
  - 事実: 該当なし grep -rn "csv\|CSV" resources/views/ routes/web.php app/Http/Controllers/ → 出力0件

### S06!L183
- L1: ★ 書籍登録画面（ISBN検索追加）
  - 事実: 実行結果.md R12 GET|HEAD books/create ... books.create › BookController@create
  - 事実: 実行結果.md R12 GET|HEAD books/isbn/{isbn} books.searchByIsbn › BookController@searchByIsbn
  - 事実: resources/views/books/create.blade.php:13 <h3 class="font-medium text-gray-800 mb-1">📖 ISBN から書籍情報を自動入力</h3>
  - 事実: resources/views/books/create.blade.php:21 🔍 検索

### S06!D205
- L1: ★ マイ読書レポート画面
  - 事実: 実行結果.md R12 GET|HEAD reports ............. reports.index › ReportController@index
  - 事実: app/Http/Controllers/ReportController.php:34 return view('reports.index', compact('stats'));
  - 事実: resources/views/reports/index.blade.php:4 {{ __('マイ読書レポート') }}

### S06!D234
- L1: ★ 読書計画一覧画面
  - 事実: 実行結果.md R12 GET|HEAD reading-plans reading-plans.index › ReadingPlanController@index
  - 事実: app/Http/Controllers/ReadingPlanController.php:30 return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
  - 事実: resources/views/reading-plans/index.blade.php:4 読書計画

### S06!M234
- L1: ★ 読書計画作成画面
  - 事実: 実行結果.md R12 GET|HEAD reading-plans/create reading-plans.create › ReadingPlanController@create
  - 事実: app/Http/Controllers/ReadingPlanController.php:40 return view('reading-plans.create', compact('books'));
  - 事実: resources/views/reading-plans/create.blade.php:4 新規読書計画作成

### S06!U234
- L1: ★ 読書計画編集画面
  - 事実: 実行結果.md R12 GET|HEAD reading-plans/{plan}/edit reading-plans.edit › ReadingPlanController@edit
  - 事実: app/Http/Controllers/ReadingPlanController.php:66 return view('reading-plans.edit', ['readingPlan' => $plan]);
  - 事実: resources/views/reading-plans/edit.blade.php:4 読書計画編集

### S06!D258
- L1: ★ 通知一覧画面
  - 事実: 実行結果.md R12 GET|HEAD notifications notifications.index › NotificationController@index
  - 事実: app/Http/Controllers/NotificationController.php:19 return view('notifications.index', compact('notifications'));
  - 事実: resources/views/notifications/index.blade.php:4 通知一覧

---

## 画像（シート6に貼付された画面画像 22枚）

### S06!IMG@D7（xl/media/image1.png）
- 対応Blade: resources/views/books/index.blade.php（レイアウト resources/views/components/app-layout.blade.php・resources/views/layouts/navigation.blade.php・ページネーション resources/views/vendor/pagination/tailwind.blade.php）
- E1: 左上のロゴアイコン
  - 事実: resources/views/layouts/navigation.blade.php:15 <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
- E2: ナビリンク「書籍一覧」
  - 事実: resources/views/layouts/navigation.blade.php:22 {{ __('書籍一覧') }}
- E3: ナビリンク「ランキング」
  - 事実: resources/views/layouts/navigation.blade.php:25 {{ __('ランキング') }}
- E4: ナビリンク「書籍登録」
  - 事実: resources/views/layouts/navigation.blade.php:28 {{ __('書籍登録') }}
- E5: ナビリンク「お気に入り」
  - 事実: resources/views/layouts/navigation.blade.php:31 {{ __('お気に入り') }}
- E6: ナビリンク「ジャンル管理」
  - 事実: resources/views/layouts/navigation.blade.php:34 {{ __('ジャンル管理') }}
- E7: 右上のユーザー名「山田太郎」＋下向き矢印（ドロップダウン）
  - 事実: resources/views/layouts/navigation.blade.php:61 <div>{{ Auth::user()->name }}</div>
  - 事実: resources/views/layouts/navigation.blade.php:64 <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
  - 事実: 実行結果.md R100 1	山田太郎	yamada@example.com	NULL	2026-09-27 09:49:37	2026-09-27 09:49:37
- E8: ページ見出し「書籍一覧」
  - 事実: resources/views/books/index.blade.php:4 {{ __('書籍一覧') }}
- E9: ボタン「書籍を登録」（右寄せ・青）
  - 事実: resources/views/books/index.blade.php:51 <a href="{{ route('books.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/books/index.blade.php:52 書籍を登録
- E10: 書籍カード（3列グリッド、カード全体がリンク）
  - 事実: resources/views/books/index.blade.php:70 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  - 事実: resources/views/books/index.blade.php:72 <a href="{{ route('books.show', $book) }}" class="block border rounded-lg p-4 shadow hover:shadow-lg transition cursor-pointer">
- E11: カード内の表紙画像（数字「1」〜「10」の画像）
  - 事実: resources/views/books/index.blade.php:74 <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="w-full h-48 object-cover mb-4 rounded">
  - 事実: 実行結果.md R100 image_url 列 https://placehold.co/200x300/e2e8f0/475569?text=1 … https://placehold.co/200x300/e2e8f0/475569?text=11
- E12: カード内のタイトル（青字リンク色）「吾輩は猫である」「人を動かす」「リーダブルコード」「7つの習慣」「坊っちゃん」「サピエンス全史」「Clean Code」「嫌われる勇気」「火花」「FACTFULNESS」
  - 事実: resources/views/books/index.blade.php:80 <h3 class="font-extrabold text-lg mb-2 text-blue-600 hover:text-blue-800">
  - 事実: resources/views/books/index.blade.php:81 {{ $book->title }}
  - 事実: 実行結果.md R100 books 1	吾輩は猫である / 2	人を動かす / 3	リーダブルコード / 4	7つの習慣 / 5	坊っちゃん / 6	サピエンス全史 / 7	Clean Code / 8	嫌われる勇気 / 9	火花 / 10	FACTFULNESS / 11	コンテナ物語
- E13: カード内の著者名「夏目漱石」「D・カーネギー」「Dustin Boswell」ほか
  - 事実: resources/views/books/index.blade.php:83 <p class="text-gray-600 text-sm font-medium mb-2">{{ $book->author }}</p>
  - 事実: 実行結果.md R100 books author 列 夏目漱石 / D・カーネギー / Dustin Boswell / スティーブン・R・コヴィー / 夏目漱石 / ユヴァル・ノア・ハラリ / Robert C. Martin / 岸見一郎・古賀史健 / 又吉直樹 / ハンス・ロスリング / マルク・レビンソン
- E14: カード内のジャンルタグ（灰色バッジ）「小説」「ビジネス」「自己啓発」「技術書」「歴史」「科学」
  - 事実: resources/views/books/index.blade.php:86 <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded">{{ $genre->name }}</span>
  - 事実: 実行結果.md R100 book_genre 1	1 / 2	2 / 2	4 / 3	3 / 4	2 / 4	4 / 5	1 / 6	6 / 6	7 / 7	3 / 8	4 / 9	1 / 10	2 / 10	7 / 11	2 / 11	6
- E15: 1ページあたり10件の表示（カード10枚）
  - 事実: app/Http/Controllers/BookController.php:50 $books = $query->paginate(10)->withQueryString();
- E16: ページ情報「Showing 1 to 10 of 11 results」
  - 事実: resources/views/vendor/pagination/tailwind.blade.php:28 {!! __('Showing') !!}
  - 事実: resources/views/vendor/pagination/tailwind.blade.php:31 {!! __('to') !!}
  - 事実: resources/views/vendor/pagination/tailwind.blade.php:36 {!! __('of') !!}
  - 事実: resources/views/vendor/pagination/tailwind.blade.php:38 {!! __('results') !!}
  - 事実: resources/views/books/index.blade.php:94 {{ $books->links() }}
- E17: ページネーションボタン「<」「1」「2」「>」
  - 事実: resources/views/vendor/pagination/tailwind.blade.php:49 <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-
  - 事実: resources/views/vendor/pagination/tailwind.blade.php:79 {{ $page }}
  - 事実: resources/views/vendor/pagination/tailwind.blade.php:90 <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1

### S06!IMG@L7（xl/media/image2.png）
- 対応Blade: resources/views/books/show.blade.php（レイアウト resources/views/components/app-layout.blade.php・resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」・右上ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D7 E1〜E7
- E2: ページ見出し「吾輩は猫である」（書籍タイトル）
  - 事実: resources/views/books/show.blade.php:4 {{ $book->title }}
  - 事実: app/Http/Controllers/BookController.php:152 return view('books.show', compact('book'));
- E3: 左側の表紙画像（数字「1」）
  - 事実: resources/views/books/show.blade.php:25 <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="w-full rounded shadow">
  - 事実: 実行結果.md R100 books 1 image_url https://placehold.co/200x300/e2e8f0/475569?text=1
- E4: タイトル「吾輩は猫である」（本文内の大見出し）
  - 事実: resources/views/books/show.blade.php:34 <h1 class="text-2xl font-bold">{{ $book->title }}</h1>
- E5: 右上の赤い塗りつぶしハートアイコン（お気に入り登録済み）
  - 事実: resources/views/books/show.blade.php:39 @if(Auth::user()->favoriteBooks->contains($book->id))
  - 事実: resources/views/books/show.blade.php:42 <button type="submit" class="text-red-500 hover:text-red-700" title="お気に入りから削除">
  - 事実: resources/views/books/show.blade.php:43 <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24">
  - 事実: 実行結果.md R100 favorites 1	1
- E6: 「著者: 夏目漱石」
  - 事実: resources/views/books/show.blade.php:68 <p class="text-gray-600 mb-2"><strong>著者:</strong> {{ $book->author }}</p>
- E7: 「ISBN: 9784101010014」
  - 事実: resources/views/books/show.blade.php:69 <p class="text-gray-600 mb-2"><strong>ISBN:</strong> {{ $book->isbn ?? '未登録' }}</p>
  - 事実: 実行結果.md R100 1	吾輩は猫である	夏目漱石	9784101010014	1905-01-01
- E8: 「出版日: 1905-01-01」
  - 事実: resources/views/books/show.blade.php:70 <p class="text-gray-600 mb-2"><strong>出版日:</strong> {{ $book->published_date?->format('Y-m-d') ?? '未登録' }}</p>
- E9: 「ジャンル:」＋灰色バッジ「小説」
  - 事実: resources/views/books/show.blade.php:72 <strong>ジャンル:</strong>
  - 事実: resources/views/books/show.blade.php:74 <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded">{{ $genre->name }}</span>
- E10: 「説明:」＋説明文「中学校の英語教師である珍野苦沙弥の家に飼われている猫である「吾輩」の視点から、…を風刺的に描いた作品。」
  - 事実: resources/views/books/show.blade.php:79 <strong>説明:</strong>
  - 事実: resources/views/books/show.blade.php:80 <p class="mt-2 text-gray-700">{{ $book->description }}</p>
  - 事実: 実行結果.md R100 1	吾輩は猫である	夏目漱石	9784101010014	1905-01-01	中学校の英語教師である珍野苦沙弥の家に飼われている猫である「吾輩」の視点から、珍野一家や、そこに出入りする人々の様子を風刺的に描いた
- E11: ボタン「編集」（黄色）
  - 事実: resources/views/books/show.blade.php:85 @can('update', $book)
  - 事実: resources/views/books/show.blade.php:86 <a href="{{ route('books.edit', $book) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/books/show.blade.php:87 編集
- E12: ボタン「削除」（赤）
  - 事実: resources/views/books/show.blade.php:90 @can('delete', $book)
  - 事実: resources/views/books/show.blade.php:91 <form action="{{ route('books.destroy', $book) }}" method="POST" onsubmit="return confirm('本当に削除しますか？')" novalidate>
  - 事実: resources/views/books/show.blade.php:95 削除
- E13: 区切り線の下の見出し「レビュー」
  - 事実: resources/views/books/show.blade.php:113 <div class="mt-8 pt-8 border-t border-gray-200">
  - 事実: resources/views/books/show.blade.php:114 <h2 class="text-xl font-bold mb-4">レビュー</h2>
- E14: 投稿フォーム見出し「レビューを投稿」
  - 事実: resources/views/books/show.blade.php:120 <h3 class="font-semibold mb-3">レビューを投稿</h3>
- E15: ラベル「評価」＋セレクト（初期表示「選択してください」）
  - 事実: resources/views/books/show.blade.php:124 <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">評価</label>
  - 事実: resources/views/books/show.blade.php:126 <option value="">選択してください</option>
  - 事実: resources/views/books/show.blade.php:127 @for($i = 5; $i >= 1; $i--)
  - 事実: resources/views/books/show.blade.php:129 {{ str_repeat('★', $i) }}{{ str_repeat('☆', 5 - $i) }} ({{ $i }})
- E16: ラベル「コメント」＋テキストエリア（placeholder「この書籍の感想を書いてください」）
  - 事実: resources/views/books/show.blade.php:138 <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">コメント</label>
  - 事実: resources/views/books/show.blade.php:139 <textarea name="comment" id="comment" rows="3"
  - 事実: resources/views/books/show.blade.php:141 placeholder="この書籍の感想を書いてください">{{ old('comment') }}</textarea>
- E17: ボタン「投稿する」（青・右寄せ）
  - 事実: resources/views/books/show.blade.php:146 <div class="flex justify-end">
  - 事実: resources/views/books/show.blade.php:148 投稿する
- E18: レビューカード1「山田太郎」「★★★★★」「2026/04/15」「日本文学の傑作。猫の視点から人間社会を風刺する手法が秀逸です。」
  - 事実: resources/views/books/show.blade.php:169 <span class="font-semibold">{{ $review->user->name }}</span>
  - 事実: resources/views/books/show.blade.php:171 {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
  - 事実: resources/views/books/show.blade.php:174 <span class="text-sm text-gray-500">{{ $review->created_at->format('Y/m/d') }}</span>
  - 事実: resources/views/books/show.blade.php:177 <p class="text-gray-700">{{ $review->comment }}</p>
  - 事実: app/Http/Controllers/BookController.php:147 'reviews' => fn ($q) => $q->latest(),
  - 事実: 実行結果.md R100 reviews 1	1	1	3	普通でした。	2026-09-27 09:49:38	2026-09-27 09:49:38
- E19: レビューカード1の「いいね (2)」（線画の親指アイコン）
  - 事実: resources/views/books/show.blade.php:197 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 20 20">
  - 事実: resources/views/books/show.blade.php:200 いいね ({{ $review->likedByUsers->count() }})
- E20: レビューカード1の右下「編集」（灰色）「削除」（赤）
  - 事実: resources/views/books/show.blade.php:216 <a href="{{ route('reviews.edit', $review) }}" class="text-sm text-gray-500 hover:text-gray-700">編集</a>
  - 事実: resources/views/books/show.blade.php:222 <button type="submit" class="text-sm text-red-500 hover:text-red-700">削除</button>
- E21: レビューカード2「鈴木花子」「★★★★☆」「2026/04/15」「古典的な作品ですが、今読んでも面白い。文体に慣れるまで少し時間がかかりました。」
  - 事実: resources/views/books/show.blade.php:169 <span class="font-semibold">{{ $review->user->name }}</span>
  - 事実: resources/views/books/show.blade.php:171 {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
  - 事実: 実行結果.md R100 reviews 2	2	1	1	残念ながら合いませんでした。	2026-09-27 09:49:38	2026-09-27 09:49:38
- E22: レビューカード2の「いいね済み (1)」（青・塗りつぶし親指アイコン）
  - 事実: resources/views/books/show.blade.php:183 @if(Auth::user()->likedReviews->contains($review->id))
  - 事実: resources/views/books/show.blade.php:186 <button type="submit" class="text-blue-500 hover:text-blue-700 text-sm flex items-center">
  - 事実: resources/views/books/show.blade.php:190 いいね済み ({{ $review->likedByUsers->count() }})
- E23: レビューカード3「田中一郎」「★★★★★」「2026/04/15」「何度読んでも新しい発見がある名作です。」「いいね (3)」
  - 事実: resources/views/books/show.blade.php:169 <span class="font-semibold">{{ $review->user->name }}</span>
  - 事実: resources/views/books/show.blade.php:200 いいね ({{ $review->likedByUsers->count() }})
  - 事実: 実行結果.md R100 reviews 3	3	1	4	期待通りの内容でした。	2026-09-27 09:49:38	2026-09-27 09:49:38
- E24: リンク「← 一覧に戻る」
  - 事実: resources/views/books/show.blade.php:238 <a href="{{ route('books.index') }}" class="text-blue-600 hover:underline">← 一覧に戻る</a>

### S06!IMG@S7（xl/media/image3.png）
- 対応Blade: resources/views/books/create.blade.php（フォーム部品 resources/views/books/_form.blade.php、レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」・右上ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D7 E1〜E7
- E2: ナビの「書籍登録」に下線（アクティブ表示）
  - 事実: resources/views/layouts/navigation.blade.php:27 <x-nav-link :href="route('books.create')" :active="request()->routeIs('books.create')">
- E3: ページ見出し「書籍の登録」
  - 事実: resources/views/books/create.blade.php:4 {{ __('書籍の登録') }}
  - 事実: app/Http/Controllers/BookController.php:122 return view('books.create', compact('genres'));
- E4: ラベル「タイトル」＋赤い「*」、入力欄（値「テスト本」）
  - 事実: resources/views/books/_form.blade.php:10 タイトル <span class="text-red-500">*</span>
  - 事実: resources/views/books/_form.blade.php:12 <input type="text" name="title" id="title" value="{{ old('title', $book->title ?? '') }}"
  - 事実: resources/views/books/_form.blade.php:14 placeholder="書籍のタイトルを入力">
- E5: ラベル「著者」＋赤い「*」、入力欄（値「テスト著者」）
  - 事実: resources/views/books/_form.blade.php:23 著者 <span class="text-red-500">*</span>
  - 事実: resources/views/books/_form.blade.php:25 <input type="text" name="author" id="author" value="{{ old('author', $book->author ?? '') }}"
  - 事実: resources/views/books/_form.blade.php:27 placeholder="著者名を入力">
- E6: ラベル「ISBN-13」＋赤い「*」、入力欄（値「1234567890123」）
  - 事実: resources/views/books/_form.blade.php:35 <label for="isbn" class="block font-medium text-sm text-gray-700 mb-1">
  - 事実: resources/views/books/_form.blade.php:36 ISBN-13
  - 事実: resources/views/books/_form.blade.php:38 <input type="text" name="isbn" id="isbn" value="{{ old('isbn', $book->isbn ?? '') }}"
  - 事実: resources/views/books/_form.blade.php:40 placeholder="9784000000000">
- E7: ヘルプ文「13桁のISBNコードを入力してください」
  - 事実: resources/views/books/_form.blade.php:41 <p class="text-xs text-gray-500 mt-1">13桁のISBNコードを入力してください</p>
- E8: ラベル「出版日」＋赤い「*」、日付入力欄（値「2026/04/15」・カレンダーアイコン）
  - 事実: resources/views/books/_form.blade.php:50 出版日
  - 事実: resources/views/books/_form.blade.php:52 <input type="date" name="published_date" id="published_date" value="{{ old('published_date', $book->published_date ?? '') }}"
- E9: ラベル「説明」、テキストエリア（値「頑張りましょう🔥」）
  - 事実: resources/views/books/_form.blade.php:62 説明
  - 事実: resources/views/books/_form.blade.php:64 <textarea name="description" id="description" rows="4"
  - 事実: resources/views/books/_form.blade.php:66 placeholder="書籍の説明を入力（任意）">{{ old('description', $book->description ?? '') }}</textarea>
- E10: ラベル「画像URL」、入力欄（placeholder「https://example.com/image.jpg」）
  - 事実: resources/views/books/_form.blade.php:75 画像URL
  - 事実: resources/views/books/_form.blade.php:79 placeholder="https://example.com/image.jpg">
- E11: ヘルプ文「書籍の表紙画像のURLを入力してください（任意）」
  - 事実: resources/views/books/_form.blade.php:80 <p class="text-xs text-gray-500 mt-1">書籍の表紙画像のURLを入力してください（任意）</p>
- E12: ラベル「ジャンル」＋赤い「*」
  - 事実: resources/views/books/_form.blade.php:89 ジャンル <span class="text-red-500">*</span>
- E13: ジャンルのチェックボックス3列（「小説」「ビジネス」（チェック済み）「技術書」「自己啓発」「エッセイ」「歴史」「科学」「芸術」「料理」「旅行」）
  - 事実: resources/views/books/_form.blade.php:95 <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
  - 事実: resources/views/books/_form.blade.php:98 <input type="checkbox" name="genres[]" value="{{ $genre->id }}"
  - 事実: resources/views/books/_form.blade.php:100 @if(in_array($genre->id, old('genres', $bookGenreIds))) checked @endif>
  - 事実: resources/views/books/_form.blade.php:101 <span class="ml-2 text-sm text-gray-700">{{ $genre->name }}</span>
  - 事実: app/Http/Controllers/BookController.php:120 $genres = Genre::orderBy('id')->get();
  - 事実: 実行結果.md R100 genres 1	小説 / 2	ビジネス / 3	技術書 / 4	自己啓発 / 5	エッセイ / 6	歴史 / 7	科学 / 8	芸術 / 9	料理 / 10	旅行
- E14: 区切り線の下、リンク「キャンセル」
  - 事実: resources/views/books/create.blade.php:30 <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
  - 事実: resources/views/books/create.blade.php:31 <a href="{{ route('books.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
  - 事実: resources/views/books/create.blade.php:32 キャンセル
- E15: ボタン「登録」（青）
  - 事実: resources/views/books/create.blade.php:34 <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
  - 事実: resources/views/books/create.blade.php:35 登録

### S06!IMG@AA7（xl/media/image4.png）
- 対応Blade: resources/views/books/edit.blade.php（フォーム部品 resources/views/books/_form.blade.php、レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」・右上ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D7 E1〜E7
- E2: ページ見出し「書籍の編集」
  - 事実: resources/views/books/edit.blade.php:4 {{ __('書籍の編集') }}
  - 事実: app/Http/Controllers/BookController.php:164 return view('books.edit', compact('book', 'genres'));
- E3: ラベル「タイトル」＋赤い「*」、入力欄（値「テスト本」）
  - 事実: resources/views/books/edit.blade.php:14 @include('books._form')
  - 事実: 同上 S06!IMG@S7 E4
- E4: ラベル「著者」＋赤い「*」、入力欄（値「テスト著者」）
  - 事実: 同上 S06!IMG@S7 E5
- E5: ラベル「ISBN-13」＋赤い「*」、入力欄（値「1234567890123」）
  - 事実: 同上 S06!IMG@S7 E6
- E6: ヘルプ文「13桁のISBNコードを入力してください」
  - 事実: 同上 S06!IMG@S7 E7
- E7: ラベル「出版日」＋赤い「*」、日付入力欄（値「2026/04/15」・カレンダーアイコン）
  - 事実: 同上 S06!IMG@S7 E8
- E8: ラベル「説明」、テキストエリア（値「頑張りましょう🔥」）
  - 事実: 同上 S06!IMG@S7 E9
- E9: ラベル「画像URL」、入力欄（placeholder「https://example.com/image.jpg」）
  - 事実: 同上 S06!IMG@S7 E10
- E10: ヘルプ文「書籍の表紙画像のURLを入力してください（任意）」
  - 事実: 同上 S06!IMG@S7 E11
- E11: ラベル「ジャンル」＋赤い「*」
  - 事実: 同上 S06!IMG@S7 E12
- E12: ジャンルのチェックボックス3列（「小説」「ビジネス」（チェック済み）「技術書」「自己啓発」「エッセイ」「歴史」「科学」「芸術」「料理」「旅行」）
  - 事実: resources/views/books/_form.blade.php:2 $bookGenreIds = isset($book) ? $book->genres->pluck('id')->toArray() : [];
  - 事実: 同上 S06!IMG@S7 E13
  - 事実: app/Http/Controllers/BookController.php:162 $genres = Genre::orderBy('id')->get();
- E13: 区切り線の下、リンク「キャンセル」
  - 事実: resources/views/books/edit.blade.php:16 <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
  - 事実: resources/views/books/edit.blade.php:17 <a href="{{ route('books.show', $book) }}" class="text-gray-600 hover:text-gray-900 mr-4">
  - 事実: resources/views/books/edit.blade.php:18 キャンセル
- E14: ボタン「更新」（青）
  - 事実: resources/views/books/edit.blade.php:20 <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
  - 事実: resources/views/books/edit.blade.php:21 更新

### S06!IMG@D51（xl/media/image5.png）
- 対応Blade: resources/views/genres/index.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」・右上ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D7 E1〜E7
- E2: ナビの「ジャンル管理」に下線（アクティブ表示）
  - 事実: resources/views/layouts/navigation.blade.php:33 <x-nav-link :href="route('genres.index')" :active="request()->routeIs('genres.*')">
- E3: ページ見出し「ジャンル管理」
  - 事実: resources/views/genres/index.blade.php:4 ジャンル管理
  - 事実: app/Http/Controllers/GenreController.php:20 return view('genres.index', compact('genres'));
- E4: ボタン「ジャンルを登録」（青、左上に配置）
  - 事実: resources/views/genres/index.blade.php:10 <div class="mb-4 flex justify-end">
  - 事実: resources/views/genres/index.blade.php:11 <a href="{{ route('genres.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/genres/index.blade.php:12 ジャンルを登録
- E5: 表の列見出し「ジャンル名」「書籍数」「操作」
  - 事実: resources/views/genres/index.blade.php:36 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ジャンル名</th>
  - 事実: resources/views/genres/index.blade.php:37 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">書籍数</th>
  - 事実: resources/views/genres/index.blade.php:38 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
- E6: ジャンル名（青字リンク）「小説」「ビジネス」「技術書」「自己啓発」「エッセイ」「歴史」「科学」「芸術」「料理」「旅行」
  - 事実: resources/views/genres/index.blade.php:45 <a href="{{ route('genres.show', $genre) }}" class="text-blue-600 hover:text-blue-800">
  - 事実: resources/views/genres/index.blade.php:46 {{ $genre->name }}
  - 事実: app/Http/Controllers/GenreController.php:18 $genres = Genre::withCount('books')->orderBy('id')->get();
  - 事実: 実行結果.md R100 genres 1	小説 / 2	ビジネス / 3	技術書 / 4	自己啓発 / 5	エッセイ / 6	歴史 / 7	科学 / 8	芸術 / 9	料理 / 10	旅行
- E7: 書籍数「3冊」「5冊」「2冊」「3冊」「0冊」「2冊」「2冊」「0冊」「0冊」「0冊」
  - 事実: resources/views/genres/index.blade.php:49 <td class="px-6 py-4 whitespace-nowrap">{{ $genre->books_count }}冊</td>
  - 事実: 実行結果.md R100 book_genre 1	1 / 2	2 / 2	4 / 3	3 / 4	2 / 4	4 / 5	1 / 6	6 / 6	7 / 7	3 / 8	4 / 9	1 / 10	2 / 10	7 / 11	2 / 11	6
- E8: 各行の操作リンク「編集」（紫）
  - 事実: resources/views/genres/index.blade.php:51 <a href="{{ route('genres.edit', $genre) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">編集</a>
- E9: 各行の操作ボタン「削除」（赤）
  - 事実: resources/views/genres/index.blade.php:52 <form action="{{ route('genres.destroy', $genre) }}" method="POST" class="inline" onsubmit="return confirm('本当に削除しますか？');" novali
  - 事実: resources/views/genres/index.blade.php:55 <button type="submit" class="text-red-600 hover:text-red-900">削除</button>

### S06!IMG@L51（xl/media/image6.png）
- 対応Blade: resources/views/genres/show.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」・右上ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D7 E1〜E7
- E2: ナビの「ジャンル管理」に下線（アクティブ表示）
  - 事実: 同上 S06!IMG@D51 E2
- E3: ページ見出し「ジャンル: 小説」
  - 事実: resources/views/genres/show.blade.php:4 ジャンル: {{ $genre->name }}
  - 事実: app/Http/Controllers/GenreController.php:48 return view('genres.show', compact('genre', 'books'));
- E4: リンク「← 書籍一覧に戻る」
  - 事実: resources/views/genres/show.blade.php:11 <a href="{{ route('books.index') }}" class="text-blue-600 hover:text-blue-800">← 書籍一覧に戻る</a>
- E5: 書籍カード3列（カード全体がリンク）
  - 事実: resources/views/genres/show.blade.php:19 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  - 事実: resources/views/genres/show.blade.php:21 <a href="{{ route('books.show', $book) }}" class="block border rounded-lg p-4 shadow hover:shadow-lg transition">
  - 事実: app/Http/Controllers/GenreController.php:46 $books = $genre->books()->with('genres')->latest('books.created_at')->paginate(10);
- E6: カード内の表紙画像（数字「1」「5」「9」）
  - 事実: resources/views/genres/show.blade.php:23 <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="w-full h-48 object-cover mb-4 rounded">
- E7: カード内のタイトル（青字）「吾輩は猫である」「坊っちゃん」「火花」
  - 事実: resources/views/genres/show.blade.php:29 <h3 class="font-bold text-lg mb-2 text-blue-600">{{ $book->title }}</h3>
  - 事実: 実行結果.md R100 book_genre 1	1 / 5	1 / 9	1
- E8: カード内の著者「夏目漱石」「夏目漱石」「又吉直樹」
  - 事実: resources/views/genres/show.blade.php:30 <p class="text-gray-600 text-sm mb-2">{{ $book->author }}</p>
- E9: カード内のジャンルタグ（灰色バッジ）「小説」
  - 事実: resources/views/genres/show.blade.php:33 <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded {{ $g->id === $genre->id ? 'bg-blue-200 text-blue-700' : '' }}">
  - 事実: resources/views/genres/show.blade.php:34 {{ $g->name }}

### S06!IMG@S51（xl/media/image7.png）
- 対応Blade: resources/views/genres/create.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」・右上ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D7 E1〜E7
- E2: ナビの「ジャンル管理」に下線（アクティブ表示）
  - 事実: 同上 S06!IMG@D51 E2
- E3: ページ見出し「ジャンル登録」
  - 事実: resources/views/genres/create.blade.php:4 ジャンル登録
  - 事実: app/Http/Controllers/GenreController.php:28 return view('genres.create');
- E4: ラベル「ジャンル名」＋赤い「*」
  - 事実: resources/views/genres/create.blade.php:15 <label for="name" class="block text-sm font-medium text-gray-700">ジャンル名 <span class="text-red-500">*</span></label>
- E5: 入力欄（値「スプリチュアル」）
  - 事実: resources/views/genres/create.blade.php:16 <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-i
- E6: リンク「キャンセル」
  - 事実: resources/views/genres/create.blade.php:23 <a href="{{ route('genres.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">キャンセル</a>
- E7: ボタン「登録」（青）
  - 事実: resources/views/genres/create.blade.php:24 <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/genres/create.blade.php:25 登録

### S06!IMG@Z51（xl/media/image8.png）
- 対応Blade: resources/views/genres/edit.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」・右上ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D7 E1〜E7
- E2: ナビの「ジャンル管理」に下線（アクティブ表示）
  - 事実: 同上 S06!IMG@D51 E2
- E3: ページ見出し「ジャンル編集」
  - 事実: resources/views/genres/edit.blade.php:4 ジャンル編集
  - 事実: app/Http/Controllers/GenreController.php:56 return view('genres.edit', compact('genre'));
- E4: ラベル「ジャンル名」＋赤い「*」
  - 事実: resources/views/genres/edit.blade.php:16 <label for="name" class="block text-sm font-medium text-gray-700">ジャンル名 <span class="text-red-500">*</span></label>
- E5: 入力欄（値「スプリチュアルを編集中」）
  - 事実: resources/views/genres/edit.blade.php:17 <input type="text" name="name" id="name" value="{{ old('name', $genre->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-50
- E6: リンク「キャンセル」
  - 事実: resources/views/genres/edit.blade.php:24 <a href="{{ route('genres.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">キャンセル</a>
- E7: ボタン「更新」（青）
  - 事実: resources/views/genres/edit.blade.php:25 <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/genres/edit.blade.php:26 更新

### S06!IMG@D75（xl/media/image9.png）
- 対応Blade: resources/views/books/show.blade.php（レビュー投稿フォームは書籍詳細画面内。独立したレビュー投稿画面のBladeは resources/views/reviews/ 配下に edit.blade.php のみ）
- E1: 左側の画像なし領域（灰色）＋文字「画像なし」
  - 事実: resources/views/books/show.blade.php:27 <div class="w-full h-64 bg-gray-200 flex items-center justify-center rounded">
  - 事実: resources/views/books/show.blade.php:28 <span class="text-gray-500">画像なし</span>
- E2: タイトル「テスト本」
  - 事実: resources/views/books/show.blade.php:34 <h1 class="text-2xl font-bold">{{ $book->title }}</h1>
- E3: 右上の灰色の線画ハートアイコン（お気に入り未登録）
  - 事実: resources/views/books/show.blade.php:51 <button type="submit" class="text-gray-400 hover:text-red-500" title="お気に入りに追加">
  - 事実: resources/views/books/show.blade.php:52 <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
- E4: 「著者: テスト著者」
  - 事実: resources/views/books/show.blade.php:68 <p class="text-gray-600 mb-2"><strong>著者:</strong> {{ $book->author }}</p>
- E5: 「ISBN: 1234567890123」
  - 事実: resources/views/books/show.blade.php:69 <p class="text-gray-600 mb-2"><strong>ISBN:</strong> {{ $book->isbn ?? '未登録' }}</p>
- E6: 「出版日: 2026-04-15」
  - 事実: resources/views/books/show.blade.php:70 <p class="text-gray-600 mb-2"><strong>出版日:</strong> {{ $book->published_date?->format('Y-m-d') ?? '未登録' }}</p>
- E7: 「ジャンル:」＋灰色バッジ「ビジネス」
  - 事実: resources/views/books/show.blade.php:72 <strong>ジャンル:</strong>
  - 事実: resources/views/books/show.blade.php:74 <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded">{{ $genre->name }}</span>
- E8: 「説明:」＋「頑張りましょう🔥」
  - 事実: resources/views/books/show.blade.php:79 <strong>説明:</strong>
  - 事実: resources/views/books/show.blade.php:80 <p class="mt-2 text-gray-700">{{ $book->description }}</p>
- E9: ボタン「編集」（黄色）
  - 事実: resources/views/books/show.blade.php:86 <a href="{{ route('books.edit', $book) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/books/show.blade.php:87 編集
- E10: ボタン「削除」（赤）
  - 事実: resources/views/books/show.blade.php:94 <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/books/show.blade.php:95 削除
- E11: 見出し「レビュー」
  - 事実: resources/views/books/show.blade.php:114 <h2 class="text-xl font-bold mb-4">レビュー</h2>
- E12: 見出し「レビューを投稿」
  - 事実: resources/views/books/show.blade.php:120 <h3 class="font-semibold mb-3">レビューを投稿</h3>
- E13: ラベル「評価」＋セレクト（選択値「★★★★★ (5)」）
  - 事実: resources/views/books/show.blade.php:124 <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">評価</label>
  - 事実: resources/views/books/show.blade.php:125 <select name="rating" id="rating" class="border-gray-300 rounded-md shadow-sm">
  - 事実: resources/views/books/show.blade.php:129 {{ str_repeat('★', $i) }}{{ str_repeat('☆', 5 - $i) }} ({{ $i }})
- E14: ラベル「コメント」＋テキストエリア（値「レビューのテストです。」）
  - 事実: resources/views/books/show.blade.php:138 <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">コメント</label>
  - 事実: resources/views/books/show.blade.php:139 <textarea name="comment" id="comment" rows="3"
- E15: ボタン「投稿する」（青）
  - 事実: resources/views/books/show.blade.php:147 <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/books/show.blade.php:148 投稿する
  - 事実: resources/views/books/show.blade.php:121 <form action="{{ route('reviews.store', $book) }}" method="POST" novalidate>
  - 事実: app/Http/Controllers/ReviewController.php:22 return redirect()->route('books.show', $book)->with('success', 'レビューを投稿しました');
- E16: 空メッセージ「まだレビューはありません。」
  - 事実: resources/views/books/show.blade.php:231 <p class="text-gray-500">まだレビューはありません。</p>
- E17: リンク「← 一覧に戻る」
  - 事実: resources/views/books/show.blade.php:238 <a href="{{ route('books.index') }}" class="text-blue-600 hover:underline">← 一覧に戻る</a>

### S06!IMG@L75（xl/media/image10.png）
- 対応Blade: resources/views/reviews/edit.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」・右上ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D7 E1〜E7
- E2: ページ見出し「レビューの編集」
  - 事実: resources/views/reviews/edit.blade.php:4 {{ __('レビューの編集') }}
  - 事実: app/Http/Controllers/ReviewController.php:34 return view('reviews.edit', compact('review'));
- E3: 「書籍: テスト本」（書籍名は太字）
  - 事実: resources/views/reviews/edit.blade.php:13 <p class="text-gray-600">書籍: <span class="font-semibold">{{ $review->book->title }}</span></p>
- E4: ラベル「評価」＋赤い「*」
  - 事実: resources/views/reviews/edit.blade.php:21 <label class="block text-sm font-medium text-gray-700 mb-2">評価 <span class="text-red-500">*</span></label>
- E5: 星5個の選択（1個目が黄色、残り4個が灰色）
  - 事実: resources/views/reviews/edit.blade.php:23 @for($i = 1; $i <= 5; $i++)
  - 事実: resources/views/reviews/edit.blade.php:25 <input type="radio" name="rating" value="{{ $i }}" class="sr-only peer" {{ old('rating', $review->rating) == $i ? 'checked' : '' }} required>
  - 事実: resources/views/reviews/edit.blade.php:26 <span class="text-2xl peer-checked:text-yellow-400 text-gray-300 hover:text-yellow-400">★</span>
- E6: ラベル「コメント」＋テキストエリア（値「レビュー編集のテストです。」）
  - 事実: resources/views/reviews/edit.blade.php:36 <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">コメント</label>
  - 事実: resources/views/reviews/edit.blade.php:37 <textarea name="comment" id="comment" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('comment', $review->comment) }}</
- E7: リンク「キャンセル」
  - 事実: resources/views/reviews/edit.blade.php:44 <a href="{{ route('books.show', $review->book) }}" class="text-gray-600 hover:text-gray-900 mr-4">キャンセル</a>
- E8: ボタン「更新する」（青）
  - 事実: resources/views/reviews/edit.blade.php:45 <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/reviews/edit.blade.php:46 更新する

### S06!IMG@D98（xl/media/image11.png）
- 対応Blade: resources/views/books/show.blade.php（お気に入り登録は書籍詳細画面内のハートボタン。レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」・右上ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D7 E1〜E7
- E2: ページ見出し「テスト本」
  - 事実: resources/views/books/show.blade.php:4 {{ $book->title }}
- E3: 左側の画像なし領域（灰色）＋文字「画像なし」
  - 事実: 同上 S06!IMG@D75 E1
- E4: タイトル「テスト本」
  - 事実: 同上 S06!IMG@D75 E2
- E5: 右上の赤い塗りつぶしハートアイコン（お気に入り登録済み）
  - 事実: resources/views/books/show.blade.php:40 <form action="{{ route('favorites.toggle', $book) }}" method="POST" novalidate>
  - 事実: 同上 S06!IMG@L7 E5
  - 事実: app/Http/Controllers/FavoriteController.php:27 Auth::user()->favoriteBooks()->toggle($book);
  - 事実: app/Http/Controllers/FavoriteController.php:29 return back();
- E6: 「著者: テスト著者」
  - 事実: 同上 S06!IMG@D75 E4
- E7: 「ISBN: 1234567890123」
  - 事実: 同上 S06!IMG@D75 E5
- E8: 「出版日: 2026-04-15」
  - 事実: 同上 S06!IMG@D75 E6
- E9: 「ジャンル:」＋灰色バッジ「ビジネス」
  - 事実: 同上 S06!IMG@D75 E7
- E10: 「説明:」＋「頑張りましょう🔥」
  - 事実: 同上 S06!IMG@D75 E8
- E11: ボタン「編集」（黄色）
  - 事実: 同上 S06!IMG@D75 E9
- E12: ボタン「削除」（赤）
  - 事実: 同上 S06!IMG@D75 E10
- E13: 見出し「レビュー」
  - 事実: 同上 S06!IMG@D75 E11
- E14: 見出し「レビューを投稿」
  - 事実: 同上 S06!IMG@D75 E12
- E15: ラベル「評価」＋セレクト（初期表示「選択してください」）
  - 事実: resources/views/books/show.blade.php:124 <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">評価</label>
  - 事実: resources/views/books/show.blade.php:126 <option value="">選択してください</option>
- E16: ラベル「コメント」＋テキストエリア（placeholder「この書籍の感想を書いてください」）
  - 事実: resources/views/books/show.blade.php:138 <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">コメント</label>
  - 事実: resources/views/books/show.blade.php:141 placeholder="この書籍の感想を書いてください">{{ old('comment') }}</textarea>
- E17: ボタン「投稿する」（青）
  - 事実: resources/views/books/show.blade.php:148 投稿する

### S06!IMG@L98（xl/media/image12.png）
- 対応Blade: resources/views/favorites/index.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」・右上ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D7 E1〜E7
- E2: ナビの「お気に入り」に下線（アクティブ表示）
  - 事実: resources/views/layouts/navigation.blade.php:30 <x-nav-link :href="route('favorites.index')" :active="request()->routeIs('favorites.index')">
- E3: ページ見出し「お気に入り一覧」
  - 事実: resources/views/favorites/index.blade.php:4 {{ __('お気に入り一覧') }}
  - 事実: app/Http/Controllers/FavoriteController.php:19 return view('favorites.index', compact('books'));
- E4: 書籍カード3列（カード全体がリンク）
  - 事実: resources/views/favorites/index.blade.php:13 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  - 事実: resources/views/favorites/index.blade.php:17 <a href="{{ route('books.show', $book) }}" class="absolute inset-0 z-0" aria-label="{{ $book->title }} の詳細"></a>
  - 事実: app/Http/Controllers/FavoriteController.php:17 $books = Auth::user()->favoriteBooks()->latest('books.created_at')->paginate(10);
- E5: カード内の表紙画像（数字「1」「3」「6」「10」）
  - 事実: resources/views/favorites/index.blade.php:20 <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="w-full h-48 object-cover rounded mb-4">
- E6: 画像のないカードの灰色領域＋文字「No Image」
  - 事実: resources/views/favorites/index.blade.php:22 <div class="w-full h-48 bg-gray-200 rounded mb-4 flex items-center justify-center">
  - 事実: resources/views/favorites/index.blade.php:23 <span class="text-gray-400">No Image</span>
- E7: カード内のタイトル（青字）「吾輩は猫である」「リーダブルコード」「サピエンス全史」「FACTFULNESS」「テスト本」
  - 事実: resources/views/favorites/index.blade.php:27 <span class="text-blue-600">{{ $book->title }}</span>
  - 事実: 実行結果.md R100 favorites 1	1 / 1	2 / 1	3 / 1	4
- E8: カード内の著者「夏目漱石」「Dustin Boswell」「ユヴァル・ノア・ハラリ」「ハンス・ロスリング」「テスト著者」
  - 事実: resources/views/favorites/index.blade.php:29 <p class="text-gray-600 mb-2">{{ $book->author }}</p>
- E9: カード内の「ISBN: 9784101010014」ほか
  - 事実: resources/views/favorites/index.blade.php:31 <span class="text-sm text-gray-500">ISBN: {{ $book->isbn }}</span>
- E10: カード右下の赤い塗りつぶしハートアイコン
  - 事実: resources/views/favorites/index.blade.php:32 <form action="{{ route('favorites.toggle', $book) }}" method="POST" novalidate class="relative z-10">
  - 事実: resources/views/favorites/index.blade.php:34 <button type="submit" class="text-red-500 hover:text-red-700">
  - 事実: resources/views/favorites/index.blade.php:35 <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">

### S06!IMG@D122（xl/media/image13.png）
- 対応Blade: resources/views/ranking/index.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」・右上ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D7 E1〜E7
- E2: ナビの「ランキング」に下線（アクティブ表示）
  - 事実: resources/views/layouts/navigation.blade.php:24 <x-nav-link :href="route('ranking.index')" :active="request()->routeIs('ranking.index')">
- E3: ページ見出し「評価ランキング TOP 10」
  - 事実: resources/views/ranking/index.blade.php:4 {{ __('評価ランキング TOP 10') }}
  - 事実: app/Http/Controllers/RankingController.php:23 return view('ranking.index', compact('rankedBooks'));
- E4: 10件の行（各行が書籍へのリンク、1〜3位は黄色背景・黄色枠）
  - 事実: resources/views/ranking/index.blade.php:17 <a href="{{ route('books.show', $book) }}" class="block hover:bg-gray-50 transition rounded-lg">
  - 事実: resources/views/ranking/index.blade.php:18 <div class="flex items-center p-4 border rounded-lg {{ $index < 3 ? 'border-yellow-300 bg-yellow-50' : 'border-gray-200' }}">
  - 事実: app/Http/Controllers/RankingController.php:15 $rankedBooks = Book::withAvg('reviews', 'rating')
  - 事実: app/Http/Controllers/RankingController.php:17 ->whereHas('reviews')
  - 事実: app/Http/Controllers/RankingController.php:18 ->orderByDesc('reviews_avg_rating')
  - 事実: app/Http/Controllers/RankingController.php:19 ->orderBy('id')
  - 事実: app/Http/Controllers/RankingController.php:20 ->limit(10)
- E5: 順位の丸バッジ「1」（金）「2」（銀）「3」（銅）「4」〜「10」（灰）
  - 事実: resources/views/ranking/index.blade.php:20 <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full {{ $index === 0 ? 'bg-yellow-400 text-white' : ($index === 1 ? 'bg-gray-300 text-white' : ($index === 2 ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600')) }} font-bold text-xl mr-4">
  - 事実: resources/views/ranking/index.blade.php:21 {{ $index + 1 }}
- E6: 書籍画像のサムネイル（数字画像）
  - 事実: resources/views/ranking/index.blade.php:27 <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover rounded shadow">
- E7: 画像のない書籍のサムネイル「No Image」
  - 事実: resources/views/ranking/index.blade.php:30 <span class="text-gray-400 text-xs">No Image</span>
- E8: 書籍タイトル（青字）「テスト本」「サピエンス全史」「吾輩は猫である」「リーダブルコード」「7つの習慣」「FACTFULNESS」「人を動かす」「坊っちゃん」「Clean Code」「嫌われる勇気」
  - 事実: resources/views/ranking/index.blade.php:37 <h3 class="text-lg font-semibold text-blue-600 hover:text-blue-800 truncate">
  - 事実: resources/views/ranking/index.blade.php:38 {{ $book->title }}
- E9: 著者名「テスト著者」「ユヴァル・ノア・ハラリ」ほか
  - 事実: resources/views/ranking/index.blade.php:40 <p class="text-sm text-gray-600">{{ $book->author }}</p>
- E10: 星5個（黄色）
  - 事実: resources/views/ranking/index.blade.php:42 @for($i = 1; $i <= 5; $i++)
  - 事実: resources/views/ranking/index.blade.php:43 @if($i <= round($book->reviews_avg_rating))
  - 事実: resources/views/ranking/index.blade.php:44 <span class="text-yellow-400">★</span>
  - 事実: resources/views/ranking/index.blade.php:46 <span class="text-gray-300">★</span>
- E11: 小数2桁の平均「5.00」「4.75」「4.67」「4.50」
  - 事実: resources/views/ranking/index.blade.php:50 {{ number_format($book->reviews_avg_rating, 2) }}
- E12: 件数「(1件のレビュー)」「(4件のレビュー)」「(3件のレビュー)」「(2件のレビュー)」
  - 事実: resources/views/ranking/index.blade.php:53 ({{ $book->reviews_count }}件のレビュー)
  - 事実: app/Http/Controllers/RankingController.php:16 ->withCount('reviews')
- E13: 右側の大きな数値（小数1桁）「5.0」「4.8」「4.7」「4.5」（1〜3位は黄色、4位以下は灰色）
  - 事実: resources/views/ranking/index.blade.php:61 <div class="text-2xl font-bold {{ $index < 3 ? 'text-yellow-500' : 'text-gray-600' }}">
  - 事実: resources/views/ranking/index.blade.php:62 {{ number_format($book->reviews_avg_rating, 1) }}
- E14: 右側の数値下のラベル「平均評価」
  - 事実: resources/views/ranking/index.blade.php:64 <div class="text-xs text-gray-500">平均評価</div>

### S06!IMG@D159（xl/media/image14.png）
- 対応Blade: resources/views/auth/register.blade.php（レイアウト resources/views/components/guest-layout.blade.php）
- E1: 中央上のロゴアイコン（灰色・大）
  - 事実: resources/views/components/guest-layout.blade.php:20 <a href="/">
  - 事実: resources/views/components/guest-layout.blade.php:21 <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
  - 事実: app/Providers/FortifyServiceProvider.php:35 return view('auth.register');
- E2: ラベル「お名前」＋入力欄（値「テスト 太郎」）
  - 事実: resources/views/auth/register.blade.php:7 <x-input-label for="name" :value="__('お名前')" />
  - 事実: resources/views/auth/register.blade.php:8 <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" autofocus />
- E3: ラベル「メールアドレス」＋入力欄（値「test@example.com」）
  - 事実: resources/views/auth/register.blade.php:14 <x-input-label for="email" :value="__('メールアドレス')" />
  - 事実: resources/views/auth/register.blade.php:15 <x-text-input id="email" class="block mt-1 w-full" type="text" name="email" :value="old('email')" />
- E4: ラベル「パスワード」＋入力欄（伏字）
  - 事実: resources/views/auth/register.blade.php:21 <x-input-label for="password" :value="__('パスワード')" />
  - 事実: resources/views/auth/register.blade.php:22 <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
- E5: ラベル「パスワード確認」＋入力欄（伏字）
  - 事実: resources/views/auth/register.blade.php:28 <x-input-label for="password_confirmation" :value="__('パスワード確認')" />
  - 事実: resources/views/auth/register.blade.php:29 <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
- E6: 下線付きリンク「アカウントをお持ちの方」
  - 事実: resources/views/auth/register.blade.php:34 <a href="{{ route('login') }}" class="text-sm text-gray-600 underline hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
  - 事実: resources/views/auth/register.blade.php:35 {{ __('アカウントをお持ちの方') }}
- E7: ボタン「登録」（濃紺）
  - 事実: resources/views/auth/register.blade.php:38 <x-primary-button class="ml-4">
  - 事実: resources/views/auth/register.blade.php:39 {{ __('登録') }}
  - 事実: resources/views/components/primary-button.blade.php:1 <button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 a

### S06!IMG@L159（xl/media/image15.png）
- 対応Blade: resources/views/auth/login.blade.php（レイアウト resources/views/components/guest-layout.blade.php）
- E1: 中央上のロゴアイコン（灰色・大）
  - 事実: resources/views/components/guest-layout.blade.php:21 <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
  - 事実: app/Providers/FortifyServiceProvider.php:31 return view('auth.login');
- E2: ラベル「メールアドレス」＋入力欄（値「test@example.com」）
  - 事実: resources/views/auth/login.blade.php:6 <x-input-label for="email" :value="__('メールアドレス')" />
  - 事実: resources/views/auth/login.blade.php:7 <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" autofocus />
- E3: ラベル「パスワード」＋入力欄（伏字）
  - 事実: resources/views/auth/login.blade.php:13 <x-input-label for="password" :value="__('パスワード')" />
  - 事実: resources/views/auth/login.blade.php:14 <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
- E4: ボタン「ログイン」（濃紺）
  - 事実: resources/views/auth/login.blade.php:19 <x-primary-button>
  - 事実: resources/views/auth/login.blade.php:20 {{ __('ログイン') }}
  - 事実: resources/views/components/primary-button.blade.php:1 <button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 a

### S06!IMG@D182（xl/media/image16.png）
- 対応Blade: resources/views/books/index.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: 左上のロゴアイコン
  - 事実: resources/views/layouts/navigation.blade.php:15 <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
- E2: ナビリンク「書籍一覧」
  - 事実: resources/views/layouts/navigation.blade.php:22 {{ __('書籍一覧') }}
- E3: ナビリンク「ランキング」
  - 事実: resources/views/layouts/navigation.blade.php:25 {{ __('ランキング') }}
- E4: ナビリンク「書籍登録」
  - 事実: resources/views/layouts/navigation.blade.php:28 {{ __('書籍登録') }}
- E5: ナビリンク「お気に入り」
  - 事実: resources/views/layouts/navigation.blade.php:31 {{ __('お気に入り') }}
- E6: ナビリンク「ジャンル管理」
  - 事実: resources/views/layouts/navigation.blade.php:34 {{ __('ジャンル管理') }}
- E7: ナビリンク「マイレポート」
  - 事実: resources/views/layouts/navigation.blade.php:37 {{ __('マイレポート') }}
- E8: ナビリンク「読書計画」
  - 事実: resources/views/layouts/navigation.blade.php:40 {{ __('読書計画') }}
- E9: 右上のベルアイコン（未読バッジなし）
  - 事実: resources/views/layouts/navigation.blade.php:49 <a href="{{ route('notifications.index') }}" class="relative inline-flex items-center px-3 py-2 text-gray-500 hover:text-gray-700">
  - 事実: resources/views/layouts/navigation.blade.php:50 <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  - 事実: resources/views/layouts/navigation.blade.php:53 @if($unreadNotificationCount > 0)
  - 事実: resources/views/layouts/navigation.blade.php:54 <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">{{ $unreadNotificationCount }}</span>
- E10: 右上のユーザー名「山田太郎」＋下向き矢印
  - 事実: resources/views/layouts/navigation.blade.php:61 <div>{{ Auth::user()->name }}</div>
  - 事実: resources/views/layouts/navigation.blade.php:64 <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
- E11: ページ見出し「書籍一覧」
  - 事実: resources/views/books/index.blade.php:4 {{ __('書籍一覧') }}
  - 事実: app/Http/Controllers/BookController.php:52 return view('books.index', [
- E12: ラベル「キーワード」＋入力欄（placeholder「タイトル・著者で検索」）
  - 事実: resources/views/books/index.blade.php:15 <label for="keyword" class="block text-sm font-medium text-gray-700 mb-1">キーワード</label>
  - 事実: resources/views/books/index.blade.php:16 <input type="text" name="keyword" id="keyword" value="{{ request('keyword') }}"
  - 事実: resources/views/books/index.blade.php:18 placeholder="タイトル・著者で検索">
  - 事実: app/Http/Controllers/BookController.php:30 $q->where('title', 'like', "%{$keyword}%")
  - 事実: app/Http/Controllers/BookController.php:31 ->orWhere('author', 'like', "%{$keyword}%");
- E13: ラベル「ジャンル」＋セレクト（表示値「すべて」）
  - 事実: resources/views/books/index.blade.php:21 <label for="genre" class="block text-sm font-medium text-gray-700 mb-1">ジャンル</label>
  - 事実: resources/views/books/index.blade.php:24 <option value="">すべて</option>
  - 事実: resources/views/books/index.blade.php:26 <option value="{{ $genre->id }}" @selected((int) request('genre') === $genre->id)>{{ $genre->name }}</option>
  - 事実: app/Http/Controllers/BookController.php:54 'genres' => Genre::all(),
- E14: ラベル「並び順」＋セレクト（表示値「新しい順」）
  - 事実: resources/views/books/index.blade.php:31 <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">並び順</label>
  - 事実: resources/views/books/index.blade.php:34 <option value="newest" @selected(request('sort', 'newest') === 'newest')>新しい順</option>
  - 事実: resources/views/books/index.blade.php:35 <option value="oldest" @selected(request('sort') === 'oldest')>古い順</option>
  - 事実: resources/views/books/index.blade.php:36 <option value="title" @selected(request('sort') === 'title')>タイトル順</option>
  - 事実: resources/views/books/index.blade.php:37 <option value="rating" @selected(request('sort') === 'rating')>評価が高い順</option>
  - 事実: app/Http/Controllers/BookController.php:42 $sort = (string) $request->query('sort', 'newest');
- E15: ボタン「検索」（青）
  - 事実: resources/views/books/index.blade.php:44 <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/books/index.blade.php:45 検索
- E16: リンク「リセット」
  - 事実: resources/views/books/index.blade.php:47 <a href="{{ route('books.index') }}" class="text-gray-600 hover:text-gray-900">
  - 事実: resources/views/books/index.blade.php:48 リセット
- E17: ボタン「書籍を登録」（青・右寄せ）
  - 事実: resources/views/books/index.blade.php:51 <a href="{{ route('books.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/books/index.blade.php:52 書籍を登録
- E18: 書籍カード（表紙画像「1」「2」「3」…、タイトル「吾輩は猫である」「人を動かす」「リーダブルコード」、著者、ジャンルタグ）
  - 事実: 同上 S06!IMG@D7 E10〜E14

### S06!IMG@L182（xl/media/image17.png）
- 対応Blade: resources/views/books/create.blade.php（フォーム部品 resources/views/books/_form.blade.php、レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」「マイレポート」「読書計画」・ベルアイコン・ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D182 E1〜E10
- E2: ナビの「書籍登録」に下線（アクティブ表示）
  - 事実: 同上 S06!IMG@S7 E2
- E3: ページ見出し「書籍の登録」
  - 事実: 同上 S06!IMG@S7 E3
- E4: 本のアイコン＋見出し「ISBN から書籍情報を自動入力」
  - 事実: resources/views/books/create.blade.php:13 <h3 class="font-medium text-gray-800 mb-1">📖 ISBN から書籍情報を自動入力</h3>
- E5: 説明文「13桁の ISBN を入力すると、Google Books API から書籍情報を取得してフォームを自動補完します。」
  - 事実: resources/views/books/create.blade.php:14 <p class="text-sm text-gray-600 mb-3">13桁の ISBN を入力すると、Google Books API から書籍情報を取得してフォームを自動補完します。</p>
- E6: ISBN入力欄（placeholder「例: 9784101010014」）
  - 事実: resources/views/books/create.blade.php:16 <input type="text" id="isbn_search" value=""
  - 事実: resources/views/books/create.blade.php:18 placeholder="例: 9784101010014">
- E7: 虫眼鏡アイコン＋ボタン「検索」（青）
  - 事実: resources/views/books/create.blade.php:19 <button type="button" id="isbn_search_button"
  - 事実: resources/views/books/create.blade.php:21 🔍 検索
  - 事実: resources/views/books/create.blade.php:69 const response = await fetch(`/books/isbn/${encodeURIComponent(isbn)}`, {
  - 事実: app/Http/Controllers/BookController.php:73 $response = Http::timeout(5)->get('https://www.googleapis.com/books/v1/volumes', $query);
- E8: ラベル「タイトル」＋赤い「*」、入力欄（placeholder「書籍のタイトルを入力」）
  - 事実: resources/views/books/_form.blade.php:10 タイトル <span class="text-red-500">*</span>
  - 事実: resources/views/books/_form.blade.php:14 placeholder="書籍のタイトルを入力">
- E9: ラベル「著者」＋赤い「*」、入力欄（placeholder「著者名を入力」）
  - 事実: resources/views/books/_form.blade.php:23 著者 <span class="text-red-500">*</span>
  - 事実: resources/views/books/_form.blade.php:27 placeholder="著者名を入力">
- E10: ラベル「ISBN-13」、入力欄（placeholder「9784000000000」）
  - 事実: resources/views/books/_form.blade.php:36 ISBN-13
  - 事実: resources/views/books/_form.blade.php:40 placeholder="9784000000000">
- E11: ヘルプ文「13桁のISBNコードを入力してください」
  - 事実: resources/views/books/_form.blade.php:41 <p class="text-xs text-gray-500 mt-1">13桁のISBNコードを入力してください</p>
- E12: ラベル「出版日」、日付入力欄（「年 /月/日」・カレンダーアイコン）
  - 事実: resources/views/books/_form.blade.php:50 出版日
  - 事実: resources/views/books/_form.blade.php:52 <input type="date" name="published_date" id="published_date" value="{{ old('published_date', $book->published_date ?? '') }}"
- E13: ラベル「説明」、テキストエリア（placeholder「書籍の説明を入力（任意）」）
  - 事実: resources/views/books/_form.blade.php:62 説明
  - 事実: resources/views/books/_form.blade.php:66 placeholder="書籍の説明を入力（任意）">{{ old('description', $book->description ?? '') }}</textarea>

### S06!IMG@D203（xl/media/image18.png）
- 対応Blade: resources/views/reports/index.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」「マイレポート」「読書計画」・ベルアイコン・ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D182 E1〜E10
- E2: ナビの「マイレポート」に下線（アクティブ表示）
  - 事実: resources/views/layouts/navigation.blade.php:36 <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
- E3: ページ見出し「マイ読書レポート」
  - 事実: resources/views/reports/index.blade.php:4 {{ __('マイ読書レポート') }}
  - 事実: app/Http/Controllers/ReportController.php:34 return view('reports.index', compact('stats'));
- E4: セクション見出し「基本統計」
  - 事実: resources/views/reports/index.blade.php:13 <h3 class="text-lg font-semibold text-gray-900 mb-4">基本統計</h3>
- E5: 青い数値「7」＋ラベル「総レビュー数」
  - 事実: resources/views/reports/index.blade.php:16 <div class="text-4xl font-bold text-blue-600 mb-2">{{ $stats['summary']['total_reviews'] }}</div>
  - 事実: resources/views/reports/index.blade.php:17 <div class="text-sm text-gray-600">総レビュー数</div>
  - 事実: app/Http/Controllers/ReportController.php:48 'total_reviews' => $totalReviews,
- E6: 緑の数値「7」＋ラベル「読了冊数」
  - 事実: resources/views/reports/index.blade.php:20 <div class="text-4xl font-bold text-green-600 mb-2">{{ $stats['summary']['books_read'] }}</div>
  - 事実: resources/views/reports/index.blade.php:21 <div class="text-sm text-gray-600">読了冊数</div>
  - 事実: app/Http/Controllers/ReportController.php:49 'books_read' => $reviews->pluck('book_id')->unique()->count(),
- E7: 黄色の数値「2.9」＋ラベル「平均評価」
  - 事実: resources/views/reports/index.blade.php:24 <div class="text-4xl font-bold text-yellow-500 mb-2">
  - 事実: resources/views/reports/index.blade.php:26 {{ number_format($stats['summary']['average_rating'], 1) }}
  - 事実: resources/views/reports/index.blade.php:31 <div class="text-sm text-gray-600">平均評価</div>
  - 事実: app/Http/Controllers/ReportController.php:50 'average_rating' => $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 0,
- E8: セクション見出し「評価分布」
  - 事実: resources/views/reports/index.blade.php:41 <h3 class="text-lg font-semibold text-gray-900 mb-4">評価分布</h3>
- E9: 星の行「★」「★★」「★★★」「★★★★」「★★★★★」（上から★1→★5の順）
  - 事実: resources/views/reports/index.blade.php:45 $rating = $index + 1;
  - 事実: resources/views/reports/index.blade.php:51 <span class="text-yellow-500">{{ str_repeat('★', $rating) }}</span>
  - 事実: app/Http/Controllers/ReportController.php:62 return collect(range(1, 5))
- E10: 各行の黄色の横棒グラフ
  - 事実: resources/views/reports/index.blade.php:46 $maxCount = $stats['rating_distribution']->max() ?: 1;
  - 事実: resources/views/reports/index.blade.php:47 $percentage = ($count / $maxCount) * 100;
  - 事実: resources/views/reports/index.blade.php:55 <div class="bg-yellow-400 h-2 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
- E11: 各行の件数「1件」「2件」「1件」「3件」「0件」
  - 事実: resources/views/reports/index.blade.php:58 <div class="w-12 text-sm text-gray-600 text-right font-medium">{{ $count }}件</div>
  - 事実: app/Http/Controllers/ReportController.php:64 $star - 1 => $reviews->where('rating', $star)->count(),
- E12: セクション見出し「高評価書籍 TOP5」
  - 事実: resources/views/reports/index.blade.php:68 <h3 class="text-lg font-semibold text-gray-900 mb-4">高評価書籍 TOP5</h3>
- E13: 順位の丸バッジ「1」（金）「2」（銀）「3」（銅）
  - 事実: resources/views/reports/index.blade.php:74 0 => 'bg-yellow-400 text-white',
  - 事実: resources/views/reports/index.blade.php:75 1 => 'bg-gray-400 text-white',
  - 事実: resources/views/reports/index.blade.php:76 2 => 'bg-amber-600 text-white',
  - 事実: resources/views/reports/index.blade.php:82 {{ $index + 1 }}
- E14: 書籍タイトル「人を動かす」「火花」「コンテナ物語」
  - 事実: resources/views/reports/index.blade.php:85 <div class="font-medium text-gray-900 truncate">{{ $book['title'] }}</div>
  - 事実: app/Http/Controllers/ReportController.php:78 ->filter(fn (Review $review): bool => $review->rating >= 4)
  - 事実: app/Http/Controllers/ReportController.php:87 ->take(5)
- E15: 著者「D・カーネギー」「又吉直樹」「マルク・レビンソン」
  - 事実: resources/views/reports/index.blade.php:86 <div class="text-sm text-gray-500">{{ $book['author'] }}</div>
- E16: 右端の星「★★★★☆」
  - 事実: resources/views/reports/index.blade.php:89 {{ str_repeat('★', $book['rating']) }}{{ str_repeat('☆', 5 - $book['rating']) }}
- E17: セクション見出し「ジャンル別評価傾向 TOP5」
  - 事実: resources/views/reports/index.blade.php:104 <h3 class="text-lg font-semibold text-gray-900 mb-1">ジャンル別評価傾向 TOP5</h3>
- E18: 説明文「どのジャンルを高く評価する傾向があるかを表示」
  - 事実: resources/views/reports/index.blade.php:105 <p class="text-sm text-gray-500 mb-4">どのジャンルを高く評価する傾向があるかを表示</p>
- E19: 3列のカード、順位バッジ「1」（金）「2」（銀）「3」（銅）「4」「5」（灰）
  - 事実: resources/views/reports/index.blade.php:107 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  - 事実: resources/views/reports/index.blade.php:115 $rankColor = $rankColors[$index] ?? 'bg-gray-200 text-gray-600';
  - 事実: resources/views/reports/index.blade.php:119 {{ $index + 1 }}
- E20: ジャンル名「歴史」「ビジネス」「小説」「技術書」「自己啓発」
  - 事実: resources/views/reports/index.blade.php:122 <div class="font-medium text-gray-900">{{ $genre['name'] }}</div>
  - 事実: app/Http/Controllers/ReportController.php:113 ->sortByDesc('average_rating')
  - 事実: app/Http/Controllers/ReportController.php:114 ->take(5)
- E21: 件数「1件のレビュー」「3件のレビュー」「2件のレビュー」ほか
  - 事実: resources/views/reports/index.blade.php:123 <div class="text-sm text-gray-500">{{ $genre['count'] }}件のレビュー</div>
- E22: 右側の黄色の数値「4.0」「3.3」「3.0」「3.0」「2.3」
  - 事実: resources/views/reports/index.blade.php:126 <div class="text-lg font-bold text-yellow-500">{{ number_format($genre['average_rating'], 1) }}</div>
- E23: 数値下のラベル「平均評価」
  - 事実: resources/views/reports/index.blade.php:127 <div class="text-xs text-gray-400">平均評価</div>

### S06!IMG@D233（xl/media/image19.png）
- 対応Blade: resources/views/reading-plans/index.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」「マイレポート」「読書計画」・ベルアイコン）
  - 事実: 同上 S06!IMG@D182 E1〜E9
- E2: ナビの「読書計画」に下線（アクティブ表示）
  - 事実: resources/views/layouts/navigation.blade.php:39 <x-nav-link :href="route('reading-plans.index')" :active="request()->routeIs('reading-plans.*')">
- E3: 右上のユーザー名「佐藤美咲」＋下向き矢印
  - 事実: resources/views/layouts/navigation.blade.php:61 <div>{{ Auth::user()->name }}</div>
  - 事実: 実行結果.md R100 4	佐藤美咲	sato@example.com	NULL	2026-09-27 09:49:38	2026-09-27 09:49:38
- E4: ページ見出し「読書計画」
  - 事実: resources/views/reading-plans/index.blade.php:4 読書計画
  - 事実: app/Http/Controllers/ReadingPlanController.php:30 return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
- E5: ラベル「状態:」＋セレクト（表示値「すべて」）
  - 事実: resources/views/reading-plans/index.blade.php:12 <label for="status" class="text-sm text-gray-700">状態:</label>
  - 事実: resources/views/reading-plans/index.blade.php:13 <select name="status" id="status" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
  - 事実: resources/views/reading-plans/index.blade.php:14 <option value="">すべて</option>
  - 事実: resources/views/reading-plans/index.blade.php:17 {{ $statusOption->label() }}
  - 事実: app/Enums/ReadingPlanStatus.php:17 self::InProgress => '進行中',
  - 事実: app/Enums/ReadingPlanStatus.php:18 self::Completed => '完了',
  - 事実: app/Enums/ReadingPlanStatus.php:19 self::Expired => '期限切れ',
- E6: ボタン「新規計画作成」（青・右寄せ）
  - 事実: resources/views/reading-plans/index.blade.php:22 <a href="{{ route('reading-plans.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/reading-plans/index.blade.php:23 新規計画作成
- E7: 空メッセージ「該当する読書計画はありません。」
  - 事実: resources/views/reading-plans/index.blade.php:35 @if($readingPlans->isEmpty())
  - 事実: resources/views/reading-plans/index.blade.php:36 <p class="text-gray-500">該当する読書計画はありません。</p>
  - 事実: app/Http/Controllers/ReadingPlanController.php:24 $readingPlans = ReadingPlan::where('user_id', Auth::id())
  - 事実: 実行結果.md R100 reading_plans の user_id 列は 1 と 2 のみ（1	1	1	2026-09-30	in_progress … 6	2	6	2026-10-02	in_progress）

### S06!IMG@M233（xl/media/image20.png）
- 対応Blade: resources/views/reading-plans/create.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」「マイレポート」「読書計画」・ベルアイコン）
  - 事実: 同上 S06!IMG@D182 E1〜E9
- E2: ナビの「読書計画」に下線（アクティブ表示）
  - 事実: 同上 S06!IMG@D233 E2
- E3: 右上のユーザー名「佐藤美咲」＋下向き矢印
  - 事実: 同上 S06!IMG@D233 E3
- E4: ページ見出し「新規読書計画作成」
  - 事実: resources/views/reading-plans/create.blade.php:4 新規読書計画作成
  - 事実: app/Http/Controllers/ReadingPlanController.php:40 return view('reading-plans.create', compact('books'));
- E5: ラベル「書籍」＋赤い「*」
  - 事実: resources/views/reading-plans/create.blade.php:15 <label for="book_id" class="block text-sm font-medium text-gray-700">書籍 <span class="text-red-500">*</span></label>
- E6: セレクト（表示値「-- 書籍を選択 --」）
  - 事実: resources/views/reading-plans/create.blade.php:17 <option value="">-- 書籍を選択 --</option>
  - 事実: resources/views/reading-plans/create.blade.php:20 {{ $book->title }}（{{ $book->author }}）
  - 事実: app/Http/Controllers/ReadingPlanController.php:38 $books = Book::orderBy('title')->get();
- E7: ラベル「期日」＋赤い「*」
  - 事実: resources/views/reading-plans/create.blade.php:30 <label for="target_date" class="block text-sm font-medium text-gray-700">期日 <span class="text-red-500">*</span></label>
- E8: 日付入力欄（「年 /月/日」・カレンダーアイコン）
  - 事実: resources/views/reading-plans/create.blade.php:31 <input type="date" name="target_date" id="target_date" value="{{ old('target_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
- E9: リンク「キャンセル」
  - 事実: resources/views/reading-plans/create.blade.php:38 <a href="{{ route('reading-plans.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">キャンセル</a>
- E10: ボタン「登録」（青）
  - 事実: resources/views/reading-plans/create.blade.php:39 <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/reading-plans/create.blade.php:40 登録
  - 事実: app/Http/Controllers/ReadingPlanController.php:56 ->with('success', '読書計画を登録しました');

### S06!IMG@U233（xl/media/image21.png）
- 対応Blade: resources/views/reading-plans/edit.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」「マイレポート」「読書計画」・ベルアイコン・ユーザー名「山田太郎」＋矢印）
  - 事実: 同上 S06!IMG@D182 E1〜E10
- E2: ナビの「読書計画」に下線（アクティブ表示）
  - 事実: 同上 S06!IMG@D233 E2
- E3: ページ見出し「読書計画編集」
  - 事実: resources/views/reading-plans/edit.blade.php:4 読書計画編集
  - 事実: app/Http/Controllers/ReadingPlanController.php:66 return view('reading-plans.edit', ['readingPlan' => $plan]);
- E4: 「対象書籍: 人を動かす」（書籍名は太字）
  - 事実: resources/views/reading-plans/edit.blade.php:13 <p class="text-sm text-gray-700">対象書籍: <strong>{{ $readingPlan->book->title }}</strong></p>
  - 事実: 実行結果.md R100 reading_plans 2	1	2	2026-09-27	in_progress	NULL	2026-09-27 09:49:38	2026-09-27 09:49:38
- E5: 「現在の状態:」＋青いバッジ「進行中」
  - 事実: resources/views/reading-plans/edit.blade.php:14 <p class="text-sm text-gray-700">現在の状態:
  - 事実: resources/views/reading-plans/edit.blade.php:15 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $readingPlan->status->badgeClass() }}">
  - 事実: resources/views/reading-plans/edit.blade.php:16 {{ $readingPlan->status->label() }}
  - 事実: app/Enums/ReadingPlanStatus.php:17 self::InProgress => '進行中',
  - 事実: app/Enums/ReadingPlanStatus.php:29 self::InProgress => 'bg-blue-100 text-blue-800',
- E6: ラベル「期日」＋赤い「*」
  - 事実: resources/views/reading-plans/edit.blade.php:25 <label for="target_date" class="block text-sm font-medium text-gray-700">期日 <span class="text-red-500">*</span></label>
- E7: 日付入力欄（値「2026/05/06」・カレンダーアイコン）
  - 事実: resources/views/reading-plans/edit.blade.php:26 <input type="date" name="target_date" id="target_date" value="{{ old('target_date', $readingPlan->target_date->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:borde
- E8: リンク「キャンセル」
  - 事実: resources/views/reading-plans/edit.blade.php:33 <a href="{{ route('reading-plans.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">キャンセル</a>
- E9: ボタン「更新」（青）
  - 事実: resources/views/reading-plans/edit.blade.php:34 <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  - 事実: resources/views/reading-plans/edit.blade.php:35 更新
  - 事実: app/Http/Controllers/ReadingPlanController.php:79 ->with('success', '読書計画を更新しました');

### S06!IMG@D257（xl/media/image22.png）
- 対応Blade: resources/views/notifications/index.blade.php（レイアウト resources/views/layouts/navigation.blade.php）
- E1: ナビゲーション（ロゴ・「書籍一覧」「ランキング」「書籍登録」「お気に入り」「ジャンル管理」「マイレポート」「読書計画」・ベルアイコン（未読バッジなし））
  - 事実: 同上 S06!IMG@D182 E1〜E9
- E2: 右上のユーザー名「高橋健太」＋下向き矢印
  - 事実: resources/views/layouts/navigation.blade.php:61 <div>{{ Auth::user()->name }}</div>
  - 事実: 実行結果.md R100 5	高橋健太	takahashi@example.com	NULL	2026-09-27 09:49:38	2026-09-27 09:49:38
- E3: ページ見出し「通知一覧」
  - 事実: resources/views/notifications/index.blade.php:4 通知一覧
  - 事実: app/Http/Controllers/NotificationController.php:19 return view('notifications.index', compact('notifications'));
- E4: 中央の灰色ベルアイコン
  - 事実: resources/views/notifications/index.blade.php:17 @if($notifications->isEmpty())
  - 事実: resources/views/notifications/index.blade.php:19 <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
- E5: 空メッセージ「通知はありません。」
  - 事実: resources/views/notifications/index.blade.php:22 <p class="mt-3 text-sm text-gray-500">通知はありません。</p>
  - 事実: app/Http/Controllers/NotificationController.php:17 $notifications = Auth::user()->notifications()->latest()->get();
  - 事実: 実行結果.md R15 notifications 0
