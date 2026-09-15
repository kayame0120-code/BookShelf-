<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * お気に入り一覧を表示する。
     */
    public function index(): View
    {
        $books = Auth::user()->favoriteBooks()->latest('books.created_at')->paginate(10);

        return view('favorites.index', compact('books'));
    }

    /**
     * お気に入りをトグルする。
     */
    public function toggle(Book $book): RedirectResponse
    {
        Auth::user()->favoriteBooks()->toggle($book);

        return back();
    }
}
