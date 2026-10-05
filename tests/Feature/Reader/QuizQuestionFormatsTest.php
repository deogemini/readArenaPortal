<?php

use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;

function makeQuizForQuestionFormats(string $suffix = ''): Quiz
{
    $book = Book::create([
        'title' => 'Question formats '.$suffix,
        'slug' => 'question-formats-'.strtolower(str_replace(' ', '-', $suffix ?: uniqid())),
        'status' => 'published',
    ]);

    return Quiz::create([
        'book_id' => $book->id,
        'title' => 'Format quiz '.$suffix,
        'pass_mark' => 70,
        'attempt_limit' => 3,
        'duration_minutes' => 10,
        'status' => 'published',
    ]);
}

test('administrators can create each supported question structure and update its type', function () {
    $admin = User::factory()->admin()->create();
    $quiz = makeQuizForQuestionFormats('admin');
    $choiceOptions = [
        ['body' => 'Option A'],
        ['body' => 'Option B'],
        ['body' => 'Option C'],
        ['body' => 'Option D'],
    ];

    $questions = [
        ['prompt' => 'Single choice prompt', 'question_type' => 'single_choice', 'answer_options' => $choiceOptions, 'correct_options' => ['1']],
        ['prompt' => 'Multiple choice prompt', 'question_type' => 'multiple_choice', 'answer_options' => $choiceOptions, 'correct_options' => ['0', '2']],
        ['prompt' => 'True or false prompt', 'question_type' => 'true_false', 'true_false_correct' => '0'],
        ['prompt' => 'One word prompt', 'question_type' => 'one_word', 'accepted_answers' => 'Ngorongoro'],
        ['prompt' => 'Short answer prompt', 'question_type' => 'short_answer', 'accepted_answers' => "Dar es Salaam\nDar-es-Salaam"],
        ['prompt' => 'Written response prompt', 'question_type' => 'written_response', 'answer_guide' => 'Look for two reasons supported by the text.'],
    ];
    foreach ($questions as $index => $payload) {
        $this->actingAs($admin)->post(route('admin.quizzes.questions.store', $quiz), [
            ...$payload,
            'points' => 10,
            'sort_order' => $index + 1,
        ])->assertRedirect(route('admin.quizzes'));

        $question = QuizQuestion::query()->where('quiz_id', $quiz->id)->where('prompt', $payload['prompt'])->firstOrFail();
        expect($question->question_type)->toBe($payload['question_type']);

        if ($payload['question_type'] === 'single_choice') {
            expect($question->answers()->where('is_correct', true)->count())->toBe(1);
        }
        if ($payload['question_type'] === 'true_false') {
            expect($question->answers()->where('is_correct', true)->value('body'))->toBe('True');
        }
        if ($payload['question_type'] === 'one_word') {
            expect($question->answers()->where('is_correct', true)->value('body'))->toBe('Ngorongoro');
        }
        if ($payload['question_type'] === 'written_response') {
            expect($question->answers()->where('is_correct', true)->value('body'))->toContain('two reasons');
        }
    }

    $oneWordQuestion = QuizQuestion::query()->where('quiz_id', $quiz->id)->where('question_type', 'one_word')->firstOrFail();
    $this->actingAs($admin)->patch(route('admin.quiz-questions.update', $oneWordQuestion), [
        'prompt' => $oneWordQuestion->prompt,
        'points' => 12,
        'sort_order' => $oneWordQuestion->sort_order,
        'question_type' => [$oneWordQuestion->id => 'short_answer'],
        'question_accepted_answers' => [$oneWordQuestion->id => "Ngorongoro\nNgorongoro Crater"],
    ])->assertRedirect(route('admin.quizzes'));

    expect($oneWordQuestion->fresh()->question_type)->toBe('short_answer')
        ->and($oneWordQuestion->fresh()->answers()->where('is_correct', true)->count())->toBe(2);
});

test('single choice questions reject more than one correct option', function () {
    $admin = User::factory()->admin()->create();
    $quiz = makeQuizForQuestionFormats('single validation');

    $this->actingAs($admin)->post(route('admin.quizzes.questions.store', $quiz), [
        'prompt' => 'Only one can be right',
        'question_type' => 'single_choice',
        'points' => 10,
        'sort_order' => 1,
        'answer_options' => [
            ['body' => 'A'], ['body' => 'B'], ['body' => 'C'], ['body' => 'D'],
        ],
        'correct_options' => ['0', '1'],
    ])->assertSessionHasErrors('correct_options');

    expect($quiz->questions()->count())->toBe(0);
});

