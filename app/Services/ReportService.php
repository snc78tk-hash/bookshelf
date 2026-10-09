<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class ReportService
{
    public function getUserStats(User $user): array
    {
        $reviews = $this->getReviews($user);

        return [
            'summary' => $this->summary($reviews),
            'rating_distribution' => $this->ratingDistribution($reviews),
            'top_rated_books' => $this->topRatedBooks($reviews),
            'genre_ratings' => $this->genreRatings($reviews),
        ];
    }

    // ユーザーのレビューを取得
    private function getReviews(User $user): Collection
    {
        return $user->reviews()
            ->with([
                'book.genres',
            ])
            ->latest()
            ->get();
    }

    // ユーザーのレビューに関する統計情報をまとめる
    private function summary(Collection $reviews): array
    {
        return [
            'total_reviews' => $reviews->count(),
            'books_read' => $reviews->pluck('book_id')->unique()->count(),
            'average_rating' => round($reviews->avg('rating'), 2),
        ];

    }

    // レビューの評価分布を計算
    private function ratingDistribution(Collection $reviews): Collection
    {
        return collect(range(1, 5))
            ->map(function ($rating) use ($reviews) {
                return $reviews
                    ->where('rating', $rating)
                    ->count();
            });
    }

    // レビューが4以上の書籍を取得
    private function topRatedBooks(Collection $reviews): Collection
    {
        return $reviews
            ->filter(function ($review) {
                return $review->rating >= 4;
            })
            ->sortByDesc('rating')
            ->take(5)
            ->map(function ($review) {
                $book = $review->book;

                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'rating' => $review->rating,
                ];
            });
    }

    // ジャンルごとの平均評価を計算
    private function genreRatings(Collection $reviews): Collection
    {
        // レビューを「ジャンル × 評価」のCollectionへ展開
        return $reviews
            ->flatMap(function ($review) {
                return $review->book->genres->map(function ($genre) use ($review) {
                    return [
                        'genre' => $genre,
                        'rating' => $review->rating,
                    ];
                });
            })
            ->groupBy(function ($item) {
                return $item['genre']->id;
            })
            ->map(function ($items) {
                $genre = $items->first()['genre'];

                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'average_rating' => round($items->avg('rating'), 2),
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();
    }
}
