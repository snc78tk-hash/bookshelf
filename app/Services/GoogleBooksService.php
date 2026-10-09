<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleBooksService
{
    public function searchByIsbn(string $isbn): ?array
    {
        $response = Http::get(
            'https://www.googleapis.com/books/v1/volumes',
            [
                'q' => 'isbn:'.$isbn,
                'key' => config('services.google_books.key'),
            ]
        );

        if (! $response->successful()) {
            logger()->error($response->json());

            return null;
        }

        $data = $response->json();

        if (empty($data['items'])) {
            return null;
        }

        $volume = $data['items'][0]['volumeInfo'];

        $imageUrl = $volume['imageLinks']['thumbnail'] ?? '';
        $imageUrl = str_replace('http://', 'https://', $imageUrl);

        return [
            'title' => $volume['title'] ?? '',
            'author' => implode(', ', $volume['authors'] ?? []),
            'published_at' => $this->formatPublishedDate(
                $volume['publishedDate'] ?? ''
            ),
            'description' => $volume['description'] ?? '',
            'image_url' => $imageUrl,
        ];
    }

    private function formatPublishedDate(string $publishedDate): string
    {
        if ($publishedDate === '') {
            return '';
        }

        if (preg_match('/^\d{4}$/', $publishedDate)) {
            return $publishedDate.'-01-01';
        }

        if (preg_match('/^\d{4}-\d{2}$/', $publishedDate)) {
            return $publishedDate.'-01';
        }

        return $publishedDate;
    }
}
