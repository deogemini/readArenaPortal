<?php

use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;

test('readers must select every correct choice and no incorrect choices', function () {
    $reader = User::factory()->create();
    $book = Book::create(['title' => 'Selection Book', 'slug' => 'selection-book', 'status' => 'published']);
    $quiz = Quiz::create([
        'book_id' => $book->id,
        'title' => 'Selection Quiz',
        'pass_mark' => 70,
        'attempt_limit' => 3,
        'duration_minutes' => 10,
        'status' => 'published',
    ]);
    $question = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'prompt' => 'Choose the two correct answers.',
        'question_type' => 'multiple_choice',
        'points' => 10,
        'sort_order' => 1,
    ]);
    $correctOne = QuizAnswer::create(['quiz_question_id' => $question->id, 'body' => 'Correct one', 'is_correct' => true]);
    $wrong = QuizAnswer::create(['quiz_question_id' => $question->id, 'body' => 'Wrong', 'is_correct' => false]);
    $correctTwo = QuizAnswer::create(['quiz_question_id' => $question->id, 'body' => 'Correct two', 'is_correct' => true]);
    QuizAnswer::create(['quiz_question_id' => $question->id, 'body' => 'Also wrong', 'is_correct' => false]);

    $this->actingAs($reader)->from('/quiz')->post(route('reader.quizzes.submit', $quiz), [
        'answers' => [$question->id => [(string) $correctOne->id, (string) $correctTwo->id]],
    ])->assertRedirect('/quiz')->assertSessionHas('status');
    expect(QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $reader->id)->latest('id')->value('score'))->toBe(100);

    $this->actingAs($reader)->from('/quiz')->post(route('reader.quizzes.submit', $quiz), [
        'answers' => [$question->id => [(string) $correctOne->id]],
    ])->assertRedirect('/quiz')->assertSessionHas('status');
    expect(QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $reader->id)->latest('id')->value('score'))->toBe(0);

    $this->actingAs($reader)->from('/quiz')->post(route('reader.quizzes.submit', $quiz), [
        'answers' => [$question->id => [(string) $correctOne->id, (string) $correctTwo->id, (string) $wrong->id]],
    ])->assertRedirect('/quiz')->assertSessionHas('status');
    expect(QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $reader->id)->latest('id')->value('score'))->toBe(0);
});
