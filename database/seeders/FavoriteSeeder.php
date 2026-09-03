<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::orderBy('id')->get();
        $bookIds = Book::orderBy('id')->pluck('id')->all();
        $total = count($bookIds);

        // 各ユーザーに3〜5冊のお気に入りを決定的に割り当てる
        $sizes = [4, 3, 5, 3, 4];

        foreach ($users as $index => $user) {
            $size = $sizes[$index % count($sizes)];
            $favorites = [];
            for ($j = 0; $j < $size; $j++) {
                $favorites[] = $bookIds[($index * 2 + $j) % $total];
            }

            $user->favoriteBooks()->syncWithoutDetaching($favorites);
        }
    }
}
