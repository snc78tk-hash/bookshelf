<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_at' => $this->published_at?->toDateString(),
            'description' => $this->description,
            'image_url' => $this->image_url,
            'genres' => $this->genres->pluck('name')->values(),
            'average_rating' => $this->reviews_avg_rating,
            'reviews_count' => $this->reviews_count,
            'created_by' => $this->creator ? $this->creator->name : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
