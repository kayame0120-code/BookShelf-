<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * 書籍一覧を表示する（キーワード検索・ジャンル絞り込み・並び替え対応）。
     */
    public function index(Request $request): View
    {
        $query = Book::query()->with('genres');

        $keyword = trim((string) $request->query('keyword', ''));
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        $genre = (int) $request->query('genre', 0);
        if ($genre > 0) {
            $query->whereHas('genres', function ($q) use ($genre) {
                $q->where('genres.id', $genre);
            });
        }

        $sort = (string) $request->query('sort', 'newest');
        match ($sort) {
            'oldest' => $query->orderBy('created_at'),
            'title' => $query->orderBy('title'),
            'rating' => $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating'),
            default => $query->orderByDesc('created_at'),
        };

        $books = $query->paginate(10)->withQueryString();

        return view('books.index', [
            'books' => $books,
            'genres' => Genre::all(),
        ]);
    }

    /**
     * 書籍登録フォームを表示する。
     */
    public function create()
    {
        $genres = Genre::orderBy('id')->get();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を登録する。
     */
    public function store(StoreBookRequest $request)
    {
        $book = DB::transaction(function () use ($request) {
            $book = Book::create($request->validated() + ['user_id' => Auth::id()]);
            $book->genres()->sync($request->genres);

            return $book;
        });

        return redirect()->route('books.show', $book)->with('success', '書籍を登録しました');
    }

    /**
     * 書籍詳細を表示する（削除済みも表示）。
     */
    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews' => fn ($q) => $q->latest(),
            'reviews.user',
            'reviews.likedByUsers',
        ]);

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集フォームを表示する。
     */
    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $genres = Genre::orderBy('id')->get();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍を更新する。
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        DB::transaction(function () use ($request, $book) {
            $book->update($request->validated());
            $book->genres()->sync($request->genres);
        });

        return redirect()->route('books.show', $book)->with('success', '書籍を更新しました');
    }

    /**
     * 書籍を論理削除する。
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍を削除しました');
    }

    /**
     * 書籍を復元する。
     */
    public function restore(Book $book)
    {
        $this->authorize('restore', $book);

        $book->restore();

        return redirect()->route('books.show', $book)->with('success', '書籍を復元しました');
    }
}
