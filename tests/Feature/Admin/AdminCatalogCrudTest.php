<?php

use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function adminCatalogBook(string $slug = 'admin-catalog-book'): Book
{
    return Book::create(['title' => 'Catalog Book', 'slug' => $slug, 'status' => 'published']);
}

test('administrators can create update and delete books and their uploaded PDF', function () {
    $admin = User::factory()->admin()->create();
    Storage::fake('local');

    $this->actingAs($admin)->post(route('admin.books.store'), [
        'title' => 'A New Novel',
        'author_name' => 'A. Writer',
        'genre_name' => 'Literary Fiction',
        'publisher_name' => 'Reader Press',
        'publication_year' => 2024,
        'page_count' => 240,
        'language' => 'en',
        'isbn' => '9781111111111',
        'status' => 'draft',
    ])->assertRedirect(route('admin.books'));

    $book = Book::where('title', 'A New Novel')->firstOrFail();
    expect($book->pdf_path)->toBeNull();

    $this->actingAs($admin)->post(route('admin.books.pdf.store', $book), [
        'pdf_file' => UploadedFile::fake()->create('novel.pdf', 10, 'application/pdf'),
    ])->assertRedirect(route('admin.books'));

    $book->refresh();
    Storage::disk('local')->assertExists($book->pdf_path);

    $this->actingAs($admin)->patch(route('admin.books.update', $book), [
        'title' => 'The New Novel',
        'author_name' => 'Another Writer',
        'genre_name' => 'Mystery',
        'publisher_name' => 'New Press',
        'publication_year' => 2025,
        'page_count' => 280,
        'language' => 'en',
        'isbn' => '9781111111112',
        'cover_image' => 'https://example.test/cover.jpg',
        'description' => 'Updated description.',
        'status' => 'published',
        'featured' => '1',
    ])->assertRedirect(route('admin.books'));

    expect($book->fresh()->title)->toBe('The New Novel')
        ->and($book->fresh()->status)->toBe('published')
        ->and((bool) $book->fresh()->featured)->toBeTrue()
        ->and($book->fresh()->authors->first()->name)->toBe('Another Writer');

    $pdfPath = $book->pdf_path;
    $this->actingAs($admin)->delete(route('admin.books.destroy', $book))->assertRedirect(route('admin.books'));
    $this->assertDatabaseMissing('books', ['id' => $book->id]);
    Storage::disk('local')->assertMissing($pdfPath);
});

test('administrators can upload a PDF larger than the previous 3MB limit', function () {
    $admin = User::factory()->admin()->create();
    Storage::fake('local');

    $this->actingAs($admin)->post(route('admin.books.store'), [
        'title' => 'A Larger Novel',
        'author_name' => 'A. Writer',
        'genre_name' => 'Literary Fiction',
        'status' => 'draft',
    ])->assertRedirect(route('admin.books'));

    $book = Book::where('title', 'A Larger Novel')->firstOrFail();
    $this->actingAs($admin)->post(route('admin.books.pdf.store', $book), [
        'pdf_file' => UploadedFile::fake()->create('larger-novel.pdf', 4096, 'application/pdf'),
    ])->assertRedirect(route('admin.books'));

    expect($book->fresh()->title)->toBe('A Larger Novel');
    $book->refresh();
    Storage::disk('local')->assertExists($book->pdf_path);
});

test('administrators can upload a PDF in small chunks and attach the assembled file', function () {
    $admin = User::factory()->admin()->create();
    $book = adminCatalogBook('chunked-pdf-book');
    Storage::fake('local');

    $pdf = '%PDF-1.7'.str_repeat('A', 600000);
    $chunks = str_split($pdf, 524288);
    $uploadId = (string) Str::uuid();
    $lastResponse = null;

    foreach ($chunks as $index => $chunk) {
        $lastResponse = $this->actingAs($admin)->call(
            'POST',
            route('admin.books.pdf.chunks', $book),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/octet-stream',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_UPLOAD_ID' => $uploadId,
                'HTTP_X_CHUNK_INDEX' => (string) $index,
                'HTTP_X_CHUNK_COUNT' => (string) count($chunks),
                'HTTP_X_TOTAL_SIZE' => (string) strlen($pdf),
            ],
            $chunk,
        );
    }

    $lastResponse->assertOk()->assertJsonPath('complete', true);
    $book->refresh();
    Storage::disk('local')->assertExists($book->pdf_path);
    expect(Storage::disk('local')->size($book->pdf_path))->toBe(strlen($pdf));
});

