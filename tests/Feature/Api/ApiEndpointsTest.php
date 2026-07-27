<?php

use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('users can register through the api', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Android User',
        'email' => 'android@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ]);

    $this->assertDatabaseHas('users', ['email' => 'android@example.com']);
});

test('authenticated users can fetch their profile through the api', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/profile');

    $response
        ->assertStatus(200)
        ->assertJsonPath('data.email', $user->email);
});

test('books can be fetched through the api', function () {
    $user = User::factory()->create();

    Book::create([
        'title' => 'API Test Book',
        'slug' => 'api-test-book',
        'description' => 'A test book for the mobile API.',
        'publication_year' => 2024,
        'page_count' => 120,
        'language' => 'en',
        'isbn' => '9780000000001',
        'featured' => true,
        'status' => 'published',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/books');

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'description'],
            ],
        ]);
});

test('book details and quiz submission can be handled through the api', function () {
    $user = User::factory()->create();
    $book = Book::create([
        'title' => 'Quiz Book',
        'slug' => 'quiz-book',
        'description' => 'A book with a quiz.',
        'publication_year' => 2024,
        'page_count' => 100,
        'language' => 'en',
        'isbn' => '9780000000002',
        'featured' => true,
        'status' => 'published',
    ]);
    $quiz = Quiz::create([
        'book_id' => $book->id,
        'title' => 'Quiz One',
        'instructions' => 'Answer the question.',
        'pass_mark' => 70,
        'attempt_limit' => 3,
        'duration_minutes' => 5,
        'status' => 'published',
    ]);
    $question = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'prompt' => 'What is 2 + 2?',
        'question_type' => 'multiple_choice',
        'points' => 10,
        'sort_order' => 1,
    ]);
    QuizAnswer::create([
        'quiz_question_id' => $question->id,
        'body' => '4',
        'is_correct' => true,
    ]);

    $bookResponse = $this->actingAs($user, 'sanctum')->getJson('/api/books/' . $book->id);
    $bookResponse->assertStatus(200)->assertJsonPath('data.title', 'Quiz Book');

    $progressResponse = $this->actingAs($user, 'sanctum')->postJson('/api/books/' . $book->id . '/progress', [
        'current_page' => 25,
    ]);
    $progressResponse->assertStatus(200)->assertJsonPath('data.current_page', 25);

    $quizResponse = $this->actingAs($user, 'sanctum')->postJson('/api/quizzes/' . $quiz->id . '/submit', [
        'answers' => [$question->id => $question->answers()->first()->id],
    ]);
    $quizResponse->assertStatus(200)->assertJsonPath('data.score', 100);
});

test('profile photos can be uploaded through the api', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/profile/photo', [
        'profile_photo' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $response->assertStatus(200)->assertJsonStructure(['message', 'data' => ['profile_photo_url']]);
    $user->refresh();
    $this->assertNotNull($user->profile_photo_path);
});
