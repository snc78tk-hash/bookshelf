<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApiStoreBookRequest;
use App\Http\Requests\Api\ApiUpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BookController extends Controller
{
    /**
     * 一覧取得
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $books = Book::query()
            ->keyword($request->keyword)
            ->genre($request->filled('genre')
                    ? $request->integer('genre')
                    : null
            )
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->paginate($request->per_page ?? 10);

        return BookResource::collection($books);
    }

    /**
     * 書籍詳細取得
     */
    public function show(Book $book): BookResource
    {
        $book->load('genres', 'creator', 'reviews.user', 'reviews.likedByUsers')
            ->loadCount('reviews')
            ->loadAvg('reviews', 'rating');

        return new BookResource($book);
    }

    /**
     * 書籍登録
     */
    public function store(ApiStoreBookRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $book = Book::create([
            'title' => $validatedData['title'],
            'author' => $validatedData['author'],
            'isbn' => $validatedData['isbn'] ?? null,
            'published_at' => $validatedData['published_at'] ?? null,
            'description' => $validatedData['description'] ?? null,
            'image_url' => $validatedData['image_url'] ?? null,
            'created_by' => auth()->id(),
        ]);

        if (isset($validatedData['genres'])) {
            $book->genres()->attach($validatedData['genres']);
        }

        $book->load('genres');

        return response()->json([
            'message' => '書籍を登録しました。',
            'data' => new BookResource($book),
        ], 201);
    }

    /**
     * 書籍情報更新
     */
    public function update(ApiUpdateBookRequest $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $validatedData = $request->validated();

        $book->update([
            'title' => $validatedData['title'],
            'author' => $validatedData['author'],
            'isbn' => $validatedData['isbn'] ?? null,
            'published_at' => $validatedData['published_at'] ?? null,
            'description' => $validatedData['description'] ?? null,
            'image_url' => $validatedData['image_url'] ?? null,
        ]);

        if (isset($validatedData['genres'])) {
            $book->genres()->sync($validatedData['genres']);
        }
        $book->load('genres');

        return response()->json([
            'message' => '書籍情報を更新しました。',
            'data' => new BookResource($book),
        ], 200);
    }

    /**
     * 書籍情報削除
     */
    public function destroy(Book $book): Response
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->noContent();
    }
}
