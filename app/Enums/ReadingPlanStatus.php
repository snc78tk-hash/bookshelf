<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case Planned = 'planned';
    case Completed = 'completed';

    /**
     * 画面表示用ラベル
     */
    public function label(): string
    {
        return match ($this) {
            self::Planned => '読書中',
            self::Completed => '読了',
        };
    }

    /**
     * バッジの色
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Planned => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
        };
    }

    /**
     * 読了済みかどうか
     */
    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }
}
