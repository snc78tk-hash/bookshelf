<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Services\GoogleBooksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $books = Book::query()
            ->with('genres')
            ->keyword($request->keyword)
            ->genre($request->genre)
            ->sort($request->sort)
            ->paginate(10)
            ->withQueryString();

        $genres = Genre::orderBy('name')->get();

        return view('books.index', compact('books', 'genres'));
    }

    public function create(): View
    {
        $genres = Genre::orderBy('name')->get();

        return view('books.create', compact('genres'));
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'published_at' => $request->published_at,
            'description' => $request->description,
            'image_url' => $request->image_url,
            'created_by' => Auth::id(),
        ]);

        $book->genres()->attach($request->genres);

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を登録しました。');
    }

    public function show(Book $book): View
    {
        $book->load('genres', 'creator', 'reviews.user', 'reviews.likedByUsers');

        $userReview = null;

        if (auth()->check()) {
            $userReview = $book->reviews()
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('books.show', compact('book', 'userReview'));
    }

    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::orderBy('name')->get();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $book->update([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'published_at' => $request->published_at,
            'description' => $request->description,
            'image_url' => $request->image_url,
        ]);

        $book->genres()->sync($request->genres);

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました。');
    }

    public function searchIsbn(string $isbn, GoogleBooksService $googleBooksService): JsonResponse
    {
        $book = $googleBooksService->searchByIsbn($isbn);

        if (! $book) {
            return response()->json(['message' => '書籍が見つかりませんでした。'], 404);
        }

        return response()->json($book);
    }
}
