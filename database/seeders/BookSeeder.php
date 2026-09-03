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
            ['title' => '吾輩は猫である', 'author' => '夏目漱石', 'isbn' => '9784101010014', 'published_date' => '1905-01-01', 'genres' => ['小説'], 'description' => '中学校の英語教師である珍野苦沙弥の家に飼われている猫である「吾輩」の視点から、珍野一家や、そこに出入りする人々の様子を風刺的に描いた作品。'],
            ['title' => '人を動かす', 'author' => 'D・カーネギー', 'isbn' => '9784422100524', 'published_date' => '1936-10-01', 'genres' => ['ビジネス', '自己啓発'], 'description' => '人を動かすための原則を、豊富な逸話を交えて説いた自己啓発の古典。相手の立場に立ち、誠実な関心を寄せることの大切さを伝える。'],
            ['title' => 'リーダブルコード', 'author' => 'Dustin Boswell', 'isbn' => '9784873115658', 'published_date' => '2012-06-23', 'genres' => ['技術書'], 'description' => '他人が読んで理解しやすいコードを書くための実践的なテクニックを、命名・コメント・制御フローなどの具体例とともに解説する。'],
            ['title' => '7つの習慣', 'author' => 'スティーブン・R・コヴィー', 'isbn' => '9784863940246', 'published_date' => '2013-08-30', 'genres' => ['ビジネス', '自己啓発'], 'description' => '主体性を発揮する、終わりを思い描くことから始めるなど、人格を磨き成功へと導く7つの習慣を体系的にまとめた自己啓発書。'],
            ['title' => '坊っちゃん', 'author' => '夏目漱石', 'isbn' => '9784101010021', 'published_date' => '1906-04-01', 'genres' => ['小説'], 'description' => '江戸っ子気質で正義感の強い新米教師が、赴任先の四国の中学校で個性的な同僚や生徒たちと繰り広げる騒動を痛快に描いた小説。'],
            ['title' => 'サピエンス全史', 'author' => 'ユヴァル・ノア・ハラリ', 'isbn' => '9784309226712', 'published_date' => '2016-09-08', 'genres' => ['歴史', '科学'], 'description' => '認知革命・農業革命・科学革命という3つの革命を軸に、ホモ・サピエンスがいかにして地球の支配者となったかを壮大に描き出す。'],
            ['title' => 'Clean Code', 'author' => 'Robert C. Martin', 'isbn' => '9784048930598', 'published_date' => '2017-12-18', 'genres' => ['技術書'], 'description' => '読みやすく保守しやすい「クリーンなコード」を書くための原則を、命名・関数・クラス・テストなど多角的な観点から解説する技術書。'],
            ['title' => '嫌われる勇気', 'author' => '岸見一郎・古賀史健', 'isbn' => '9784478025819', 'published_date' => '2013-12-13', 'genres' => ['自己啓発'], 'description' => 'アドラー心理学の考え方を哲人と青年の対話形式で解き明かし、対人関係の悩みから解放されて自由に生きる道を示す一冊。'],
            ['title' => '火花', 'author' => '又吉直樹', 'isbn' => '9784163902302', 'published_date' => '2015-03-11', 'genres' => ['小説'], 'description' => '売れない若手芸人の主人公と破天荒な先輩芸人との交流を通して、芸人としての生き方と青春の葛藤を描いた芥川賞受賞作。'],
            ['title' => 'FACTFULNESS', 'author' => 'ハンス・ロスリング', 'isbn' => '9784822289607', 'published_date' => '2019-01-11', 'genres' => ['ビジネス', '科学'], 'description' => '人が陥りがちな10の思い込みを指摘し、データと事実に基づいて世界を正しく読み解くための思考法を説く。'],
            ['title' => 'コンテナ物語', 'author' => 'マルク・レビンソン', 'isbn' => '9784822251468', 'published_date' => '2007-01-18', 'genres' => ['ビジネス', '歴史'], 'description' => '規格化された輸送用コンテナの登場が物流コストを劇的に下げ、世界経済とグローバル化を一変させた歴史を描くノンフィクション。'],
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
