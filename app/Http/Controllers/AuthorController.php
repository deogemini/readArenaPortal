<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function dashboard(): View
    {
        $books = Book::whereHas('authors', function ($query) {
            $query->where('name', auth()->user()->name);
        })->latest()->take(10)->get();

        return view('author.dashboard', [
            'books' => $books,
            'quizzes' => Quiz::whereIn('book_id', $books->pluck('id'))->count(),
        ]);
    }

    public function storeBook(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'genre' => ['required', 'string', 'max:120'],
            'publication_year' => ['nullable', 'integer', 'min:1400', 'max:2100'],
            'page_count' => ['nullable', 'integer', 'min:1'],
            'cover_image' => ['nullable', 'url'],
        ]);

        $publisher = Publisher::firstOrCreate(['name' => 'Independent Press']);
        $book = Book::create([
            'title' => $payload['title'],
            'slug' => Str::slug($payload['title']).'-'.Str::lower(Str::random(6)),
            'description' => $payload['description'] ?? null,
            'publisher_id' => $publisher->id,
            'publication_year' => $payload['publication_year'] ?? null,
            'page_count' => $payload['page_count'] ?? null,
            'language' => 'en',
            'status' => 'draft',
            'cover_image' => $payload['cover_image'] ?? null,
        ]);

        $author = Author::firstOrCreate(['name' => auth()->user()->name]);
        $genre = Genre::firstOrCreate(
            ['slug' => Str::slug($payload['genre'])],
            ['name' => $payload['genre']]
        );

        $book->authors()->syncWithoutDetaching([$author->id]);
        $book->genres()->syncWithoutDetaching([$genre->id]);

        return redirect()->route('author.dashboard')->with('status', 'Book uploaded successfully and saved as draft.');
    }

    public function storeQuiz(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'title' => ['required', 'string', 'max:255'],
            'question' => ['required', 'string'],
            'correct_answer' => ['required', 'string', 'max:255'],
            'wrong_answer_1' => ['required', 'string', 'max:255'],
            'wrong_answer_2' => ['required', 'string', 'max:255'],
        ]);

        $quiz = Quiz::create([
            'book_id' => $payload['book_id'],
            'title' => $payload['title'],
            'instructions' => 'Author generated assessment.',
            'status' => 'draft',
            'pass_mark' => 70,
            'attempt_limit' => 3,
            'duration_minutes' => 10,
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'prompt' => $payload['question'],
            'question_type' => 'multiple_choice',
            'points' => 10,
            'sort_order' => 1,
        ]);

        QuizAnswer::create([
            'quiz_question_id' => $question->id,
            'body' => $payload['correct_answer'],
            'is_correct' => true,
        ]);
        QuizAnswer::create([
            'quiz_question_id' => $question->id,
            'body' => $payload['wrong_answer_1'],
            'is_correct' => false,
        ]);
        QuizAnswer::create([
            'quiz_question_id' => $question->id,
            'body' => $payload['wrong_answer_2'],
            'is_correct' => false,
        ]);

        return redirect()->route('author.dashboard')->with('status', 'Quiz draft with answers created.');
    }
}
