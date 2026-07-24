<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reader Library | ReadArena</title>
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
                <a href="{{ route('reader.library') }}" class="rounded-full bg-[#1B0D05] px-4 py-2 text-sm text-[#FBF6EA]">Library</a>
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
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach($books as $book)
                <article class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-5 shadow-sm">
                    <img src="{{ $book->cover_image ?? 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $book->title }}" class="h-48 w-full rounded-[18px] object-cover">
                    <div class="mt-4">
                        <p class="text-xs uppercase tracking-[0.3em] text-[#B98A2C]">{{ $book->genres->first()?->name ?? 'Classic' }}</p>
                        <h2 class="mt-2 font-serif text-2xl text-[#1B0D05]">{{ $book->title }}</h2>
                        <p class="mt-2 text-sm text-[#786A5D]">{{ $book->authors->first()?->name ?? 'Unknown author' }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="/reader/books/{{ $book->slug }}" class="inline-flex rounded-full border border-[#d8c9ad] px-4 py-2 text-sm font-semibold text-[#1B0D05]">Open book</a>
                            @if($book->pdf_path)
                                <a href="{{ asset('storage/'.$book->pdf_path) }}" target="_blank" rel="noopener" class="inline-flex rounded-full bg-[#1B0D05] px-4 py-2 text-sm font-semibold text-[#FBF6EA]">Read PDF</a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </main>
</div>
</body>
</html>
