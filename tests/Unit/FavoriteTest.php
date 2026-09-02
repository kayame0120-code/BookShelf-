<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** U-F1: favoriteBooks belongsToMany toggle works */
    public function test_favorite_books_toggle(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->toggle($book->id);
        $this->assertTrue($user->fresh()->favoriteBooks->contains($book->id));

        $user->favoriteBooks()->toggle($book->id);
        $this->assertFalse($user->fresh()->favoriteBooks->contains($book->id));
    }
}
