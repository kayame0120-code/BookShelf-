<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示する。
     */
    public function index()
    {
        $genres = Genre::withCount('books')->orderBy('id')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル登録フォームを表示する。
     */
    public function create()
    {
        return view('genres.create');
    }

    /**
     * ジャンルを登録する。
     */
    public function store(StoreGenreRequest $request)
    {
        Genre::create($request->validated());

        return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました');
    }

    /**
     * ジャンル詳細（所属書籍一覧）を表示する。
     */
    public function show(Genre $genre)
    {
        $books = $genre->books()->with('genres')->latest('books.created_at')->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンル編集フォームを表示する。
     */
    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンルを更新する。
     */
    public function update(UpdateGenreRequest $request, Genre $genre)
    {
        $genre->update($request->validated());

        return redirect()->route('genres.index')->with('success', 'ジャンルを更新しました');
    }

    /**
     * ジャンルを削除する（紐づく書籍があれば削除不可）。
     */
    public function destroy(Genre $genre)
    {
        if ($genre->books()->withTrashed()->exists()) {
            return redirect()->route('genres.index')
                ->with('error', 'このジャンルに紐づく書籍が存在するため削除できません');
        }

        $genre->delete();

        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました');
    }
}
