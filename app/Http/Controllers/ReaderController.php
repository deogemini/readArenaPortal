<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Lesson;
use App\Models\LiveShow;
use App\Models\ReadingGoal;
use App\Models\Recommendation;
use Illuminate\Http\Request;

class ReaderController extends Controller
{
    public function dashboard()
    {
        return view('reader.dashboard', [
            'books' => Book::where('status', 'published')->latest()->take(6)->get(),
            'goals' => ReadingGoal::where('user_id', auth()->id())->latest()->take(3)->get(),
            'shows' => LiveShow::where('status', 'scheduled')->latest()->take(3)->get(),
            'lessons' => Lesson::where('user_id', auth()->id())->latest()->take(3)->get(),
            'recommendations' => Recommendation::where('user_id', auth()->id())->latest()->take(3)->get(),
        ]);
    }

    public function library()
    {
        return view('reader.library', [
            'books' => Book::where('status', 'published')->latest()->paginate(9),
        ]);
    }

    public function book(string $slug)
    {
        $book = Book::with(['authors', 'genres'])->where('slug', $slug)->firstOrFail();

        return view('reader.book', compact('book'));
    }

    public function goals()
    {
        return view('reader.goals', ['goals' => ReadingGoal::where('user_id', auth()->id())->latest()->get()]);
    }

    public function lessons()
    {
        return view('reader.lessons', ['lessons' => Lesson::where('user_id', auth()->id())->latest()->get()]);
    }

    public function duels()
    {
        return view('reader.duels');
    }

    public function shows()
    {
        return view('reader.shows', ['shows' => LiveShow::where('status', 'scheduled')->latest()->get()]);
    }
}
