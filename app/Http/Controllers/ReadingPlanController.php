<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    public function index(Request $request): View
    {
        // 読書計画の一覧を取得してビューに渡す
        $query = Auth::user()->readingPlans()->with('book');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $readingPlans = $query->latest()->get();

        return view('reading-plans.index', [
            'readingPlans' => $readingPlans,
            'currentStatus' => $request->status,
        ]);
    }

    public function create(): View
    {
        // 読書計画の作成フォームを表示
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        $data = $request->validated();

        ReadingPlan::create([
            'user_id' => Auth::id(),
            'book_id' => $data['book_id'],
            'target_date' => $data['target_date'],
            'status' => ReadingPlanStatus::Planned,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を作成しました。');
    }

    public function edit(ReadingPlan $readingPlan): View
    {
        // 読書計画の編集フォームを表示
        $this->authorize('update', $readingPlan);

        $books = Book::orderBy('title')->get();

        return view('reading-plans.edit', compact('readingPlan', 'books'));
    }

    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan): RedirectResponse
    {
        // 読書計画を更新
        $this->authorize('update', $readingPlan);

        $data = $request->validated();

        $readingPlan->update([
            'target_date' => $data['target_date'],
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        // 読書計画を削除
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }

    public function complete(ReadingPlan $readingPlan): RedirectResponse
    {
        // 読書計画を読了済みに更新
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を読了済みに更新しました。');
    }
}
