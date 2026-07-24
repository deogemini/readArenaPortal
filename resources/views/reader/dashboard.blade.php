<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reader Dashboard | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(216,168,62,0.18),_transparent_40%)]">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-[#B98A2C]">Reader lounge</p>
                <h1 class="font-serif text-2xl text-[#1B0D05]">Welcome back, {{ auth()->user()->name }}</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <a href="{{ route('reader.dashboard') }}" class="rounded-full bg-[#1B0D05] px-4 py-2 text-sm text-[#FBF6EA]">Dashboard</a>
                <a href="{{ route('reader.library') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Library</a>
                <a href="{{ route('reader.goals') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Goals</a>
                <a href="{{ route('reader.lessons') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Lessons</a>
                <a href="{{ route('reader.shows') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Shows</a>
                <a href="{{ route('reader.duels') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Duels</a>
                <a href="/logout" class="rounded-full bg-[#1B0D05] px-4 py-2 text-sm text-[#FBF6EA]" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                <form id="logout-form" action="/logout" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('warning'))
            <div class="mb-6 rounded-xl border border-[#c17b6f] bg-[#FBF6EA] px-4 py-3 text-sm text-[#7a2e22]">
                {{ session('warning') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <section class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Continue reading</p>
                        <h2 class="mt-2 font-serif text-3xl text-[#1B0D05]">Tonight’s shelf</h2>
                    </div>
                    <a href="/reader/library" class="text-sm font-semibold text-[#1B0D05]">Browse all</a>
                </div>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @foreach($books as $book)
                        <div class="rounded-[22px] border border-[#d8c9ad] bg-[#F4EBD8] p-4">
                            <div class="flex gap-4">
                                <img src="{{ $book->cover_image ?? 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=300&q=80' }}" alt="{{ $book->title }}" class="h-24 w-20 rounded-[16px] object-cover">
                                <div class="flex-1">
                                    <p class="text-xs uppercase tracking-[0.3em] text-[#B98A2C]">{{ $book->genres->first()?->name ?? 'Classic' }}</p>
                                    <h3 class="mt-1 font-serif text-xl text-[#1B0D05]">{{ $book->title }}</h3>
                                    <p class="mt-1 text-sm text-[#786A5D]">{{ $book->authors->first()?->name ?? 'Unknown author' }}</p>
                                    <div class="mt-3 flex items-center gap-2 text-sm">
                                        <span class="rounded-full bg-[#1B0D05] px-3 py-1 text-[#FBF6EA]">Quiz ready</span>
                                        @if($duelsUnlocked)
                                            <span class="rounded-full border border-[#d8c9ad] px-3 py-1">Duel unlocked</span>
                                        @else
                                            <span class="rounded-full border border-[#d8c9ad] px-3 py-1">Duel locked</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('reader.books.show', $book->slug) }}" class="mt-3 inline-flex rounded-full border border-[#d8c9ad] px-3 py-1 text-xs font-semibold text-[#1B0D05]">Read & attempt quiz</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <aside class="space-y-6">
                <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Quiz progress</p>
                    <div class="mt-4 flex items-end justify-between">
                        <div>
                            <p class="text-xs text-[#786A5D]">Total points earned</p>
                            <p class="font-serif text-4xl text-[#1B0D05]">{{ $totalPoints }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-[#786A5D]">Attempts</p>
                            <p class="text-lg font-semibold text-[#1B0D05]">{{ $quizAttemptsCount }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-[#786A5D]">
                        Duels unlock after {{ $requiredQuizzesForDuels }} completed quizzes. You have completed {{ $completedQuizzesCount }}.
                    </p>
                </div>

                <div class="rounded-[28px] border border-[#d8c9ad] bg-[#1B0D05] p-6 text-[#F4EBD8]">
                    <p class="text-sm uppercase tracking-[0.3em] text-[#D8A83E]">Reading goal</p>
                    <h2 class="mt-2 font-serif text-2xl">{{ $goals->first()?->title ?? 'Annual reading challenge' }}</h2>
                    <p class="mt-4 text-sm leading-7 text-[#e5d7bf]">You’re pacing through the year with a steady streak and fresh momentum.</p>
                    <div class="mt-6 h-2 rounded-full bg-[#2B170D]">
                        <div class="h-2 w-2/3 rounded-full bg-[#D8A83E]"></div>
                    </div>
                </div>

                <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Upcoming shows</p>
                    <div class="mt-4 space-y-3">
                        @foreach($shows as $show)
                            <div class="rounded-[18px] border border-[#d8c9ad] bg-[#F4EBD8] p-3">
                                <h3 class="font-semibold text-[#1B0D05]">{{ $show->title }}</h3>
                                <p class="mt-1 text-sm text-[#786A5D]">{{ $show->start_at->format('M d, Y') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>

        <section class="mt-8 grid gap-6 lg:grid-cols-3">
            <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
                <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Recent lessons</p>
                <div class="mt-4 space-y-3">
                    @foreach($lessons as $lesson)
                        <div class="rounded-[18px] border border-[#d8c9ad] bg-[#F4EBD8] p-3">
                            <h3 class="font-semibold text-[#1B0D05]">{{ $lesson->title }}</h3>
                            <p class="mt-1 text-sm text-[#786A5D]">{{ $lesson->book->title ?? 'Book' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
                <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Recommendations</p>
                <div class="mt-4 space-y-3">
                    @foreach($recommendations as $recommendation)
                        <div class="rounded-[18px] border border-[#d8c9ad] bg-[#F4EBD8] p-3">
                            <h3 class="font-semibold text-[#1B0D05]">{{ $recommendation->book->title ?? 'Book' }}</h3>
                            <p class="mt-1 text-sm text-[#786A5D]">{{ $recommendation->message }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
                <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Weekly leaderboard</p>
                <div class="mt-4 space-y-3">
                    @foreach([['name'=>'Mina Rivera','points'=>'2,140'],['name'=>'Kai Brooks','points'=>'1,980'],['name'=>'Ayo Chen','points'=>'1,760']] as $player)
                        <div class="flex items-center justify-between rounded-[18px] border border-[#d8c9ad] bg-[#F4EBD8] p-3">
                            <span class="font-semibold text-[#1B0D05]">{{ $player['name'] }}</span>
                            <span class="text-sm text-[#786A5D]">{{ $player['points'] }} pts</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
