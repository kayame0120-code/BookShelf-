<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /** U-G1: Genre belongsToMany Book (book_genre) */
    public function test_genre_belongs_to_many_books(): void
    {
        $genre = Genre::factory()->create();
        $books = Book::factory()->count(2)->create();
        $genre->books()->sync($books->pluck('id')->all());

        $this->assertCount(2, $genre->fresh()->books);
        $this->assertInstanceOf(Book::class, $genre->books->first());
    }
}
