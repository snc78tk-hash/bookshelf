<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\View\View;

class RankingController extends Controller
{
    public function index(): View
    {
        $rankedBooks = Book::withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews')
            ->whereHas('reviews')
            ->orderBy('avg_rating', 'desc')
            ->orderBy('reviews_count', 'desc')
            ->limit(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
