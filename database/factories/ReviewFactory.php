<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $comments = [
            1 => '期待外れでした。',
            2 => 'あまり面白くなかったです。',
            3 => '普通でしたが、参考になりました。',
            4 => '面白く、勉強になりました。',
            5 => '素晴らしい本で、非常に参考になりました！',
        ];

        $rating = $this->faker->numberBetween(1, 5);

        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'rating' => $rating,
            'comment' => $comments[$rating],
        ];
    }
}
