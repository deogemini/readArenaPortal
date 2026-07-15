<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Lesson;
use App\Models\LiveShow;
use App\Models\Quiz;
use App\Models\Recommendation;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index()
    {
        return view('public.home', [
            'books' => Book::where('status', 'published')->latest()->take(4)->get(),
            'shows' => LiveShow::where('status', 'scheduled')->latest()->take(3)->get(),
            'lessons' => Lesson::where('status', 'published')->latest()->take(3)->get(),
            'quizzes' => Quiz::with('book')->where('status', 'published')->latest()->take(3)->get(),
            'recommendations' => Recommendation::where('status', 'published')->latest()->take(3)->get(),
        ]);
    }

    public function about()
    {
        return view('public.about');
    }

    public function library()
    {
        return view('public.library', [
            'books' => Book::where('status', 'published')->latest()->paginate(9),
        ]);
    }

    public function book(string $slug)
    {
        $book = Book::with(['authors', 'genres'])->where('slug', $slug)->firstOrFail();

        return view('public.book', compact('book'));
    }

    public function proArena()
    {
        return view('public.pro-arena', [
            'packages' => SubscriptionPackage::where('status', 'active')->orderBy('price_tsh')->get(),
        ]);
    }
}
