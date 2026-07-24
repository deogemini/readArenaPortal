<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Shows | ReadArena</title>
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
                <a href="{{ route('reader.dashboard') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Dashboard</a>
                <a href="{{ route('reader.library') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Library</a>
                <a href="{{ route('reader.goals') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Goals</a>
                <a href="{{ route('reader.lessons') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Lessons</a>
                <a href="{{ route('reader.shows') }}" class="rounded-full bg-[#1B0D05] px-4 py-2 text-sm text-[#FBF6EA]">Shows</a>
                <a href="{{ route('reader.duels') }}" class="rounded-full border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2 text-sm">Duels</a>
                <a href="/logout" class="rounded-full bg-[#1B0D05] px-4 py-2 text-sm text-[#FBF6EA]" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                <form id="logout-form" action="/logout" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-8 shadow-sm">
            <h1 class="font-serif text-3xl text-[#1B0D05]">Scheduled sessions</h1>
            <div class="mt-6 space-y-4">
                @forelse($shows as $show)
                    <div class="rounded-[20px] border border-[#d8c9ad] bg-[#F4EBD8] p-5">
                        <h2 class="font-semibold text-[#1B0D05]">{{ $show->title }}</h2>
                        <p class="mt-2 text-sm text-[#5e544d]">{{ date('M d, Y', strtotime($show->start_at)) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-[#5e544d]">No live shows are scheduled just yet.</p>
                @endforelse
            </div>
        </div>
    </main>
</div>
</body>
</html>