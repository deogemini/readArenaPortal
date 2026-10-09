<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reader Dashboard | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
@include('components.language-switcher')
<div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(216,168,62,0.18),_transparent_40%)]">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/90">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-[#B98A2C]">Reader lounge</p>
                <h1 class="font-serif text-2xl text-[#1B0D05]">Welcome back, {{ auth()->user()->name }}</h1>
                <p class="mt-1 text-sm text-[#786A5D]">{{ auth()->user()->email }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <a href="{{ route('reader.dashboard') }}" class="rounded-full bg-[#1B0D05] px-4 py-2 text-sm text-[#FBF6EA]">Dashboard</a>
                <a href="{{ route('reader.library') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Library</a>
                <a href="{{ route('reader.goals') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Goals</a>
                <a href="{{ route('reader.lessons') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Lessons</a>
                <a href="{{ route('reader.recommendations') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Recommendations</a>
                <a href="{{ route('reader.shows') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Shows</a>
                <a href="{{ route('reader.duels') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Duels</a>
                <a href="https://play.google.com/store/apps/details?id=tz.co.eportsolutions.readarena" target="_blank" rel="noopener noreferrer" class="rounded-full border border-[#B98A2C] px-4 py-2 text-sm font-semibold text-[#5c3b08]">Download Android app</a>
                <a href="/logout" class="rounded-full bg-[#1B0D05] px-4 py-2 text-sm text-[#FBF6EA]" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log out</a>
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
                        <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Your account</p>
                        <h2 class="mt-2 font-serif text-3xl text-[#1B0D05]">Continue reading</h2>
                    </div>
                    <a href="/reader/library" class="text-sm font-semibold text-[#1B0D05]">Browse all</a>
                </div>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @forelse($continueReading as $progress)
                        @php($book = $progress->book)
                        <div class="rounded-[22px] border border-[#d8c9ad] bg-[#F4EBD8] p-4">
                            <div class="flex gap-4">
                                <img src="{{ $book->cover_image ?? 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=300&q=80' }}" alt="{{ $book->title }}" class="h-24 w-20 rounded-[16px] object-cover">
                                <div class="flex-1">
                                    <h3 class="mt-1 font-serif text-xl text-[#1B0D05]">{{ $book->title }}</h3>
                                    <p class="mt-1 text-sm text-[#786A5D]">{{ $book->authors->first()?->name ?? 'Unknown author' }}</p>
                                    <p class="mt-2 text-xs text-[#786A5D]">Last page read: {{ number_format((int) $progress->last_page_read) }}{{ $book->page_count ? ' of '.number_format((int) $book->page_count) : '' }}</p>
                                    <a href="{{ route('reader.books.show', $book->slug) }}" class="mt-3 inline-flex rounded-full border border-[#d8c9ad] px-3 py-1 text-xs font-semibold text-[#1B0D05]">Continue book</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[22px] border border-dashed border-[#d8c9ad] p-5 text-sm text-[#786A5D] md:col-span-2">
                            You have no saved reading progress yet. Browse the library and open a book to see it here.
                            <a href="{{ route('reader.library') }}" class="mt-3 inline-flex rounded-full bg-[#1B0D05] px-4 py-2 font-semibold text-[#FBF6EA]">Browse the library</a>
                        </div>
                    @endforelse
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
                        You have verified {{ $verifiedBooksCount }} {{ \Illuminate\Support\Str::plural('book', $verifiedBooksCount) }} for book-specific duels.
                    </p>
                </div>

                <div class="rounded-[28px] border border-[#d8c9ad] bg-[#1B0D05] p-6 text-[#F4EBD8]">
                    @php($activeGoal = $goals->firstWhere('status', 'active'))
                    @php($goalPercent = $activeGoal && $activeGoal->target_value > 0 ? min(100, (int) floor(($activeGoal->current_value / $activeGoal->target_value) * 100)) : 0)
                    <p class="text-sm uppercase tracking-[0.3em] text-[#D8A83E]">Reading goal</p>
                    <h2 class="mt-2 font-serif text-2xl">{{ $activeGoal?->title ?? 'No active goal yet' }}</h2>
                    @if ($activeGoal)
                        <p class="mt-4 text-sm leading-7 text-[#e5d7bf]">{{ $activeGoal->current_value }} / {{ $activeGoal->target_value }} {{ $activeGoal->goal_type }} · {{ $goalPercent }}% complete</p>
                    @else
                        <p class="mt-4 text-sm leading-7 text-[#e5d7bf]">Create a reading goal and track your progress here.</p>
                    @endif
                    <div class="mt-6 h-2 rounded-full bg-[#2B170D]">
                        <div class="h-2 rounded-full bg-[#D8A83E]" style="width: {{ $goalPercent }}%"></div>
                    </div>
                    <a href="{{ route('reader.goals') }}" class="mt-4 inline-block text-sm font-semibold text-[#D8A83E]">Manage goals</a>
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
                    @forelse($leaderboard as $player)
                        <div class="flex items-center justify-between rounded-[18px] border border-[#d8c9ad] bg-[#F4EBD8] p-3">
                            <span class="font-semibold text-[#1B0D05]">#{{ $player['rank'] }} {{ $player['name'] }}</span>
                            <span class="text-sm text-[#786A5D]">{{ $player['points'] }} pts</span>
                        </div>
                    @empty
                        <p class="text-sm text-[#786A5D]">Pass a quiz to appear on this week’s board.</p>
                    @endforelse
                </div>
                <a href="{{ route('leaderboard') }}?period=weekly" class="mt-4 inline-block text-sm font-semibold text-[#1B0D05]">View leaderboard</a>
            </div>
        </section>

        <section class="mt-8 grid gap-6 lg:grid-cols-2">
            <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
                <div class="flex items-center justify-between gap-3">
                    <div><p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Your saved books</p><h2 class="mt-2 font-serif text-2xl text-[#1B0D05]">Reading shelf</h2></div>
                    <a href="{{ route('reader.library') }}" class="text-sm font-semibold text-[#1B0D05]">Open library</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($shelfBooks as $shelfBook)
                        <div class="flex items-center justify-between gap-3 rounded-[18px] border border-[#d8c9ad] bg-[#F4EBD8] p-3">
                            <div class="min-w-0">
                                <h3 class="truncate font-semibold text-[#1B0D05]">{{ $shelfBook->book?->title ?? 'Book' }}</h3>
                                <p class="mt-1 text-xs text-[#786A5D]">{{ $shelfBook->book?->authors->first()?->name ?? 'Unknown author' }}</p>
                            </div>
                            <span class="shrink-0 rounded-full border border-[#d8c9ad] px-3 py-1 text-xs">{{ str($shelfBook->status)->replace('_', ' ')->title() }}</span>
                        </div>
                    @empty
                        <p class="rounded-[18px] border border-dashed border-[#d8c9ad] p-4 text-sm text-[#786A5D]">Your reading shelf is empty. Add books from the library in the app or website.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
                <div class="flex items-center justify-between gap-3">
                    <div><p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Your competitions</p><h2 class="mt-2 font-serif text-2xl text-[#1B0D05]">Recent duels</h2></div>
                    <a href="{{ route('reader.duels') }}" class="text-sm font-semibold text-[#1B0D05]">View all</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($recentDuels as $duel)
                        @php($isOpponent = (int) $duel->opponent_id === (int) auth()->id())
                        <div class="rounded-[18px] border border-[#d8c9ad] bg-[#F4EBD8] p-3">
                            <h3 class="font-semibold text-[#1B0D05]">{{ $duel->book?->title ?? 'Book duel' }}</h3>
                            <p class="mt-1 text-sm text-[#786A5D]">{{ $isOpponent ? 'Challenge from ' : 'Challenge to ' }}{{ $isOpponent ? ($duel->challenger?->name ?? 'Reader') : ($duel->opponent?->name ?? 'Reader') }}</p>
                            <span class="mt-2 inline-flex rounded-full border border-[#d8c9ad] px-3 py-1 text-xs uppercase tracking-wide">{{ $duel->status }}</span>
                        </div>
                    @empty
                        <p class="rounded-[18px] border border-dashed border-[#d8c9ad] p-4 text-sm text-[#786A5D]">No duels yet. Your invitations and results will appear here.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
