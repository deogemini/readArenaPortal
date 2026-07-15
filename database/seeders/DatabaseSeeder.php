<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Lesson;
use App\Models\LiveShow;
use App\Models\PlatformSetting;
use App\Models\Publisher;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\ReadingGoal;
use App\Models\Recommendation;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $author = Author::updateOrCreate(
            ['name' => 'Toni Morrison'],
            ['bio' => 'Pulitzer Prize-winning novelist.']
        );
        $author2 = Author::updateOrCreate(
            ['name' => 'Chinua Achebe'],
            ['bio' => 'Influential Nigerian novelist.']
        );
        $author3 = Author::updateOrCreate(
            ['name' => 'George Eliot'],
            ['bio' => 'English novelist and poet.']
        );

        $genre = Genre::updateOrCreate(
            ['slug' => 'literary-fiction'],
            ['name' => 'Literary Fiction']
        );
        $genre2 = Genre::updateOrCreate(
            ['slug' => 'historical-fiction'],
            ['name' => 'Historical Fiction']
        );
        $genre3 = Genre::updateOrCreate(
            ['slug' => 'classic'],
            ['name' => 'Classic']
        );

        $publisher = Publisher::firstOrCreate(['name' => 'Vintage Books']);

        $book = Book::updateOrCreate([
            'slug' => 'beloved',
        ], [
            'title' => 'Beloved',
            'description' => 'A haunting novel about memory, trauma, and liberation.',
            'publisher_id' => $publisher->id,
            'publication_year' => 1987,
            'page_count' => 321,
            'language' => 'en',
            'isbn' => '9781400033416',
            'featured' => true,
            'status' => 'published',
            'cover_image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80',
        ]);
        $book->authors()->sync([$author->id]);
        $book->genres()->sync([$genre->id, $genre2->id]);

        $book2 = Book::updateOrCreate([
            'slug' => 'things-fall-apart',
        ], [
            'title' => 'Things Fall Apart',
            'description' => 'A landmark novel about colonialism and cultural change.',
            'publisher_id' => $publisher->id,
            'publication_year' => 1958,
            'page_count' => 209,
            'language' => 'en',
            'isbn' => '9780385474542',
            'featured' => true,
            'status' => 'published',
            'cover_image' => 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=800&q=80',
        ]);
        $book2->authors()->sync([$author2->id]);
        $book2->genres()->sync([$genre2->id, $genre3->id]);

        $admin = User::updateOrCreate(
            ['email' => 'admin@bookduel.test'],
            [
                'name' => 'Aurelia Hart',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'role' => 'admin',
            ]
        );

        $reader = User::updateOrCreate(
            ['email' => 'reader@bookduel.test'],
            [
                'name' => 'Mina Rivera',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'role' => 'reader',
            ]
        );

        User::updateOrCreate(
            ['email' => 'author@bookduel.test'],
            [
                'name' => 'Noah Kisembo',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'role' => 'author',
            ]
        );

        $quiz = Quiz::updateOrCreate([
            'book_id' => $book2->id,
            'title' => 'Things Fall Apart Quiz',
        ], [
            'instructions' => 'Show you read the novel.',
            'pass_mark' => 70,
            'attempt_limit' => 3,
            'duration_minutes' => 10,
            'status' => 'published',
        ]);

        $question = QuizQuestion::updateOrCreate(
            ['quiz_id' => $quiz->id, 'prompt' => 'Who is the central narrator of the novel?'],
            ['question_type' => 'multiple_choice', 'points' => 10, 'sort_order' => 1]
        );

        QuizAnswer::updateOrCreate(
            ['quiz_question_id' => $question->id, 'body' => 'Okonkwo'],
            ['is_correct' => true]
        );
        QuizAnswer::updateOrCreate(
            ['quiz_question_id' => $question->id, 'body' => 'Obierika'],
            ['is_correct' => false]
        );
        QuizAnswer::updateOrCreate(
            ['quiz_question_id' => $question->id, 'body' => 'Nwoye'],
            ['is_correct' => false]
        );

        ReadingGoal::updateOrCreate(
            ['user_id' => $reader->id, 'title' => 'Yearly Reading Challenge'],
            ['goal_type' => 'books', 'target_value' => 24, 'current_value' => 3, 'start_date' => now()->subMonths(2), 'end_date' => now()->addMonths(10), 'status' => 'active']
        );
        Lesson::updateOrCreate(
            ['user_id' => $reader->id, 'book_id' => $book->id, 'title' => 'What memory teaches us'],
            ['content' => '<p>Beloved forces us to ask how history stays alive in our bodies.</p>', 'visibility' => 'public', 'status' => 'published']
        );
        Recommendation::updateOrCreate(
            ['user_id' => $reader->id, 'book_id' => $book2->id],
            ['message' => 'A fierce, unforgettable novel.', 'rating' => 5, 'visibility' => 'public', 'status' => 'published']
        );
        LiveShow::updateOrCreate(
            ['title' => 'The Wednesday Salon'],
            ['description' => 'A live conversation on memory and inheritance.', 'book_id' => $book->id, 'start_at' => now()->addDays(2), 'status' => 'scheduled']
        );

        PlatformSetting::updateOrCreate(
            ['key' => 'platform_name'],
            ['value' => 'BookDuel', 'type' => 'string']
        );
        PlatformSetting::updateOrCreate(
            ['key' => 'hero_title'],
            ['value' => 'Read the book. Prove it. Then duel.', 'type' => 'string']
        );

        $packages = [
            ['name' => 'Compete in Tanzania', 'price_tsh' => 1000, 'games_count' => 10, 'reward_label' => 'Local Arena Rank', 'region_scope' => 'tanzania', 'status' => 'active'],
            ['name' => 'Compete Outside Tanzania', 'price_tsh' => 2000, 'games_count' => 10, 'reward_label' => 'International Arena Rank', 'region_scope' => 'outside-tanzania', 'status' => 'active'],
            ['name' => 'Compete to Win $200', 'price_tsh' => 1500, 'games_count' => 5, 'reward_label' => 'Win $200', 'region_scope' => 'global', 'status' => 'active'],
            ['name' => 'Compete for a Hard Copy', 'price_tsh' => 5000, 'games_count' => 4, 'reward_label' => 'Hard Copy Prize', 'region_scope' => 'global', 'status' => 'active'],
        ];

        foreach ($packages as $package) {
            SubscriptionPackage::updateOrCreate(
                ['name' => $package['name']],
                $package
            );
        }
    }
}