test('an oversized PDF request leaves the previously saved book details intact', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->post(route('admin.books.store'), [
        'title' => 'Saved Before PDF',
        'author_name' => 'A. Writer',
        'genre_name' => 'Literary Fiction',
        'description' => 'These details must survive a failed PDF upload.',
        'status' => 'draft',
    ])->assertRedirect(route('admin.books'));

    $book = Book::where('title', 'Saved Before PDF')->firstOrFail();

    $this->withServerVariables(['CONTENT_LENGTH' => '9999999999'])
        ->actingAs($admin)
        ->post(route('admin.books.pdf.store', $book), [])
        ->assertStatus(413)
        ->assertSee('This upload is too large.')
        ->assertSee('Book PDFs may be up to 100MB.');

    expect($book->fresh()->description)->toBe('These details must survive a failed PDF upload.')
        ->and($book->fresh()->pdf_path)->toBeNull();
});

test('administrators can create update and delete quizzes and questions', function () {
    $admin = User::factory()->admin()->create();
    $book = adminCatalogBook('quiz-crud-book');

    $this->actingAs($admin)->post(route('admin.quizzes.store'), [
        'book_id' => $book->id,
        'title' => 'First Quiz',
        'pass_mark' => 70,
        'attempt_limit' => 3,
        'duration_minutes' => 10,
        'status' => 'draft',
    ])->assertRedirect(route('admin.quizzes'));
    $quiz = Quiz::where('title', 'First Quiz')->firstOrFail();

    $this->actingAs($admin)->patch(route('admin.quizzes.update', $quiz), [
        'title' => 'Revised Quiz',
        'instructions' => 'Read carefully.',
        'pass_mark' => 75,
        'attempt_limit' => 4,
        'duration_minutes' => 12,
        'status' => 'published',
    ])->assertRedirect(route('admin.quizzes'));
    expect($quiz->fresh()->title)->toBe('Revised Quiz')->and($quiz->fresh()->pass_mark)->toBe(75);

    $this->actingAs($admin)->post(route('admin.quizzes.questions.store', $quiz), [
        'prompt' => 'Who is the narrator?',
        'points' => 10,
        'answer_options' => [
            ['body' => 'Mara'],
            ['body' => 'Jon'],
            ['body' => 'Lee'],
            ['body' => 'Nia'],
        ],
        'correct_options' => ['0', '2'],
    ])->assertRedirect(route('admin.quizzes'));
    $question = QuizQuestion::where('quiz_id', $quiz->id)->firstOrFail();
    expect($question->answers)->toHaveCount(4)
        ->and($question->answers->where('is_correct', true)->pluck('body')->all())->toBe(['Mara', 'Lee'])
        ->and($question->answers->where('is_correct', false))->toHaveCount(2);

    $this->actingAs($admin)->patch(route('admin.quiz-questions.update', $question), [
        'prompt' => 'Who tells the story?',
        'points' => 15,
        'sort_order' => 1,
        'question_answer_options' => [
            $question->id => [
                ['body' => 'Mara'],
                ['body' => 'Jon'],
                ['body' => 'Lee'],
                ['body' => 'Nia'],
            ],
        ],
        'question_correct_options' => [$question->id => ['1', '3']],
    ])->assertRedirect(route('admin.quizzes'));
    expect($question->fresh()->prompt)->toBe('Who tells the story?')
        ->and($question->fresh()->points)->toBe(15)
        ->and($question->fresh()->answers->where('is_correct', true)->pluck('body')->all())->toBe(['Jon', 'Nia']);

    $this->actingAs($admin)->delete(route('admin.quiz-questions.destroy', $question))->assertRedirect(route('admin.quizzes'));
    $this->assertDatabaseMissing('quiz_questions', ['id' => $question->id]);
    $this->actingAs($admin)->delete(route('admin.quizzes.destroy', $quiz))->assertRedirect(route('admin.quizzes'));
    $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
});

test('administrators can create update and delete arena packages', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.packages.store'), [
        'name' => 'Reader Pack',
        'price_tsh' => 15000,
        'games_count' => 8,
        'reward_label' => 'Gold badge',
        'region_scope' => 'global',
        'status' => 'active',
    ])->assertRedirect(route('admin.packages'));
    $package = SubscriptionPackage::where('name', 'Reader Pack')->firstOrFail();

    $this->actingAs($admin)->patch(route('admin.packages.update', $package), [
        'name' => 'Champion Pack',
        'price_tsh' => 25000,
        'games_count' => 12,
        'reward_label' => 'Champion badge',
        'region_scope' => 'east-africa',
        'status' => 'inactive',
    ])->assertRedirect(route('admin.packages'));
    expect($package->fresh()->name)->toBe('Champion Pack')->and($package->fresh()->status)->toBe('inactive');

    $this->actingAs($admin)->delete(route('admin.packages.destroy', $package))->assertRedirect(route('admin.packages'));
    $this->assertDatabaseMissing('subscription_packages', ['id' => $package->id]);
});
