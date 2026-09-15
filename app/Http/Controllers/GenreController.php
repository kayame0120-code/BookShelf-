<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示する。
     */
    public function index(): View
    {
        $genres = Genre::withCount('books')->orderBy('id')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル登録フォームを表示する。
     */
    public function create(): View
    {
        return view('genres.create');
    }

    /**
     * ジャンルを登録する。
     */
    public function store(StoreGenreRequest $request): RedirectResponse
    {
        Genre::create($request->validated());

        return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました');
    }

    /**
     * ジャンル詳細（所属書籍一覧）を表示する。
     */
    public function show(Genre $genre): View
    {
        $books = $genre->books()->with('genres')->latest('books.created_at')->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンル編集フォームを表示する。
     */
    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンルを更新する。
     */
    public function update(UpdateGenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update($request->validated());

        return redirect()->route('genres.index')->with('success', 'ジャンルを更新しました');
    }

    /**
     * ジャンルを削除する（紐づく書籍があれば削除不可）。
     */
    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->withTrashed()->exists()) {
            return redirect()->route('genres.index')
                ->with('error', 'このジャンルに紐づく書籍が存在するため削除できません');
        }

        $genre->delete();

        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました');
    }
}
