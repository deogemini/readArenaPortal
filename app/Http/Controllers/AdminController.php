<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Lesson;
use App\Models\LiveShow;
use App\Models\Quiz;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'readers' => User::count(),
            'books' => Book::count(),
            'quizzes' => Quiz::count(),
            'shows' => LiveShow::count(),
            'lessons' => Lesson::count(),
            'recommendations' => Recommendation::count(),
        ]);
    }

    public function books()
    {
        return view('admin.books', ['books' => Book::latest()->paginate(10)]);
    }

    public function quizzes()
    {
        return view('admin.quizzes', ['quizzes' => Quiz::with('book')->latest()->paginate(10)]);
    }

    public function duels()
    {
        return view('admin.duels');
    }

    public function shows()
    {
        return view('admin.shows', ['shows' => LiveShow::latest()->paginate(10)]);
    }

    public function settings()
    {
        return view('admin.settings');
    }
}
