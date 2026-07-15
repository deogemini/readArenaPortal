<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Lesson;
use App\Models\LiveShow;
use App\Models\Quiz;
use App\Models\Recommendation;
use App\Models\SubscriptionPackage;
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

    public function users(Request $request)
    {
        $role = $request->string('role')->toString();
        $query = User::query()->latest();

        if (in_array($role, ['reader', 'author', 'admin'], true)) {
            $query->where('role', $role);
        }

        return view('admin.users', [
            'users' => $query->paginate(20)->withQueryString(),
            'selectedRole' => $role,
        ]);
    }

    public function packages()
    {
        return view('admin.packages', [
            'packages' => SubscriptionPackage::latest()->paginate(20),
        ]);
    }

    public function storePackage(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:subscription_packages,name'],
            'price_tsh' => ['required', 'integer', 'min:1'],
            'games_count' => ['required', 'integer', 'min:1'],
            'reward_label' => ['nullable', 'string', 'max:255'],
            'region_scope' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        SubscriptionPackage::create([
            'name' => $payload['name'],
            'price_tsh' => $payload['price_tsh'],
            'games_count' => $payload['games_count'],
            'reward_label' => $payload['reward_label'] ?? null,
            'region_scope' => $payload['region_scope'] ?? 'global',
            'status' => $payload['status'] ?? 'active',
        ]);

        return redirect()->route('admin.packages')->with('status', 'Subscription package created.');
    }
}
