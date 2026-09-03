<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::orderBy('id')->get();
        $books = Book::orderBy('id')->get();

        // 各書籍に配分するレビュー件数（2〜4件、合計32件）
        $counts = [3, 3, 3, 3, 3, 3, 2, 3, 3, 3, 3];

        $comments = [
            '期待以上の内容で、読んで良かったです。',
            '分かりやすくまとまっていて参考になりました。',
            '何度も読み返したくなる一冊です。',
            '新しい視点を得ることができました。',
            'テーマの掘り下げが丁寧で好印象でした。',
            '実生活にすぐ役立つ内容でした。',
            '読み応えがあり、満足しています。',
            '冒頭から引き込まれました。',
            '構成が良く、最後まで飽きずに読めました。',
            '万人におすすめできる良書だと思います。',
        ];

        $commentIndex = 0;

        foreach ($books as $bookIndex => $book) {
            for ($j = 0; $j < $counts[$bookIndex]; $j++) {
                $user = $users[($bookIndex + $j) % $users->count()];
                $rating = 3 + (($bookIndex + $j) % 3);

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $comments[$commentIndex % count($comments)],
                ]);

                $commentIndex++;
            }
        }
    }
}
