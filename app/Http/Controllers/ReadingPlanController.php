<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\ReadingPlanStoreRequest;
use App\Http\Requests\ReadingPlanUpdateRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    /**
     * 自分の読書計画一覧を状態で絞り込んで新しい順に表示する。
     */
    public function index(Request $request): View
    {
        $currentStatus = $request->query('status');

        $readingPlans = ReadingPlan::where('user_id', Auth::id())
            ->with('book')
            ->when($currentStatus, fn ($query) => $query->where('status', $currentStatus))
            ->latest()
            ->get();

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    /**
     * 読書計画の新規作成フォームを表示する。
     */
    public function create(): View
    {
        $books = Book::select('id', 'title', 'author')->orderBy('id')->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 読書計画を登録する。
     */
    public function store(ReadingPlanStoreRequest $request): RedirectResponse
    {
        ReadingPlan::create([
            'user_id' => Auth::id(),
            'book_id' => $request->validated('book_id'),
            'target_date' => $request->validated('target_date'),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を登録しました');
    }

    /**
     * 読書計画の編集フォームを表示する。
     */
    public function edit(ReadingPlan $plan): View
    {
        $this->authorize('update', $plan);

        return view('reading-plans.edit', ['readingPlan' => $plan]);
    }

    /**
     * 読書計画の期日を更新する（target_dateのみ）。
     */
    public function update(ReadingPlanUpdateRequest $request, ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $plan->update(['target_date' => $request->validated('target_date')]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を更新しました');
    }

    /**
     * 読書計画を完了状態にする。
     */
    public function complete(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('complete', $plan);

        $plan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を完了しました');
    }

    /**
     * 読書計画を削除する。
     */
    public function destroy(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を削除しました');
    }
}
