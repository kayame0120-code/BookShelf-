<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = User::first()->id;

        $books = [
            ['title' => '吾輩は猫である', 'author' => '夏目漱石', 'isbn' => '9784101010014', 'published_date' => '1905-01-01', 'genres' => ['小説'], 'description' => '中学校の英語教師である珍野苦沙弥の家に飼われる猫の視点から、人間社会を風刺的に描いた長編小説。'],
            ['title' => '人を動かす', 'author' => 'D・カーネギー', 'isbn' => '9784422100524', 'published_date' => '1936-10-01', 'genres' => ['ビジネス', '自己啓発'], 'description' => '人間関係の原則を説いた自己啓発の古典。相手の立場に立って考えることの大切さを教えてくれる。'],
            ['title' => 'リーダブルコード', 'author' => 'Dustin Boswell', 'isbn' => '9784873115658', 'published_date' => '2012-06-23', 'genres' => ['技術書'], 'description' => 'より良いコードを書くためのシンプルで実践的なテクニックを解説する一冊。'],
            ['title' => '7つの習慣', 'author' => 'スティーブン・R・コヴィー', 'isbn' => '9784863940246', 'published_date' => '2013-08-30', 'genres' => ['ビジネス', '自己啓発'], 'description' => '人格主義に基づく成功のための7つの習慣を体系的にまとめた世界的ベストセラー。'],
            ['title' => '坊っちゃん', 'author' => '夏目漱石', 'isbn' => '9784101010021', 'published_date' => '1906-04-01', 'genres' => ['小説'], 'description' => '正義感の強い江戸っ子の新米教師が四国の中学校で繰り広げる痛快な青春小説。'],
            ['title' => 'サピエンス全史', 'author' => 'ユヴァル・ノア・ハラリ', 'isbn' => '9784309226712', 'published_date' => '2016-09-08', 'genres' => ['歴史', '科学'], 'description' => '認知革命・農業革命・科学革命を軸に、人類の歴史を壮大なスケールで描き出す。'],
            ['title' => 'Clean Code', 'author' => 'Robert C. Martin', 'isbn' => '9784048930598', 'published_date' => '2017-12-18', 'genres' => ['技術書'], 'description' => '読みやすく保守しやすいコードを書くための原則とプラクティスを詳細に解説する。'],
            ['title' => '嫌われる勇気', 'author' => '岸見一郎・古賀史健', 'isbn' => '9784478025819', 'published_date' => '2013-12-13', 'genres' => ['自己啓発'], 'description' => 'アドラー心理学を対話形式で分かりやすく解説し、幸福に生きるための考え方を示す。'],
            ['title' => '火花', 'author' => '又吉直樹', 'isbn' => '9784163902302', 'published_date' => '2015-03-11', 'genres' => ['小説'], 'description' => '売れない芸人の青春と葛藤を描き、芥川賞を受賞した話題作。'],
            ['title' => 'FACTFULNESS', 'author' => 'ハンス・ロスリング', 'isbn' => '9784822289607', 'published_date' => '2019-01-11', 'genres' => ['ビジネス', '科学'], 'description' => 'データに基づき世界を正しく見るための10の思い込みとその克服法を説く。'],
            ['title' => 'コンテナ物語', 'author' => 'マルク・レビンソン', 'isbn' => '9784822251468', 'published_date' => '2007-01-18', 'genres' => ['ビジネス', '歴史'], 'description' => '一つの規格化された箱が世界の物流と経済をいかに変えたかを描くノンフィクション。'],
        ];

        foreach ($books as $index => $data) {
            $book = Book::firstOrCreate(
                ['isbn' => $data['isbn']],
                [
                    'title' => $data['title'],
                    'author' => $data['author'],
                    'published_date' => $data['published_date'],
                    'description' => $data['description'],
                    'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text='.($index + 1),
                    'user_id' => $userId,
                ]
            );

            $genreIds = Genre::whereIn('name', $data['genres'])->pluck('id')->all();
            $book->genres()->sync($genreIds);
        }
    }
}
