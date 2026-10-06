<?php

use App\Models\Book;
use App\Models\Duel;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\ReaderIdea;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function mobileParityBook(string $suffix, ?string $pdfPath = null): Book
{
    return Book::create([
        'title' => 'Mobile contract '.$suffix,
        'slug' => 'mobile-contract-'.$suffix,
        'status' => 'published',
        'page_count' => 100,
        'pdf_path' => $pdfPath,
    ]);
}

function mobileParityQuiz(Book $book, string $title = 'Duel quiz'): Quiz
{
    return Quiz::create([
        'book_id' => $book->id,
        'title' => $title,
        'pass_mark' => 70,
        'attempt_limit' => 3,
        'duration_minutes' => 10,
        'status' => 'published',
    ]);
}

function mobileParityVerifyReader(User $reader, Book $book): void
{
    $quiz = mobileParityQuiz($book, 'Eligibility '.$reader->id);
    QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'user_id' => $reader->id,
        'score' => 90,
        'passed' => true,
        'review_status' => 'graded',
    ]);
}

test('mobile reading supports PUT aliases and returns a PDF chapter manifest', function () {
    Storage::fake('local');
    Storage::disk('local')->put('books/sample.pdf', '%PDF sample');
    $reader = User::factory()->create();
    $book = mobileParityBook('reading', 'books/sample.pdf');

    $this->actingAs($reader, 'sanctum')->putJson('/api/books/'.$book->id.'/progress', ['page_number' => 24])
        ->assertOk()
        ->assertJsonPath('data.current_page', 24);

    $this->actingAs($reader, 'sanctum')->getJson('/api/books/'.$book->id.'/content')
        ->assertOk()
        ->assertJsonPath('data.book_id', $book->id)
        ->assertJsonPath('data.chapters.0.id', 'full-book')
        ->assertJsonPath('data.chapters.0.content_type', 'application/pdf');

    $this->actingAs($reader, 'sanctum')->get('/api/books/'.$book->id.'/content/pdf')->assertOk();
});

test('mobile duel detail shows role permissions and both readers can submit one scored answer set', function () {
    $challenger = User::factory()->create(['profile_photo_path' => 'profile-photos/challenger.png']);
    $opponent = User::factory()->create(['profile_photo_path' => 'profile-photos/opponent.png']);
    $challenger->forceFill(['last_seen_at' => now()])->save();
    $opponent->forceFill(['last_seen_at' => now()])->save();
    $book = mobileParityBook('duel');
    mobileParityVerifyReader($challenger, $book);
    mobileParityVerifyReader($opponent, $book);
    $quiz = mobileParityQuiz($book, 'Match quiz');
    $question = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'prompt' => 'Which answer is correct?',
        'question_type' => 'single_choice',
        'points' => 10,
        'sort_order' => 1,
    ]);
    $correct = QuizAnswer::create(['quiz_question_id' => $question->id, 'body' => 'Correct', 'is_correct' => true]);
    $incorrect = QuizAnswer::create(['quiz_question_id' => $question->id, 'body' => 'Incorrect', 'is_correct' => false]);
    $duel = Duel::create(['book_id' => $book->id, 'challenger_id' => $challenger->id, 'opponent_id' => $opponent->id, 'status' => 'accepted']);

    $this->actingAs($opponent, 'sanctum')->getJson('/api/duels/'.$duel->id)
        ->assertOk()
        ->assertJsonPath('data.permissions.can_accept', false)
        ->assertJsonPath('data.permissions.can_decline', false)
        ->assertJsonPath('data.permissions.can_submit_answers', true)
        ->assertJsonPath('data.challenger.profile_photo_url', Storage::disk('public')->url('profile-photos/challenger.png'))
        ->assertJsonPath('data.challenger.is_online', true);

    $this->actingAs($challenger, 'sanctum')->getJson('/api/duels/'.$duel->id)
        ->assertJsonPath('data.permissions.can_cancel', true);

    $this->actingAs($challenger, 'sanctum')->postJson('/api/duels/'.$duel->id.'/answers', [
        'quiz_id' => $quiz->id,
        'answers' => [$question->id => $correct->id],
    ])->assertOk()->assertJsonPath('data.status', 'live')->assertJsonPath('data.challenger_result.score', 100)
        ->assertJsonPath('data.opponent_result.score', null);

    $this->actingAs($opponent, 'sanctum')->postJson('/api/duels/'.$duel->id.'/answers', [
        'quiz_id' => $quiz->id,
        'answers' => [$question->id => $incorrect->id],
    ])->assertOk()->assertJsonPath('data.status', 'completed')->assertJsonPath('data.winner_id', $challenger->id);

    $this->actingAs($challenger, 'sanctum')->postJson('/api/duels/'.$duel->id.'/answers', [
        'quiz_id' => $quiz->id,
        'answers' => [$question->id => $correct->id],
    ])->assertUnprocessable();
});

