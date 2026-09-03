<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Requests\Api\V1\StoreApiBookRequest;
use App\Http\Requests\Api\V1\UpdateApiBookRequest;
use App\Http\Resources\BookListResource;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * 書籍一覧を返す。
     */
    public function index(IndexBookRequest $request)
    {
        $perPage = $request->input('per_page', 10);

        $books = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%")
                        ->orWhere('author', 'like', "%{$keyword}%");
                });
            })
            ->when($request->genre_id, function ($query, $genreId) {
                $query->whereHas('genres', fn ($q) => $q->where('genres.id', $genreId));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return BookListResource::collection($books);
    }

    /**
     * 書籍詳細を返す。
     */
    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookResource($book);
    }

    /**
     * 書籍を登録する。
     */
    public function store(StoreApiBookRequest $request)
    {
        $book = DB::transaction(function () use ($request) {
            $book = Book::create($request->validated());
            $book->genres()->sync($request->genres);

            return $book;
        });

        $book->load('genres')->loadAvg('reviews', 'rating')->loadCount('reviews');

        return (new BookResource($book))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * 書籍を更新する。
     */
    public function update(UpdateApiBookRequest $request, Book $book)
    {
        DB::transaction(function () use ($request, $book) {
            $book->update($request->validated());
            $book->genres()->sync($request->genres);
        });

        $book->load('genres')->loadAvg('reviews', 'rating')->loadCount('reviews');

        return new BookResource($book);
    }

    /**
     * 書籍を論理削除する。
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return response()->noContent();
    }
}
