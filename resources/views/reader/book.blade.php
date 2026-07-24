<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $book->title }} | Reader Book</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/90">
        <div class="mx-auto max-w-7xl px-6 py-4 lg:px-8">
            <a href="/reader/library" class="font-serif text-2xl text-[#1B0D05]">Reader book</a>
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-xl border border-[#d8c9ad] bg-[#FBF6EA] px-4 py-3 text-sm text-[#1B0D05]">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-[#c17b6f] bg-[#FBF6EA] px-4 py-3 text-sm text-[#7a2e22]">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-8 shadow-sm">
            <div class="grid gap-8 lg:grid-cols-[0.7fr_1.3fr]">
                <img src="{{ $book->cover_image ?? 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $book->title }}" class="h-80 w-full rounded-[20px] object-cover">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">{{ $book->genres->first()?->name ?? 'Classic' }}</p>
                    <h1 class="mt-2 font-serif text-3xl text-[#1B0D05]">{{ $book->title }}</h1>
                    <p class="mt-2 text-sm text-[#786A5D]">by {{ $book->authors->first()?->name ?? 'Unknown author' }}</p>
                    <p class="mt-6 text-base leading-8 text-[#5e544d]">{{ $book->description }}</p>
                    <div class="mt-6 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-[#d8c9ad] px-3 py-1">{{ $book->publication_year }}</span>
                        <span class="rounded-full border border-[#d8c9ad] px-3 py-1">{{ $book->page_count }} pages</span>
                        <span class="rounded-full border border-[#d8c9ad] px-3 py-1">{{ strtoupper($book->language) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <section class="mt-8 space-y-6">
            <h2 class="font-serif text-3xl text-[#1B0D05]">Book Quiz</h2>

            @forelse($book->quizzes as $quiz)
                @php($stats = $quizStats[$quiz->id] ?? ['attempts' => 0, 'best_score' => 0])
                <article class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="font-serif text-2xl text-[#1B0D05]">{{ $quiz->title }}</h3>
                            <p class="mt-1 text-sm text-[#786A5D]">{{ $quiz->instructions ?: 'Answer the questions and earn points.' }}</p>
                        </div>
                        <div class="text-sm text-[#5e544d]">
                            <p>Attempts: {{ $stats['attempts'] }} / {{ $quiz->attempt_limit }}</p>
                            <p>Best score: {{ $stats['best_score'] }} pts</p>
                        </div>
                    </div>

                    <form action="{{ route('reader.quizzes.submit', $quiz) }}" method="POST" class="mt-5 space-y-5">
                        @csrf
                        @foreach($quiz->questions->sortBy('sort_order') as $question)
                            <div class="rounded-[18px] border border-[#d8c9ad] bg-[#F4EBD8] p-4">
                                <p class="font-semibold text-[#1B0D05]">{{ $question->prompt }}</p>
                                <p class="mt-1 text-xs text-[#786A5D]">Points: {{ $question->points }}</p>
                                <div class="mt-3 space-y-2">
                                    @foreach($question->answers as $answer)
                                        <label class="flex items-center gap-2 text-sm text-[#24150D]">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}" required>
                                            <span>{{ $answer->body }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <button class="rounded-full bg-[#1B0D05] px-6 py-2 text-sm font-semibold text-[#FBF6EA]">Submit answers</button>
                    </form>
                </article>
            @empty
                <div class="rounded-[20px] border border-[#d8c9ad] bg-[#FBF6EA] p-5 text-sm text-[#786A5D]">
                    No published quiz yet for this book.
                </div>
            @endforelse
        </section>
    </main>
</div>
</body>
</html>
