<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * Determine whether the user can update the book.
     */
    public function update(User $user, Book $book): bool
    {
        return $user->id === $book->user_id && ! $book->trashed();
    }

    /**
     * Determine whether the user can delete the book.
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->id === $book->user_id && ! $book->trashed();
    }

    /**
     * Determine whether the user can restore the book.
     */
    public function restore(User $user, Book $book): bool
    {
        return $user->id === $book->user_id && $book->trashed();
    }
}
