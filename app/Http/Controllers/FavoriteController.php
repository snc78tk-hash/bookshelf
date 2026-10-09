<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function toggle(Book $book): RedirectResponse
    {
        $user = auth()->user();

        // Laravel の toggle() を使うと簡潔
        $user->favoriteBooks()->toggle($book->id);

        return back();
    }

    public function index(): View
    {
        $books = auth()->user()
            ->favoriteBooks()
            ->with(['genres'])
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }
}