test('reader directory, like requests, and staff ideas APIs enforce their roles', function () {
    $reader = User::factory()->create();
    $other = User::factory()->create();
    $other->forceFill(['last_seen_at' => now()])->save();
    $this->actingAs($reader, 'sanctum')->getJson('/api/readers?online_only=1')
        ->assertOk()
        ->assertJsonPath('data.0.id', $other->id)
        ->assertJsonPath('data.0.is_online', true);

    $request = $this->actingAs($reader, 'sanctum')->postJson('/api/readers/'.$other->id.'/like')->assertCreated()
        ->assertJsonPath('data.status', 'pending');
    $requestId = $request->json('data.id');
    $this->actingAs($other, 'sanctum')->getJson('/api/like-requests?direction=received')
        ->assertOk()->assertJsonPath('data.0.id', $requestId)->assertJsonPath('data.0.permissions.can_respond', true);
    $this->actingAs($other, 'sanctum')->patchJson('/api/like-requests/'.$requestId.'/respond', ['action' => 'accept'])
        ->assertOk()->assertJsonPath('data.status', 'accepted');

    $idea = ReaderIdea::create([
        'user_id' => $reader->id,
        'title' => 'Show reading streaks',
        'description' => 'Please show a reading streak on the dashboard.',
        'category' => 'app',
    ]);
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin, 'sanctum')->getJson('/api/staff/ideas')
        ->assertOk()->assertJsonPath('data.0.id', $idea->id)->assertJsonPath('data.0.reader.id', $reader->id);
    $this->actingAs($admin, 'sanctum')->patchJson('/api/staff/ideas/'.$idea->id, ['status' => 'planned', 'admin_notes' => 'Accepted for planning.'])
        ->assertOk()->assertJsonPath('data.status', 'planned')->assertJsonPath('data.admin_notes', 'Accepted for planning.');
    $this->actingAs($reader, 'sanctum')->getJson('/api/staff/ideas')->assertForbidden();
});

test('activity and rewards APIs are available and account deletion checks the password', function () {
    $reader = User::factory()->create();
    $book = mobileParityBook('rewards');
    $quiz = mobileParityQuiz($book, 'Reward quiz');
    $questionOne = QuizQuestion::create(['quiz_id' => $quiz->id, 'prompt' => 'Question one?', 'question_type' => 'single_choice', 'points' => 10, 'sort_order' => 1]);
    $questionTwo = QuizQuestion::create(['quiz_id' => $quiz->id, 'prompt' => 'Question two?', 'question_type' => 'single_choice', 'points' => 10, 'sort_order' => 2]);
    $correctOne = QuizAnswer::create(['quiz_question_id' => $questionOne->id, 'body' => 'Right one', 'is_correct' => true]);
    $wrongOne = QuizAnswer::create(['quiz_question_id' => $questionOne->id, 'body' => 'Wrong one', 'is_correct' => false]);
    $correctTwo = QuizAnswer::create(['quiz_question_id' => $questionTwo->id, 'body' => 'Right two', 'is_correct' => true]);
    $wrongTwo = QuizAnswer::create(['quiz_question_id' => $questionTwo->id, 'body' => 'Wrong two', 'is_correct' => false]);
    $quiz->update(['pass_mark' => 50]);

    $this->actingAs($reader, 'sanctum')->postJson('/api/quizzes/'.$quiz->id.'/submit', [
        'answers' => [$questionOne->id => $correctOne->id, $questionTwo->id => $wrongTwo->id],
    ])->assertOk()->assertJsonPath('data.score', 50)->assertJsonPath('data.reward_points', 50)->assertJsonPath('data.reward_balance', 50);
    $this->actingAs($reader, 'sanctum')->postJson('/api/quizzes/'.$quiz->id.'/submit', [
        'answers' => [$questionOne->id => $correctOne->id, $questionTwo->id => $correctTwo->id],
    ])->assertOk()->assertJsonPath('data.score', 100)->assertJsonPath('data.reward_points', 50)->assertJsonPath('data.reward_balance', 100);
    $this->actingAs($reader, 'sanctum')->getJson('/api/rewards/history')
        ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('meta.points_balance', 100);
    $this->actingAs($reader, 'sanctum')->getJson('/api/activity')
        ->assertOk()->assertJsonPath('data.0.activity_key', 'completed_quiz');

    $this->actingAs($reader, 'sanctum')->deleteJson('/api/account', ['password' => 'wrong-password'])->assertUnprocessable();
    $this->actingAs($reader, 'sanctum')->deleteJson('/api/account', ['password' => 'password'])->assertOk();
    $this->assertDatabaseMissing('users', ['id' => $reader->id]);
});
