<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>エラーが発生しました</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="text-center px-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">エラーが発生しました</h1>
        <p class="text-gray-600 mb-8">申し訳ありません。サーバーで問題が発生しました。時間をおいて再度お試しください。</p>
        <a href="{{ route('books.index') }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">トップページへ戻る</a>
    </div>
</body>
</html>
