<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Review;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * マイ読書レポートを表示する。
     *
     * ログインユーザー自身のレビューから4種の統計（基本サマリー・評価分布・
     * 高評価書籍TOP5・ジャンル別評価傾向TOP5）を集計し、$stats 配列として渡す。
     * 集計対象には論理削除済み書籍への自分のレビューも含める（Review::book() に
     * withTrashed() が適用済みのため book.genres の Eager Load で欠落しない）。
     */
    public function index(): View
    {
        $reviews = Review::where('user_id', Auth::id())
            ->with('book.genres')
            ->get();

        $stats = [
            'summary' => $this->buildSummary($reviews),
            'rating_distribution' => $this->buildRatingDistribution($reviews),
            'top_rated_books' => $this->buildTopRatedBooks($reviews),
            'genre_ratings' => $this->buildGenreRatings($reviews),
        ];

        return view('reports.index', compact('stats'));
    }

    /**
     * 基本サマリー（総レビュー数・読了冊数・平均評価）を組み立てる。
     *
     * @param  Collection<int, Review>  $reviews
     * @return array{total_reviews: int, books_read: int, average_rating: float|int}
     */
    private function buildSummary(Collection $reviews): array
    {
        $totalReviews = $reviews->count();

        return [
            'total_reviews' => $totalReviews,
            'books_read' => $reviews->pluck('book_id')->unique()->count(),
            'average_rating' => $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 0,
        ];
    }

    /**
     * 評価分布を組み立てる。要素数5・添字0が★1〜添字4が★5に対応する Collection。
     *
     * @param  Collection<int, Review>  $reviews
     * @return Collection<int, int>
     */
    private function buildRatingDistribution(Collection $reviews): Collection
    {
        return collect(range(1, 5))
            ->mapWithKeys(fn (int $star): array => [
                $star - 1 => $reviews->where('rating', $star)->count(),
            ]);
    }

    /**
     * 高評価書籍TOP5を組み立てる。自分が4以上を付けた書籍を、書籍単位で
     * 代表評価（自分がその書籍に付けた最高評価）の高い順に最大5件返す。
     *
     * @param  Collection<int, Review>  $reviews
     * @return Collection<int, array{id: int, title: string, author: string, rating: int}>
     */
    private function buildTopRatedBooks(Collection $reviews): Collection
    {
        return $reviews
            ->filter(fn (Review $review): bool => $review->rating >= 4)
            ->groupBy('book_id')
            ->map(fn (Collection $group): array => [
                'id' => $group->first()->book->id,
                'title' => $group->first()->book->title,
                'author' => $group->first()->book->author,
                'rating' => $group->max('rating'),
            ])
            ->sortByDesc('rating')
            ->take(5)
            ->values();
    }

    /**
     * ジャンル別評価傾向TOP5を組み立てる。1冊が複数ジャンルに属する場合、その
     * レビューは該当する全ジャンルの集計に計上する。平均の高い順に最大5件返す。
     *
     * @param  Collection<int, Review>  $reviews
     * @return Collection<int, array{id: int, name: string, count: int, average_rating: float}>
     */
    private function buildGenreRatings(Collection $reviews): Collection
    {
        return $reviews
            ->flatMap(fn (Review $review) => $review->book->genres->map(fn (Genre $genre): array => [
                'id' => $genre->id,
                'name' => $genre->name,
                'rating' => $review->rating,
            ]))
            ->groupBy('id')
            ->map(fn (Collection $group): array => [
                'id' => $group->first()['id'],
                'name' => $group->first()['name'],
                'count' => $group->count(),
                'average_rating' => $group->avg('rating'),
            ])
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();
    }
}
