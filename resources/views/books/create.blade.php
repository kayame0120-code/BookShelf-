<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('書籍の登録') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 mb-6">
                        <h3 class="font-medium text-gray-800 mb-1">📖 ISBN から書籍情報を自動入力</h3>
                        <p class="text-sm text-gray-600 mb-3">13桁の ISBN を入力すると、Google Books API から書籍情報を取得してフォームを自動補完します。</p>
                        <div class="flex gap-2">
                            <input type="text" id="isbn_search" value=""
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full"
                                placeholder="例: 9784101010014">
                            <button type="button" id="isbn_search_button"
                                class="shrink-0 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                                🔍 検索
                            </button>
                        </div>
                        <p id="isbn_search_message" class="text-sm mt-2 hidden"></p>
                    </div>

                    <form action="{{ route('books.store') }}" method="POST" novalidate>
                        @include('books._form')

                        <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
                            <a href="{{ route('books.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                キャンセル
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                                登録
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const button = document.getElementById('isbn_search_button');
                const input = document.getElementById('isbn_search');
                const message = document.getElementById('isbn_search_message');

                const showMessage = function (text, isError) {
                    message.textContent = text;
                    message.classList.remove('hidden', 'text-red-600', 'text-green-600');
                    message.classList.add(isError ? 'text-red-600' : 'text-green-600');
                };

                const setValue = function (id, value) {
                    const field = document.getElementById(id);
                    if (field && value) {
                        field.value = value;
                    }
                };

                button.addEventListener('click', async function () {
                    const isbn = input.value.trim();
                    message.classList.add('hidden');

                    try {
                        const response = await fetch(`/books/isbn/${encodeURIComponent(isbn)}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await response.json();

                        if (!response.ok) {
                            showMessage(data.message ?? '書籍情報の取得に失敗しました', true);
                            return;
                        }

                        setValue('title', data.title);
                        setValue('author', data.author);
                        setValue('description', data.description);
                        setValue('image_url', data.image_url);
                        setValue('published_date', data.published_date);
                        document.getElementById('isbn').value = isbn;

                        if (data.published_date_padded) {
                            showMessage('出版日は年月までの情報のため、日付は仮の値（1日）を自動設定しました。正しい日付が分かる場合は修正してください。', false);
                        } else {
                            showMessage('書籍情報を自動入力しました', false);
                        }
                    } catch (e) {
                        showMessage('書籍情報の取得に失敗しました', true);
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
