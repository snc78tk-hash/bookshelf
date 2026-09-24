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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function genres()
    {
        return $this->belongsToMany(
            Genre::class,
            'book_genre'
        );
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favoritedUsers()
    {
        return $this->belongsToMany(
            User::class,
            'favorites'
        );
    }

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

    public function scopeGenre(Builder $query, ?int $genreId): Builder
    {
        if (blank($genreId)) {
            return $query;
        }

        return $query->whereHas('genres', function ($q) use ($genreId) {
            $q->where('genres.id', $genreId);
        });
    }

    public function scopeSort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {

            'newest' => $query->orderByDesc('id'),

            'oldest' => $query->orderBy('id'),

            'title' => $query->orderBy('title'),

            'rating' => $query
                ->withAvg('reviews', 'rating')
                ->orderByDesc('reviews_avg_rating'),

            default => $query->orderByDesc('id'),

        };
    }

    public function readingPlans()
    {
        return $this->hasMany(ReadingPlan::class);
    }
}
