<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $book->title }} | Reader Book</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D] antialiased">
@include('components.language-switcher')
<div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(216,168,62,0.16),_transparent_38%)]">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/95">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <a href="{{ route('reader.dashboard') }}" class="group w-fit">
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-[#B98A2C]">Reader lounge</p>
                <p class="mt-1 font-serif text-xl text-[#1B0D05] group-hover:text-[#8a5d13]">Welcome back, {{ auth()->user()->name }}</p>
            </a>
            <nav aria-label="Reader navigation" class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 lg:mx-0 lg:flex-wrap lg:justify-end lg:overflow-visible lg:pb-0">
                <a href="{{ route('reader.dashboard') }}" class="shrink-0 rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm transition hover:border-[#B98A2C] hover:bg-white">Dashboard</a>
                <a href="{{ route('reader.library') }}" aria-current="page" class="shrink-0 rounded-full border border-[#1B0D05] bg-[#1B0D05] px-4 py-2 text-sm text-[#FBF6EA]">Library</a>
                <a href="{{ route('reader.goals') }}" class="shrink-0 rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm transition hover:border-[#B98A2C] hover:bg-white">Goals</a>
                <a href="{{ route('reader.lessons') }}" class="shrink-0 rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm transition hover:border-[#B98A2C] hover:bg-white">Lessons</a>
                <a href="{{ route('reader.shows') }}" class="shrink-0 rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm transition hover:border-[#B98A2C] hover:bg-white">Shows</a>
                <a href="{{ route('reader.duels') }}" class="shrink-0 rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm transition hover:border-[#B98A2C] hover:bg-white">Duels</a>
                <a href="/logout" class="shrink-0 rounded-full bg-[#1B0D05] px-4 py-2 text-sm text-[#FBF6EA] transition hover:bg-[#432919]" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                <form id="logout-form" action="/logout" method="POST" class="hidden">@csrf</form>
            </nav>
        </div>
    </header>

    @php
        $resumePage = max(1, (int) $progress->last_page_read);
        $readingPercent = $book->page_count ? max(0, min(100, (int) round(($progress->last_page_read / $book->page_count) * 100))) : 0;
        $authors = $book->authors->pluck('name')->filter()->implode(', ');
        $genres = $book->genres->pluck('name')->filter()->implode(' / ');
    @endphp

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <a href="{{ route('reader.library') }}" class="inline-flex items-center gap-2 text-sm font-medium text-[#786A5D] transition hover:text-[#1B0D05]">
            <span aria-hidden="true">←</span> Back to library
        </a>

        @if (session('status'))
            <div role="status" class="mt-5 flex items-start gap-3 rounded-2xl border border-[#b9c9a7] bg-[#f5f8ef] px-4 py-3 text-sm text-[#35472d] shadow-sm">
                <span aria-hidden="true" class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#dce9ce] font-bold">✓</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mt-5 rounded-2xl border border-[#c17b6f] bg-[#fff8f4] px-4 py-3 text-sm text-[#7a2e22] shadow-sm">
                <p class="font-semibold">Please check the following:</p>
                <ul class="mt-1 list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <section class="mt-5 overflow-hidden rounded-[28px] border border-[#dfcfad] bg-[#FBF6EA] shadow-[0_16px_50px_rgba(60,37,17,0.08)]" aria-labelledby="book-title">
            <div class="grid lg:grid-cols-[minmax(230px,0.68fr)_minmax(0,1.32fr)]">
                <div class="relative flex items-center justify-center bg-[linear-gradient(145deg,#ead7ad,#f5ead0_55%,#e8d5aa)] p-6 sm:p-8 lg:p-10">
                    <div class="absolute left-5 top-5 rounded-full border border-white/60 bg-[#FBF6EA]/85 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#72551d] shadow-sm">Book details</div>
                    <img src="{{ $book->cover_image ?: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80' }}" alt="Cover of {{ $book->title }}" class="max-h-[26rem] w-full max-w-[20rem] rounded-[18px] object-cover shadow-[0_20px_40px_rgba(36,21,13,0.22)] ring-1 ring-black/5">
                </div>

                <div class="flex flex-col p-6 sm:p-8 lg:p-10">
                    <div class="flex flex-wrap items-center gap-2">
                        @if($genres)<span class="text-xs font-semibold uppercase tracking-[0.2em] text-[#A77920]">{{ $genres }}</span>@endif
                        @if($book->featured)<span class="rounded-full bg-[#f2e2b8] px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-[#75500f]">Featured</span>@endif
                    </div>
                    <h1 id="book-title" class="mt-3 font-serif text-3xl leading-tight text-[#1B0D05] sm:text-4xl lg:text-[2.8rem]" data-no-translate>{{ $book->title }}</h1>
                    <p class="mt-2 text-base text-[#786A5D]">by <span class="font-medium text-[#4f4035]" data-no-translate>{{ $authors ?: 'Unknown author' }}</span></p>
                    @if($book->description)
                        <p class="mt-5 max-w-3xl text-[15px] leading-7 text-[#5e544d]" data-no-translate>{{ $book->description }}</p>
                    @endif

                    <dl class="mt-6 grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @if($book->publication_year)<div class="rounded-xl border border-[#e5d8bf] bg-white/55 px-3 py-2.5"><dt class="text-[10px] font-semibold uppercase tracking-[0.16em] text-[#897a67]">Published</dt><dd class="mt-0.5 text-sm font-semibold text-[#342319]">{{ $book->publication_year }}</dd></div>@endif
                        @if($book->page_count)<div class="rounded-xl border border-[#e5d8bf] bg-white/55 px-3 py-2.5"><dt class="text-[10px] font-semibold uppercase tracking-[0.16em] text-[#897a67]">Length</dt><dd class="mt-0.5 text-sm font-semibold text-[#342319]">{{ number_format($book->page_count) }} pages</dd></div>@endif
                        @if($book->language)<div class="rounded-xl border border-[#e5d8bf] bg-white/55 px-3 py-2.5"><dt class="text-[10px] font-semibold uppercase tracking-[0.16em] text-[#897a67]">Language</dt><dd class="mt-0.5 text-sm font-semibold text-[#342319]">{{ strtoupper($book->language) }}</dd></div>@endif
                    </dl>

                    <div class="mt-7 flex flex-wrap gap-3">
                        @if($book->pdf_path)
                            <a href="#read-book" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#1B0D05] px-6 py-3 text-sm font-semibold text-[#FBF6EA] shadow-sm transition hover:-translate-y-0.5 hover:bg-[#432919] focus:outline-none focus:ring-2 focus:ring-[#B98A2C] focus:ring-offset-2">
                                {{ $progress->last_page_read > 0 ? 'Continue reading' : 'Read this book' }} <span aria-hidden="true">→</span>
                            </a>
                        @else
                            <span class="inline-flex cursor-not-allowed items-center rounded-full bg-[#d7cdbb] px-6 py-3 text-sm font-semibold text-[#6f6559]" title="The book file is not available yet">Book file unavailable</span>
                        @endif
                        @if($book->quizzes->isNotEmpty())
                            <a href="#book-quizzes" class="inline-flex items-center justify-center rounded-full border border-[#cbb992] bg-white/50 px-6 py-3 text-sm font-semibold text-[#3a281a] transition hover:border-[#9b7429] hover:bg-white">Explore quizzes</a>
                        @endif
                    </div>

                    <div class="mt-7 border-t border-[#e5d8bf] pt-5">
                        <div class="flex items-center justify-between gap-3 text-xs text-[#786A5D]">
                            <span class="font-semibold uppercase tracking-[0.14em]">Your reading progress</span>
                            <span class="font-medium text-[#3e2d20]">{{ $book->page_count ? $readingPercent.'%' : 'Page '.$progress->last_page_read }}</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-[#e9dfcc]" role="progressbar" aria-label="Reading progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $readingPercent }}">
                            <div class="h-full rounded-full bg-[#c18b25] transition-all" style="width: {{ $readingPercent }}%"></div>
                        </div>
                        <p class="mt-2 text-xs text-[#786A5D]">{{ $progress->last_page_read > 0 ? 'Last reached page '.$progress->last_page_read.($book->page_count ? ' of '.number_format($book->page_count) : '') : 'Your reading progress will appear here.' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <nav aria-label="On this page" class="sticky top-0 z-20 -mx-4 mt-5 border-y border-[#dfcfad] bg-[#F4EBD8]/95 px-4 py-2 backdrop-blur sm:static sm:mx-0 sm:rounded-2xl sm:border sm:px-3">
            <div class="flex gap-2 overflow-x-auto sm:flex-wrap">
                <a href="#read-book" class="shrink-0 rounded-full px-3 py-2 text-xs font-semibold text-[#4c3827] transition hover:bg-[#eadcc1]">Read</a>
                <a href="#book-quizzes" class="shrink-0 rounded-full px-3 py-2 text-xs font-semibold text-[#4c3827] transition hover:bg-[#eadcc1]">Quizzes & results</a>
                <a href="#reading-tools" class="shrink-0 rounded-full px-3 py-2 text-xs font-semibold text-[#4c3827] transition hover:bg-[#eadcc1]">Progress & bookmarks</a>
                <a href="#book-review" class="shrink-0 rounded-full px-3 py-2 text-xs font-semibold text-[#4c3827] transition hover:bg-[#eadcc1]">Write a review</a>
            </div>
        </nav>

        <section id="read-book" class="scroll-mt-20 mt-5 rounded-[26px] border border-[#dfcfad] bg-[#FBF6EA] p-5 shadow-sm sm:p-7">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">Your reading space</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05] sm:text-3xl">{{ $progress->last_page_read > 0 ? 'Pick up where you left off' : 'Start reading' }}</h2>
                    @if($book->pdf_path)<p class="mt-1 text-sm text-[#786A5D]">The book opens at page {{ $resumePage }}. Your progress can be updated below the reader.</p>@endif
                </div>
                @if($book->pdf_path)
                    <a href="{{ route('reader.books.content', $book->slug).'#page='.$resumePage }}" target="_blank" rel="noopener" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm font-semibold text-[#3a281a] transition hover:bg-white">Open reader in new tab</a>
                @endif
            </div>
            @if ($book->pdf_path)
                <div class="mt-5 overflow-hidden rounded-2xl border border-[#d8c9ad] bg-white shadow-inner">
                    <iframe src="{{ route('reader.books.content', $book->slug).'#page='.$resumePage }}" class="h-[68vh] min-h-[28rem] w-full bg-white" title="{{ $book->title }} PDF reader" loading="lazy"></iframe>
                </div>
            @else
                <div class="mt-5 flex flex-col items-start gap-3 rounded-2xl border border-dashed border-[#cbb992] bg-[#f6eedf] p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div><p class="font-semibold text-[#3a281a]">The PDF is not available yet</p><p class="mt-1 text-sm text-[#786A5D]">You can still save this title to your shelf, set a goal, or try a published quiz.</p></div>
                    <a href="{{ route('reader.library') }}" class="shrink-0 rounded-full border border-[#cbb992] px-4 py-2 text-sm font-semibold text-[#3a281a] hover:bg-white">Browse library</a>
                </div>
            @endif
        </section>

        <section id="book-quizzes" class="scroll-mt-20 mt-5 space-y-5" aria-labelledby="quiz-section-title">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">Learn as you read</p>
                    <h2 id="quiz-section-title" class="mt-1 font-serif text-2xl text-[#1B0D05] sm:text-3xl">Quizzes & reader results</h2>
                    <p class="mt-1 text-sm text-[#786A5D]">Try a quiz or see how readers are doing with this book.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <article class="rounded-2xl border border-[#dfcfad] bg-[#FBF6EA] p-4"><p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[#786A5D]">Published quizzes</p><p class="mt-1 font-serif text-3xl text-[#1B0D05]">{{ (int) $book->published_quizzes_count }}</p></article>
                <article class="rounded-2xl border border-[#dfcfad] bg-[#FBF6EA] p-4"><p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[#786A5D]">Readers attempted</p><p class="mt-1 font-serif text-3xl text-[#1B0D05]">{{ (int) $book->quiz_readers_count }}</p></article>
                <article class="rounded-2xl border border-[#dfcfad] bg-[#FBF6EA] p-4"><p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[#786A5D]">Total attempts</p><p class="mt-1 font-serif text-3xl text-[#1B0D05]">{{ (int) $book->quiz_attempts_count }}</p></article>
                <article class="rounded-2xl border border-[#dfcfad] bg-[#FBF6EA] p-4"><p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[#786A5D]">Average score</p><p class="mt-1 font-serif text-3xl text-[#1B0D05]">{{ $book->quiz_average_score !== null ? number_format((float) $book->quiz_average_score, 1).'%' : '—' }}</p></article>
            </div>

            <div class="flex flex-wrap gap-x-6 gap-y-1 rounded-2xl border border-[#e2d5bd] bg-[#f5eddf] px-4 py-3 text-xs text-[#6f6253]">
                <span>{{ (int) $book->quiz_passed_attempts_count }} passed attempts</span>
                <span>{{ $book->quiz_pass_rate !== null ? number_format($book->quiz_pass_rate, 1).'%' : '—' }} pass rate</span>
                <span>{{ $book->quiz_best_score !== null ? (int) $book->quiz_best_score.'%' : '—' }} highest score</span>
                <span class="sm:ml-auto">Aggregate results from graded attempts</span>
            </div>

            @forelse($book->quizzes as $quiz)
                @php($stats = $quizStats[$quiz->id] ?? ['attempts' => 0, 'pending_review' => 0, 'best_score' => 0, 'latest_status' => null, 'latest_score' => null, 'latest_passed' => null])
                <article class="overflow-hidden rounded-[24px] border border-[#dfcfad] bg-[#FBF6EA] shadow-sm">
                    <div class="grid lg:grid-cols-[minmax(0,1fr)_18rem]">
                        <div class="p-5 sm:p-6">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-[#f2e2b8] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-[#75500f]">Quiz</span>
                                        <span class="text-xs text-[#786A5D]">{{ $quiz->questions->count() }} {{ $quiz->questions->count() === 1 ? 'question' : 'questions' }}</span>
                                    </div>
                                    <h3 class="mt-2 font-serif text-2xl text-[#1B0D05]" data-no-translate>{{ $quiz->title }}</h3>
                                    @if($quiz->instructions)<p class="mt-1 max-w-2xl text-sm leading-6 text-[#786A5D]">{{ $quiz->instructions }}</p>@endif
                                </div>
                            </div>

                            @if($stats['latest_status'] === 'pending_review')
                                <p class="mt-4 rounded-xl border border-[#e6d49e] bg-[#fbf5e4] px-3 py-2 text-sm text-[#705311]">Your latest written response is awaiting review.</p>
                            @elseif($stats['latest_status'] === 'graded')
                                <p class="mt-4 rounded-xl border border-[#d7e2ca] bg-[#f4f8ef] px-3 py-2 text-sm text-[#425735]">Your latest result: <strong>{{ $stats['latest_score'] }}%</strong> · {{ $stats['latest_passed'] ? 'Passed' : 'Not passed yet' }}</p>
                            @endif

                            @if($stats['attempts'] < $quiz->attempt_limit)
                            <details class="group mt-5 overflow-hidden rounded-2xl border border-[#dfcfad] bg-white/40" @if($errors->has('answers') || $errors->has('quiz')) open @endif>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-3.5 transition hover:bg-white/70 [&::-webkit-details-marker]:hidden">
                                    <span><span class="block text-sm font-semibold text-[#342319]">Start this quiz</span><span class="mt-0.5 block text-xs text-[#786A5D]">{{ $quiz->questions->count() }} questions · {{ max(0, $quiz->attempt_limit - $stats['attempts']) }} {{ $quiz->attempt_limit - $stats['attempts'] === 1 ? 'attempt' : 'attempts' }} remaining</span></span>
                                    <span aria-hidden="true" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#f2e2b8] text-[#75500f] transition group-open:rotate-180">⌄</span>
                                </summary>
                                <div class="border-t border-[#e5d8bf] p-4 sm:p-5">
                            <form action="{{ route('reader.quizzes.submit', $quiz) }}" method="POST" class="space-y-4">
                                @csrf
                                @foreach($quiz->questions->sortBy('sort_order') as $question)
                                    <fieldset class="rounded-2xl border border-[#e3d7c2] bg-[#f7f0e3] p-4 sm:p-5">
                                        <legend class="sr-only">Question {{ $loop->iteration }}</legend>
                                        <div class="flex items-start gap-3">
                                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#e9d8ae] text-xs font-bold text-[#5b4116]">{{ $loop->iteration }}</span>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-semibold leading-6 text-[#1B0D05]" data-no-translate>{{ $question->prompt }}</p>
                                                <p class="mt-1 text-xs text-[#786A5D]">{{ $question->points }} {{ (int) $question->points === 1 ? 'point' : 'points' }}</p>
                                                @if(in_array($question->question_type, ['one_word', 'short_answer', 'written_response'], true))
                                                    @if($question->question_type === 'one_word')
                                                        <input name="answers[{{ $question->id }}]" value="{{ old('answers.'.$question->id) }}" maxlength="100" required placeholder="Enter one word" aria-label="Your answer" class="mt-3 w-full rounded-xl border border-[#d8c9ad] bg-[#fffdf8] px-4 py-3 text-sm focus:border-[#a77920] focus:ring-[#a77920]">
                                                    @elseif($question->question_type === 'short_answer')
                                                        <textarea name="answers[{{ $question->id }}]" maxlength="1000" rows="2" required placeholder="Write a short answer" aria-label="Your answer" class="mt-3 w-full rounded-xl border border-[#d8c9ad] bg-[#fffdf8] px-4 py-3 text-sm focus:border-[#a77920] focus:ring-[#a77920]">{{ old('answers.'.$question->id) }}</textarea>
                                                        <p class="mt-1.5 text-xs text-[#786A5D]">Your answer is checked without regard to case or punctuation.</p>
                                                    @else
                                                        <textarea name="answers[{{ $question->id }}]" maxlength="5000" rows="4" required placeholder="Write your explanation" aria-label="Your answer" class="mt-3 w-full rounded-xl border border-[#d8c9ad] bg-[#fffdf8] px-4 py-3 text-sm focus:border-[#a77920] focus:ring-[#a77920]">{{ old('answers.'.$question->id) }}</textarea>
                                                        <p class="mt-1.5 text-xs text-[#786A5D]">An administrator will review this response before your score is finalized.</p>
                                                    @endif
                                                @else
                                                    <p class="mt-2 text-xs text-[#786A5D]">{{ $question->question_type === 'multiple_choice' ? 'Choose all answers that apply.' : 'Choose one answer.' }}</p>
                                                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                                        @foreach($question->answers as $answer)
                                                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#e2d5bd] bg-[#fffdf8] px-3 py-3 text-sm leading-5 text-[#3d3025] transition hover:border-[#bd9a59] hover:bg-white has-[:checked]:border-[#a77920] has-[:checked]:bg-[#fbf4e2]">
                                                                @if(in_array($question->question_type, ['single_choice', 'true_false'], true))
                                                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}" @checked((string) old('answers.'.$question->id) === (string) $answer->id) required class="mt-0.5 border-[#9b8c77] text-[#8b641c] focus:ring-[#a77920]">
                                                                @else
                                                                    <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $answer->id }}" @checked(in_array($answer->id, (array) old('answers.'.$question->id, []))) class="mt-0.5 rounded border-[#9b8c77] text-[#8b641c] focus:ring-[#a77920]">
                                                                @endif
                                                                <span data-no-translate>{{ $answer->body }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </fieldset>
                                @endforeach

                                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#e5d8bf] pt-4">
                                    <p class="text-xs text-[#786A5D]">Attempts used: {{ $stats['attempts'] }} of {{ $quiz->attempt_limit }}</p>
                                    <button class="inline-flex items-center gap-2 rounded-full bg-[#1B0D05] px-5 py-2.5 text-sm font-semibold text-[#FBF6EA] transition hover:bg-[#432919] focus:outline-none focus:ring-2 focus:ring-[#B98A2C] focus:ring-offset-2">Submit quiz <span aria-hidden="true">→</span></button>
                                </div>
                            </form>
                                </div>
                            </details>
                            @else
                                <p class="mt-5 rounded-xl bg-[#eee5d5] px-4 py-3 text-sm font-medium text-[#73685a]">You’ve used all {{ $quiz->attempt_limit }} attempts for this quiz.</p>
                            @endif
                        </div>

                        <aside class="border-t border-[#e5d8bf] bg-[#f5eddf] p-5 lg:border-l lg:border-t-0 sm:p-6" aria-label="Quiz performance">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-[#A77920]">Reader performance</p>
                            <p class="mt-1 text-xs text-[#786A5D]">Shared results for this quiz</p>
                            <dl class="mt-4 grid grid-cols-2 gap-2 lg:grid-cols-1">
                                <div class="rounded-xl border border-[#e3d7c2] bg-[#FBF6EA] p-3"><dt class="text-[10px] font-semibold uppercase tracking-wide text-[#897a67]">Readers</dt><dd class="mt-0.5 text-lg font-semibold text-[#1B0D05]">{{ $quiz->readers_count }}</dd></div>
                                <div class="rounded-xl border border-[#e3d7c2] bg-[#FBF6EA] p-3"><dt class="text-[10px] font-semibold uppercase tracking-wide text-[#897a67]">Attempts</dt><dd class="mt-0.5 text-lg font-semibold text-[#1B0D05]">{{ $quiz->attempts_count }}</dd></div>
                                <div class="rounded-xl border border-[#e3d7c2] bg-[#FBF6EA] p-3"><dt class="text-[10px] font-semibold uppercase tracking-wide text-[#897a67]">Average score</dt><dd class="mt-0.5 text-lg font-semibold text-[#1B0D05]">{{ $quiz->average_score !== null ? number_format((float) $quiz->average_score, 1).'%' : '—' }}</dd></div>
                                <div class="rounded-xl border border-[#e3d7c2] bg-[#FBF6EA] p-3"><dt class="text-[10px] font-semibold uppercase tracking-wide text-[#897a67]">Pass rate</dt><dd class="mt-0.5 text-lg font-semibold text-[#1B0D05]">{{ $quiz->pass_rate !== null ? number_format((float) $quiz->pass_rate, 1).'%' : '—' }}</dd></div>
                            </dl>
                            <div class="mt-3 border-t border-[#e1d3ba] pt-3 text-xs leading-5 text-[#6f6253]">
                                <p>Your best: <strong class="text-[#342319]">{{ $stats['best_score'] }}%</strong></p>
                                <p>Highest reader score: <strong class="text-[#342319]">{{ $quiz->best_score !== null ? (int) $quiz->best_score.'%' : '—' }}</strong></p>
                                <p>Passed attempts: <strong class="text-[#342319]">{{ $quiz->passed_attempts_count }}</strong></p>
                                @if($stats['pending_review'] > 0)<p class="mt-2 text-[#705311]">{{ $stats['pending_review'] }} response{{ $stats['pending_review'] === 1 ? '' : 's' }} awaiting review</p>@endif
                            </div>
                        </aside>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-[#cbb992] bg-[#FBF6EA] p-6 text-center">
                    <p class="font-serif text-xl text-[#3a281a]">No quiz is available yet</p>
                    <p class="mt-1 text-sm text-[#786A5D]">Check back later for a chance to test what you’ve learned.</p>
                </div>
            @endforelse
        </section>

        <section id="reading-tools" class="scroll-mt-20 mt-5 grid gap-5 lg:grid-cols-[1.15fr_0.85fr]">
            <article class="rounded-[24px] border border-[#dfcfad] bg-[#FBF6EA] p-5 shadow-sm sm:p-6">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">Keep your place</p>
                <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">Reading progress</h2>
                <p class="mt-1 text-sm text-[#786A5D]">Log the page you reached so your reading goals stay up to date.</p>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-[#f5eddf] p-3"><p class="text-[10px] font-semibold uppercase tracking-wide text-[#897a67]">Last page</p><p class="mt-0.5 text-xl font-semibold text-[#1B0D05]">{{ number_format($progress->last_page_read) }}{{ $book->page_count ? ' / '.number_format($book->page_count) : '' }}</p></div>
                    <div class="rounded-xl bg-[#f5eddf] p-3"><p class="text-[10px] font-semibold uppercase tracking-wide text-[#897a67]">Pages tracked</p><p class="mt-0.5 text-xl font-semibold text-[#1B0D05]">{{ number_format($progress->pages_read_total) }}</p></div>
                </div>
                @if($progress->last_opened_at)<p class="mt-3 text-xs text-[#786A5D]">Last opened {{ \Illuminate\Support\Carbon::parse($progress->last_opened_at)->format('M d, Y · H:i') }}</p>@endif
                @if($progress->last_opened_at && (!$progress->last_progress_at || \Illuminate\Support\Carbon::parse($progress->last_progress_at)->lt(\Illuminate\Support\Carbon::parse($progress->last_opened_at))))
                    <p class="mt-3 rounded-xl border border-[#e7c5b6] bg-[#fff7f2] px-3 py-2 text-xs text-[#7a2e22]">You opened this book but have not logged any new pages yet.</p>
                @endif

                <form action="{{ route('reader.books.pages.track', $book->slug) }}" method="POST" class="mt-4 flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="min-w-[10rem] flex-1 sm:flex-none">
                        <label for="current_page" class="mb-1.5 block text-xs font-semibold text-[#5e544d]">Current page reached</label>
                        <input id="current_page" type="number" name="current_page" min="1" @if($book->page_count) max="{{ $book->page_count }}" @endif value="{{ $book->page_count ? min(max(1, (int) $progress->last_page_read + 1), (int) $book->page_count) : max(1, (int) $progress->last_page_read + 1) }}" class="w-full rounded-xl border border-[#d8c9ad] bg-[#fffdf8] px-4 py-2.5 text-sm focus:border-[#a77920] focus:ring-[#a77920]" required>
                    </div>
                    <button class="rounded-full bg-[#1B0D05] px-5 py-2.5 text-sm font-semibold text-[#FBF6EA] transition hover:bg-[#432919]">Save progress</button>
                </form>

                <div class="mt-5 border-t border-[#e5d8bf] pt-4">
                    <h3 class="text-sm font-semibold text-[#342319]">Goals for this book</h3>
                    <div class="mt-3 space-y-2">
                        @forelse($pageGoals as $goal)
                            @php($goalPercent = $goal->target_value > 0 ? min(100, (int) round(($goal->current_value / $goal->target_value) * 100)) : 0)
                            <div class="rounded-xl bg-[#f5eddf] p-3">
                                <div class="flex items-center justify-between gap-2"><p class="text-sm font-medium text-[#342319]">{{ $goal->title }}</p><span class="text-[10px] font-semibold uppercase tracking-wide text-[#786A5D]">{{ str_replace('_', ' ', $goal->status) }}</span></div>
                                <p class="mt-1 text-xs text-[#786A5D]">{{ $goal->current_value }} of {{ $goal->target_value }} pages</p>
                                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-[#e3d7c2]" role="progressbar" aria-label="{{ $goal->title }} progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $goalPercent }}"><div class="h-full rounded-full bg-[#c18b25]" style="width: {{ $goalPercent }}%"></div></div>
                            </div>
                        @empty
                            <p class="rounded-xl bg-[#f5eddf] p-3 text-xs leading-5 text-[#786A5D]">No page goal is linked to this title. <a href="{{ route('reader.goals') }}" class="font-semibold text-[#5c3b08] underline underline-offset-2">Create a goal</a>.</p>
                        @endforelse
                    </div>
                </div>
            </article>

            <article class="rounded-[24px] border border-[#dfcfad] bg-[#FBF6EA] p-5 shadow-sm sm:p-6">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">Your collection</p>
                <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">Reading shelf</h2>
                <p class="mt-1 text-sm text-[#786A5D]">Save this book and keep its status current.</p>
                <div class="mt-4 rounded-xl border border-[#e5d8bf] bg-[#f5eddf] p-4">
                    <p class="text-xs text-[#786A5D]">Current status</p>
                    <p class="mt-1 font-semibold text-[#342319]">{{ $shelf ? ucfirst(str_replace('_', ' ', $shelf->status)) : 'Not on your shelf yet' }}</p>
                </div>
                <form action="{{ route('reader.books.shelf.update', $book->slug) }}" method="POST" class="mt-4 space-y-3">
                    @csrf
                    <label for="shelf_status" class="block text-xs font-semibold text-[#5e544d]">Reading status</label>
                    <select id="shelf_status" name="status" class="w-full rounded-xl border border-[#d8c9ad] bg-[#fffdf8] px-3 py-2.5 text-sm focus:border-[#a77920] focus:ring-[#a77920]">
                        @foreach (['want_to_read' => 'Want to read', 'currently_reading' => 'Currently reading', 'completed' => 'Completed', 'paused' => 'Paused', 'abandoned' => 'Abandoned'] as $value => $label)<option value="{{ $value }}" @selected($shelf?->status === $value)>{{ $label }}</option>@endforeach
                    </select>
                    <button class="w-full rounded-full bg-[#1B0D05] px-4 py-2.5 text-sm font-semibold text-[#FBF6EA] transition hover:bg-[#432919]">{{ $shelf ? 'Update shelf' : 'Add to my shelf' }}</button>
                </form>
                @if ($shelf)
                    <form action="{{ route('reader.books.shelf.destroy', $book->slug) }}" method="POST" class="mt-2">@csrf @method('DELETE')<button class="w-full rounded-full border border-[#d8c9ad] px-4 py-2 text-xs font-medium text-[#786A5D] transition hover:border-[#c17b6f] hover:text-[#7a2e22]">Remove from shelf</button></form>
                @endif
            </article>
        </section>

        <section class="mt-5 rounded-[24px] border border-[#dfcfad] bg-[#FBF6EA] p-5 shadow-sm sm:p-6" aria-labelledby="bookmarks-title">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div><p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">Save a passage</p><h2 id="bookmarks-title" class="mt-1 font-serif text-2xl text-[#1B0D05]">Page bookmarks</h2></div>
                <p class="text-xs text-[#786A5D]">Keep a note of pages you want to revisit.</p>
            </div>
            <form action="{{ route('reader.books.bookmarks.store', $book->slug) }}" method="POST" class="mt-4 grid gap-3 sm:grid-cols-[9rem_minmax(0,1fr)_auto]">
                @csrf
                <label class="sr-only" for="bookmark_page">Page number</label>
                <input id="bookmark_page" type="number" name="page_number" min="1" @if($book->page_count) max="{{ $book->page_count }}" @endif value="{{ max(1, (int) $progress->last_page_read) }}" required class="rounded-xl border border-[#d8c9ad] bg-[#fffdf8] px-3 py-2.5 text-sm focus:border-[#a77920] focus:ring-[#a77920]" placeholder="Page">
                <label class="sr-only" for="bookmark_label">Bookmark note (optional)</label>
                <input id="bookmark_label" type="text" name="label" maxlength="255" placeholder="Optional note, e.g. key passage" class="rounded-xl border border-[#d8c9ad] bg-[#fffdf8] px-3 py-2.5 text-sm focus:border-[#a77920] focus:ring-[#a77920]">
                <button class="rounded-full bg-[#1B0D05] px-5 py-2.5 text-sm font-semibold text-[#FBF6EA] transition hover:bg-[#432919]">Save bookmark</button>
            </form>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @forelse ($bookmarks as $bookmark)
                    <article class="rounded-xl border border-[#e5d8bf] bg-[#f5eddf] p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div><a href="{{ route('reader.books.content', $book->slug) }}#page={{ $bookmark->page_number }}" class="font-semibold text-[#3a281a] underline decoration-[#bd9a59] underline-offset-4">Page {{ $bookmark->page_number }}</a><p class="mt-1 text-xs text-[#786A5D]">{{ $bookmark->label ?: 'Saved page' }}</p></div>
                            <form action="{{ route('reader.bookmarks.destroy', $bookmark) }}" method="POST">@csrf @method('DELETE')<button class="rounded-full border border-[#dec9b7] px-3 py-1.5 text-xs text-[#7a2e22] hover:bg-white">Remove</button></form>
                        </div>
                        <details class="mt-3 border-t border-[#e3d7c2] pt-3">
                            <summary class="cursor-pointer text-xs font-semibold text-[#6f6253]">Edit bookmark</summary>
                            <form action="{{ route('reader.bookmarks.update', $bookmark) }}" method="POST" class="mt-3 grid gap-2 sm:grid-cols-[7rem_minmax(0,1fr)_auto]">
                                @csrf @method('PATCH')
                                <label class="sr-only" for="bookmark_page_{{ $bookmark->id }}">Updated page</label>
                                <input id="bookmark_page_{{ $bookmark->id }}" type="number" name="page_number" min="1" @if($book->page_count) max="{{ $book->page_count }}" @endif value="{{ $bookmark->page_number }}" required class="rounded-lg border border-[#d8c9ad] bg-[#fffdf8] px-3 py-2 text-xs">
                                <label class="sr-only" for="bookmark_label_{{ $bookmark->id }}">Bookmark note</label>
                                <input id="bookmark_label_{{ $bookmark->id }}" type="text" name="label" maxlength="255" value="{{ $bookmark->label }}" placeholder="Add a note" class="rounded-lg border border-[#d8c9ad] bg-[#fffdf8] px-3 py-2 text-xs">
                                <button class="rounded-full border border-[#cbb992] px-3 py-2 text-xs font-semibold hover:bg-white">Update</button>
                            </form>
                        </details>
                    </article>
                @empty
                    <p class="rounded-xl bg-[#f5eddf] p-4 text-sm text-[#786A5D] md:col-span-2">You haven’t saved any pages yet. Add one above to find important passages quickly.</p>
                @endforelse
            </div>
        </section>

        <section id="book-review" class="scroll-mt-20 mt-5 rounded-[24px] border border-[#dfcfad] bg-[#FBF6EA] p-5 shadow-sm sm:p-6" aria-labelledby="review-title">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div><p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">Share with readers</p><h2 id="review-title" class="mt-1 font-serif text-2xl text-[#1B0D05]">{{ $myReview ? 'Your review' : 'What did you think?' }}</h2><p class="mt-1 text-sm text-[#786A5D]">Your review will appear after it has been approved.</p></div>
                @if ($myReview)<span class="rounded-full border border-[#dfcfad] bg-[#f5eddf] px-3 py-1.5 text-xs font-medium text-[#5e544d]">{{ ucfirst($myReview->status) }} · {{ $myReview->rating }}/5</span>@endif
            </div>
            @php($reviewAction = $myReview ? route('reader.reviews.update', $myReview) : route('reader.books.reviews.store', $book->slug))
            <form action="{{ $reviewAction }}" method="POST" class="mt-5 grid gap-3 sm:grid-cols-2">
                @csrf @if ($myReview) @method('PATCH') @endif
                <div>
                    <label for="review_rating" class="mb-1.5 block text-xs font-semibold text-[#5e544d]">Your rating</label>
                    <select id="review_rating" name="rating" required class="w-full rounded-xl border border-[#d8c9ad] bg-[#fffdf8] px-3 py-2.5 text-sm focus:border-[#a77920] focus:ring-[#a77920]">
                        @foreach (range(5, 1) as $rating)<option value="{{ $rating }}" @selected((int) ($myReview?->rating ?? 5) === $rating)>{{ $rating }} / 5 {{ $rating === 1 ? 'star' : 'stars' }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="review_title" class="mb-1.5 block text-xs font-semibold text-[#5e544d]">Review title <span class="font-normal text-[#897a67]">(optional)</span></label>
                    <input id="review_title" type="text" name="title" maxlength="255" value="{{ $myReview?->title }}" placeholder="Give your review a title" class="w-full rounded-xl border border-[#d8c9ad] bg-[#fffdf8] px-3 py-2.5 text-sm focus:border-[#a77920] focus:ring-[#a77920]">
                </div>
                <div class="sm:col-span-2">
                    <label for="review_body" class="mb-1.5 block text-xs font-semibold text-[#5e544d]">Your thoughts</label>
                    <textarea id="review_body" name="body" rows="4" minlength="10" maxlength="5000" required placeholder="What stood out to you? What might another reader enjoy?" class="w-full rounded-xl border border-[#d8c9ad] bg-[#fffdf8] px-3 py-3 text-sm leading-6 focus:border-[#a77920] focus:ring-[#a77920]">{{ $myReview?->body }}</textarea>
                    <p class="mt-1 text-xs text-[#897a67]">Write at least 10 characters. Please avoid sharing spoilers without warning.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 sm:col-span-2">
                    <button class="rounded-full bg-[#1B0D05] px-5 py-2.5 text-sm font-semibold text-[#FBF6EA] transition hover:bg-[#432919]">{{ $myReview ? 'Update review' : 'Submit review' }}</button>
                </div>
            </form>
            @if ($myReview)
                <form action="{{ route('reader.reviews.destroy', $myReview) }}" method="POST" class="mt-2">@csrf @method('DELETE')<button class="rounded-full px-4 py-2.5 text-sm text-[#7a2e22] underline decoration-[#d8b2a6] underline-offset-4 hover:bg-[#fff7f2]">Delete review</button></form>
            @endif
        </section>
    </main>
</div>
</body>
</html>
