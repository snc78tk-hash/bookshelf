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
        $user = Auth::user();

        if ($user->favoriteBooks()->where('book_id', $book->id)->exists()
        ) {
            $user->favoriteBooks()->detach($book->id);
        } else {
            $user->favoriteBooks()->attach($book->id);
        }

        return redirect()->back();
    }

    public function index(): View
    {
        $books = Auth::user()->favoriteBooks()->with('genres')->withAvg('reviews', 'rating')->latest()->paginate(10);

        return view('favorites.index', compact('books'));
    }
}
