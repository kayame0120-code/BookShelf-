<?php

namespace App\Http\Controllers;

use App\Models\Book;

class RankingController extends Controller
{
    /**
     * 評価ランキングTOP10を表示する。
     */
    public function index()
    {
        $rankedBooks = Book::withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->whereHas('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->orderBy('id')
            ->limit(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
