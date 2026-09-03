<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * レビューを投稿する。
     */
    public function store(StoreReviewRequest $request, Book $book)
    {
        $book->reviews()->create($request->validated() + ['user_id' => Auth::id()]);

        return redirect()->route('books.show', $book)->with('success', 'レビューを投稿しました');
    }

    /**
     * レビュー編集フォームを表示する。
     */
    public function edit(Review $review)
    {
        $this->authorize('update', $review);

        $review->load('book');

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューを更新する。
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book)->with('success', 'レビューを更新しました');
    }

    /**
     * レビューを削除する（review_likesはcascadeで連動削除）。
     */
    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $book = $review->book;
        $review->delete();

        return redirect()->route('books.show', $book)->with('success', 'レビューを削除しました');
    }

    /**
     * レビューへのいいねをトグルする。
     */
    public function like(Review $review)
    {
        Auth::user()->likedReviews()->toggle($review);

        return back();
    }
}
