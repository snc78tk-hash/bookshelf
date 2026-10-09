<?php

namespace Database\Factories;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingPlanFactory extends Factory
{
    protected $model = ReadingPlan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'target_date' => fake()->dateTimeBetween('today', '+30 days'),
            'status' => ReadingPlanStatus::Planned,
            'completed_at' => null,
        ];
    }

    /**
     * 読書中
     */
    public function reading(): static
    {
        return $this->state(fn () => [
            'status' => ReadingPlanStatus::Planned,
            'completed_at' => null,
        ]);
    }

    /**
     * 読了済み
     */
    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    /**
     * 期限切れ
     */
    public function overdue(): static
    {
        return $this->state(fn () => [
            'target_date' => now()->subDays(3),
            'status' => ReadingPlanStatus::Planned,
        ]);
    }

    /**
     * 今日が期限
     */
    public function dueToday(): static
    {
        return $this->state(fn () => [
            'target_date' => today(),
            'status' => ReadingPlanStatus::Planned,
        ]);
    }
}
