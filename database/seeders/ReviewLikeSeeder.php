<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::orderBy('id')->get();
        $reviews = Review::orderBy('id')->get();
        $userCount = $users->count();

        foreach ($reviews as $reviewIndex => $review) {
            // 各レビューに0〜3人がいいね（投稿者本人は除く）
            $likeCount = $reviewIndex % 4;
            $likers = [];

            for ($j = 0; count($likers) < $likeCount && $j < $userCount; $j++) {
                $user = $users[($reviewIndex + $j) % $userCount];
                if ($user->id !== $review->user_id && ! in_array($user->id, $likers)) {
                    $likers[] = $user->id;
                }
            }

            if (! empty($likers)) {
                $review->likedByUsers()->syncWithoutDetaching($likers);
            }
        }
    }
}
