<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duels | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(216,168,62,0.18),_transparent_40%)]">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/90">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-[#B98A2C]">Reader lounge</p>
                <h1 class="font-serif text-2xl text-[#1B0D05]">Book duels</h1>
            </div>
            <nav class="flex flex-wrap gap-2 text-sm">
                <a href="{{ route('reader.dashboard') }}" class="rounded-full border border-[#d8c9ad] px-4 py-2">Dashboard</a>
                <a href="{{ route('reader.library') }}" class="rounded-full border border-[#d8c9ad] px-4 py-2">Library</a>
                <a href="{{ route('reader.duels') }}" class="rounded-full bg-[#1B0D05] px-4 py-2 text-[#FBF6EA]">Duels</a>
            </nav>
        </div>
    </header>
    <main class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-[#d8c9ad] bg-[#FBF6EA] px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-[#c17b6f] bg-[#FBF6EA] px-4 py-3 text-sm text-[#7a2e22]">
                <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm sm:p-8">
            <p class="text-xs uppercase tracking-[0.3em] text-[#B98A2C]">Verified reader matchmaking</p>
            <h2 class="mt-2 font-serif text-3xl text-[#1B0D05]">Challenge someone who has read the same book</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-[#786A5D]">Pass a published quiz for a book to unlock challenges for that title. Your invitation stays pending until the other reader accepts or rejects it.</p>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                @forelse ($verifiedBooks as $book)
                    @php($opponents = $opponentsByBook->get($book->id, collect()))
                    <article class="rounded-[20px] border border-[#d8c9ad] bg-[#F4EBD8] p-5">
                        <p class="text-xs uppercase tracking-[0.2em] text-[#B98A2C]">Verified book</p>
                        <h3 class="mt-1 font-serif text-2xl text-[#1B0D05]">{{ $book->title }}</h3>
                        @if ($opponents->isNotEmpty())
                            <form action="{{ route('reader.duels.store') }}" method="POST" class="mt-4 flex flex-col gap-3 sm:flex-row">
                                @csrf
                                <input type="hidden" name="book_id" value="{{ $book->id }}">
                                <select name="opponent_id" required class="min-w-0 flex-1 rounded-xl border border-[#d8c9ad] bg-[#FBF6EA] px-3 py-2 text-sm">
                                    <option value="">Choose a verified reader</option>
                                    @foreach ($opponents as $opponent)<option value="{{ $opponent->id }}">{{ $opponent->name }}</option>@endforeach
                                </select>
                                <button class="rounded-full bg-[#1B0D05] px-5 py-2 text-sm font-semibold text-[#FBF6EA]">Send invite</button>
                            </form>
                        @else
                            <p class="mt-4 text-sm text-[#786A5D]">No other verified readers for this book yet.</p>
                        @endif
                    </article>
                @empty
                    <div class="rounded-[20px] border border-dashed border-[#d8c9ad] p-5 text-sm text-[#786A5D] md:col-span-2">
                        Pass a published book quiz to unlock your first duel invitation. Your verified books will appear here.
                        <a href="{{ route('reader.library') }}" class="ml-1 font-semibold text-[#1B0D05]">Browse the library</a>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm sm:p-8">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div><p class="text-xs uppercase tracking-[0.3em] text-[#B98A2C]">Your match history</p><h2 class="mt-2 font-serif text-3xl text-[#1B0D05]">Invitations and duels</h2></div>
                <span class="text-sm text-[#786A5D]">{{ $duels->total() }} total</span>
            </div>
            <div class="mt-6 space-y-3">
                @forelse ($duels as $duel)
                    @php($isOpponent = (int) $duel->opponent_id === (int) auth()->id())
                    @php($otherReader = $isOpponent ? $duel->challenger : $duel->opponent)
                    <article class="flex flex-col gap-4 rounded-[18px] border border-[#d8c9ad] bg-[#F4EBD8] p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-semibold text-[#1B0D05]">{{ $duel->book?->title ?? 'Book removed' }}</h3>
                            <p class="mt-1 text-sm text-[#786A5D]">{{ $isOpponent ? 'Challenge from' : 'Challenge to' }} {{ $otherReader?->name ?? 'Reader' }} · {{ $duel->created_at->format('M j, Y') }}</p>
                            <span class="mt-2 inline-flex rounded-full border border-[#d8c9ad] px-3 py-1 text-xs uppercase tracking-wide">{{ $duel->status }}</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if ($isOpponent && $duel->status === 'pending')
                                @foreach (['accept' => 'Accept', 'reject' => 'Decline'] as $action => $label)
                                    <form action="{{ route('reader.duels.respond', $duel) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="action" value="{{ $action }}"><button class="rounded-full {{ $action === 'accept' ? 'bg-[#1B0D05] text-[#FBF6EA]' : 'border border-[#d8c9ad]' }} px-4 py-2 text-sm">{{ $label }}</button></form>
                                @endforeach
                            @elseif (!$isOpponent && in_array($duel->status, ['pending', 'accepted', 'scheduled'], true))
                                <form action="{{ route('reader.duels.cancel', $duel) }}" method="POST" onsubmit="return confirm('Cancel this duel invitation?')">@csrf @method('PATCH')<button class="rounded-full border border-[#c17b6f] px-4 py-2 text-sm text-[#7a2e22]">Cancel invite</button></form>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="rounded-[18px] border border-dashed border-[#d8c9ad] p-5 text-sm text-[#786A5D]">No invitations yet. Challenge a verified reader to get started.</p>
                @endforelse
            </div>
            <div class="mt-5">{{ $duels->links() }}</div>
        </section>
    </main>
</div>
</body>
</html>
