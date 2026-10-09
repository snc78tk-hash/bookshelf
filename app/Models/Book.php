<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_at',
        'description',
        'image_url',
        'created_by',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    // 作成者
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ジャンル（多対多）
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    // レビュー（1対多）
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // お気に入り（多対多）
    public function favoritedUsers()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    // 読書計画（1対多）
    public function readingPlans()
    {
        return $this->hasMany(ReadingPlan::class);
    }

    // キーワード検索
    public function scopeKeyword(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('author', 'like', "%{$keyword}%");
        });
    }

    // ジャンル絞り込み
    public function scopeGenre(Builder $query, ?int $genreId): Builder
    {
        if (blank($genreId)) {
            return $query;
        }

        return $query->whereHas('genres', function ($q) use ($genreId) {
            $q->whereKey($genreId); // より Laravel らしい
        });
    }

    // 並び替え
    public function scopeSort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'newest' => $query->orderByDesc('id'),
            'oldest' => $query->orderBy('id'),
            'title' => $query->orderBy('title'),

            'rating' => $query
                ->withAvg('reviews as avg_rating', 'rating')
                ->orderByDesc('avg_rating'),

            default => $query->orderByDesc('id'),
        };
    }
}
