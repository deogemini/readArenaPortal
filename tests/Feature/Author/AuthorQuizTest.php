<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Quiz;
use App\Models\User;

test('authors can create quizzes with multiple correct choices', function () {
    $authorUser = User::factory()->create(['role' => 'author']);
    $book = Book::create(['title' => 'Author Book', 'slug' => 'author-book', 'status' => 'draft']);
    $author = Author::create(['name' => $authorUser->name]);
    $book->authors()->attach($author->id);

    $this->actingAs($authorUser)->post(route('author.quizzes.store'), [
        'book_id' => $book->id,
        'title' => 'Author Quiz',
        'question' => 'Choose both answers.',
        'answer_options' => [
            ['body' => 'First correct'],
            ['body' => 'Wrong'],
            ['body' => 'Second correct'],
            ['body' => 'Also wrong'],
        ],
        'correct_options' => ['0', '2'],
    ])->assertRedirect(route('author.dashboard'));

    $quiz = Quiz::where('title', 'Author Quiz')->firstOrFail();
    expect($quiz->questions->first()->answers->where('is_correct', true)->pluck('body')->all())
        ->toBe(['First correct', 'Second correct']);
});
