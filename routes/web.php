<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// 書籍（公開）
Route::get('/', [BookController::class, 'index']);
Route::get('/books', [BookController::class, 'index'])->name('books.index');

// 書籍（認証必須。/books/{book} より前に定義）
Route::middleware('auth')->group(function () {
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::get('/books/isbn/{isbn}', [BookController::class, 'searchByIsbn'])->name('books.searchByIsbn');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
});

// 書籍詳細（公開・削除済みも解決）
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show')->withTrashed();

// 書籍（認証必須）
Route::middleware('auth')->group(function () {
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    Route::patch('/books/{book}/restore', [BookController::class, 'restore'])
        ->name('books.restore')->withTrashed();
});

// レビュー・いいね・お気に入り（認証必須）
Route::middleware('auth')->group(function () {
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])->name('reviews.like');

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});

// ジャンル（認証必須）
Route::middleware('auth')->group(function () {
    Route::resource('genres', GenreController::class);
});

// マイ読書レポート（認証必須）
Route::middleware('auth')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

// 読書計画（認証必須）
Route::middleware('auth')->group(function () {
    Route::get('/reading-plans', [ReadingPlanController::class, 'index'])->name('reading-plans.index');
    Route::get('/reading-plans/create', [ReadingPlanController::class, 'create'])->name('reading-plans.create');
    Route::post('/reading-plans', [ReadingPlanController::class, 'store'])->name('reading-plans.store');
    Route::get('/reading-plans/{plan}/edit', [ReadingPlanController::class, 'edit'])->name('reading-plans.edit');
    Route::put('/reading-plans/{plan}', [ReadingPlanController::class, 'update'])->name('reading-plans.update');
    Route::post('/reading-plans/{plan}/complete', [ReadingPlanController::class, 'complete'])->name('reading-plans.complete');
    Route::delete('/reading-plans/{plan}', [ReadingPlanController::class, 'destroy'])->name('reading-plans.destroy');
});

// ランキング（公開）
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
