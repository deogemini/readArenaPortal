<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading Goals | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/90">
        <div class="mx-auto max-w-7xl px-6 py-4 lg:px-8">
            <a href="/reader/dashboard" class="font-serif text-2xl text-[#1B0D05]">Your goals</a>
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <section class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-8 shadow-sm">
                <h1 class="font-serif text-3xl text-[#1B0D05]">Set a new goal</h1>

                @if (session('status'))
                    <div class="mt-4 rounded-[16px] border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-3 text-sm text-[#1B0D05]">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 rounded-[16px] border border-[#c17b6f] bg-[#F4EBD8] px-4 py-3 text-sm text-[#7a2e22]">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('reader.goals.store') }}" method="POST" class="mt-6 grid gap-4">
                    @csrf
                    <input name="title" value="{{ old('title') }}" placeholder="Goal title" class="rounded-xl border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2" required>

                    <select name="goal_type" class="rounded-xl border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2" required>
                        <option value="books" @selected(old('goal_type', 'books') === 'books')>Books</option>
                        <option value="pages" @selected(old('goal_type') === 'pages')>Pages</option>
                    </select>

                    <input type="number" name="target_value" value="{{ old('target_value') }}" min="1" placeholder="Target value" class="rounded-xl border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2" required>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="mb-1 text-xs uppercase tracking-[0.2em] text-[#786A5D]">Start date</p>
                            <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" class="w-full rounded-xl border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2" required>
                        </div>
                        <div>
                            <p class="mb-1 text-xs uppercase tracking-[0.2em] text-[#786A5D]">End date</p>
                            <input type="date" name="end_date" value="{{ old('end_date', now()->addMonth()->toDateString()) }}" class="w-full rounded-xl border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2" required>
                        </div>
                    </div>

                    <button class="w-fit rounded-full bg-[#1B0D05] px-5 py-2 text-sm font-semibold text-[#FBF6EA]">Create goal</button>
                </form>
            </section>

            <section class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-8 shadow-sm">
                <h2 class="font-serif text-3xl text-[#1B0D05]">Your goals</h2>
                <div class="mt-6 space-y-4">
                    @forelse($goals as $goal)
                        <div class="rounded-[20px] border border-[#d8c9ad] bg-[#F4EBD8] p-5">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-[#1B0D05]">{{ $goal->title }}</h3>
                                <span class="rounded-full border border-[#d8c9ad] px-3 py-1 text-xs uppercase">{{ $goal->status }}</span>
                            </div>
                            <p class="mt-2 text-sm text-[#5e544d]">
                                {{ ucfirst($goal->goal_type) }} target: {{ $goal->current_value }} / {{ $goal->target_value }}
                            </p>
                            <p class="mt-1 text-xs text-[#786A5D]">
                                {{ \Illuminate\Support\Carbon::parse($goal->start_date)->format('M d, Y') }} - {{ \Illuminate\Support\Carbon::parse($goal->end_date)->format('M d, Y') }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-[#5e544d]">No goals yet. Create your first reading goal to start tracking progress.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
