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
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $author = Author::create(['name' => 'Toni Morrison', 'bio' => 'Pulitzer Prize-winning novelist.']);
        $author2 = Author::create(['name' => 'Chinua Achebe', 'bio' => 'Influential Nigerian novelist.']);
        $author3 = Author::create(['name' => 'George Eliot', 'bio' => 'English novelist and poet.']);

        $genre = Genre::create(['name' => 'Literary Fiction', 'slug' => 'literary-fiction']);
        $genre2 = Genre::create(['name' => 'Historical Fiction', 'slug' => 'historical-fiction']);
        $genre3 = Genre::create(['name' => 'Classic', 'slug' => 'classic']);

        $publisher = Publisher::create(['name' => 'Vintage Books']);

        $book = Book::create([
            'title' => 'Beloved',
            'slug' => 'beloved',
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

        $book2 = Book::create([
            'title' => 'Things Fall Apart',
            'slug' => 'things-fall-apart',
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

        $admin = User::create([
            'name' => 'Aurelia Hart',
            'email' => 'admin@bookduel.test',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $reader = User::create([
            'name' => 'Mina Rivera',
            'email' => 'reader@bookduel.test',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $quiz = Quiz::create([
            'book_id' => $book2->id,
            'title' => 'Things Fall Apart Quiz',
            'instructions' => 'Show you read the novel.',
            'pass_mark' => 70,
            'attempt_limit' => 3,
            'duration_minutes' => 10,
            'status' => 'published',
        ]);

        $question = QuizQuestion::create(['quiz_id' => $quiz->id, 'prompt' => 'Who is the central narrator of the novel?', 'question_type' => 'multiple_choice', 'points' => 10, 'sort_order' => 1]);
        QuizAnswer::create(['quiz_question_id' => $question->id, 'body' => 'Okonkwo', 'is_correct' => true]);
        QuizAnswer::create(['quiz_question_id' => $question->id, 'body' => 'Obierika', 'is_correct' => false]);
        QuizAnswer::create(['quiz_question_id' => $question->id, 'body' => 'Nwoye', 'is_correct' => false]);

        ReadingGoal::create(['user_id' => $reader->id, 'title' => 'Yearly Reading Challenge', 'goal_type' => 'books', 'target_value' => 24, 'current_value' => 3, 'start_date' => now()->subMonths(2), 'end_date' => now()->addMonths(10), 'status' => 'active']);
        Lesson::create(['user_id' => $reader->id, 'book_id' => $book->id, 'title' => 'What memory teaches us', 'content' => '<p>Beloved forces us to ask how history stays alive in our bodies.</p>', 'visibility' => 'public', 'status' => 'published']);
        Recommendation::create(['user_id' => $reader->id, 'book_id' => $book2->id, 'message' => 'A fierce, unforgettable novel.', 'rating' => 5, 'visibility' => 'public', 'status' => 'published']);
        LiveShow::create(['title' => 'The Wednesday Salon', 'description' => 'A live conversation on memory and inheritance.', 'book_id' => $book->id, 'start_at' => now()->addDays(2), 'status' => 'scheduled']);

        PlatformSetting::create(['key' => 'platform_name', 'value' => 'BookDuel', 'type' => 'string']);
        PlatformSetting::create(['key' => 'hero_title', 'value' => 'Read the book. Prove it. Then duel.', 'type' => 'string']);
    }
}
