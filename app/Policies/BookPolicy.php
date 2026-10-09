<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * 作成者のみが更新可能
     */
    public function update(User $user, Book $book): bool
    {
        return $user->id === $book->created_by;
    }

    /**
     * 作成者のみが削除可能
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->id === $book->created_by;
    }
}