test('readers can submit a typed answer and written responses are finalized by admin review', function () {
    $reader = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $quiz = makeQuizForQuestionFormats('manual review');
    $wordQuestion = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'prompt' => 'Name the mountain.',
        'question_type' => 'one_word',
        'points' => 10,
        'sort_order' => 1,
    ]);
    QuizAnswer::create(['quiz_question_id' => $wordQuestion->id, 'body' => 'Kilimanjaro', 'is_correct' => true]);
    $writtenQuestion = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'prompt' => 'Explain why the setting matters.',
        'question_type' => 'written_response',
        'points' => 10,
        'sort_order' => 2,
    ]);
    QuizAnswer::create(['quiz_question_id' => $writtenQuestion->id, 'body' => 'Award points for a relevant explanation.', 'is_correct' => true]);

    $this->actingAs($reader)->post(route('reader.quizzes.submit', $quiz), [
        'answers' => [
            $wordQuestion->id => 'kilimanjaro!',
            $writtenQuestion->id => 'The setting shapes the conflict and changes what the characters can do.',
        ],
    ])->assertSessionHas('status', 'Quiz submitted. Your written response is waiting for review.');

    $attempt = QuizAttempt::query()->where('quiz_id', $quiz->id)->where('user_id', $reader->id)->firstOrFail();
    expect($attempt->review_status)->toBe('pending_review')->and($attempt->score)->toBe(0);
    expect($attempt->responses()->where('quiz_question_id', $wordQuestion->id)->value('points_awarded'))->toBe(10);

    $this->actingAs($admin)->post(route('admin.quiz-attempts.review', $attempt), [
        'points_awarded' => [$writtenQuestion->id => 8],
    ])->assertRedirect(route('admin.quizzes'));

    expect($attempt->fresh()->review_status)->toBe('graded')
        ->and($attempt->fresh()->score)->toBe(90)
        ->and($attempt->fresh()->passed)->toBeTrue();
});

test('android quiz details include response type without exposing accepted text answers', function () {
    $reader = User::factory()->create();
    $quiz = makeQuizForQuestionFormats('android');
    $question = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'prompt' => 'What is the capital city?',
        'question_type' => 'short_answer',
        'points' => 10,
        'sort_order' => 1,
    ]);
    QuizAnswer::create(['quiz_question_id' => $question->id, 'body' => 'Dodoma', 'is_correct' => true]);
    $writtenQuestion = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'prompt' => 'Explain your reasoning.',
        'question_type' => 'written_response',
        'points' => 10,
        'sort_order' => 2,
    ]);
    QuizAnswer::create(['quiz_question_id' => $writtenQuestion->id, 'body' => 'Private marking guide text.', 'is_correct' => true]);

    $response = $this->actingAs($reader, 'sanctum')->getJson('/api/quizzes/'.$quiz->id);

    $response->assertOk()
        ->assertJsonPath('data.questions.0.question_type', 'short_answer')
        ->assertJsonPath('data.questions.0.response_format.kind', 'textarea')
        ->assertJsonPath('data.questions.0.response_format.auto_graded', true)
        ->assertJsonPath('data.questions.0.answers', [])
        ->assertJsonPath('data.questions.1.question_type', 'written_response')
        ->assertJsonPath('data.questions.1.response_format.requires_review', true)
        ->assertJsonPath('data.questions.1.answers', []);
    expect($response->getContent())->not->toContain('Dodoma');
    expect($response->getContent())->not->toContain('Private marking guide text.');

    $this->actingAs($reader, 'sanctum')->postJson('/api/quizzes/'.$quiz->id.'/submit', [
        'answers' => [
            $question->id => 'DODOMA!',
            $writtenQuestion->id => 'The text supports that conclusion because...',
        ],
    ])->assertOk()
        ->assertJsonPath('data.review_status', 'pending_review')
        ->assertJsonPath('data.score', null)
        ->assertJsonPath('data.passed', null);

    $this->actingAs($reader, 'sanctum')->getJson('/api/quizzes/'.$quiz->id)
        ->assertJsonPath('data.latest_attempt.review_status', 'pending_review')
        ->assertJsonPath('data.latest_attempt.score', null);
});
