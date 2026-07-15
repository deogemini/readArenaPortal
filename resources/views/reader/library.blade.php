<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reader Library | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/90">
        <div class="mx-auto max-w-7xl px-6 py-4 lg:px-8">
            <a href="/reader/dashboard" class="font-serif text-2xl text-[#1B0D05]">Your library</a>
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach($books as $book)
                <article class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-5 shadow-sm">
                    <img src="{{ $book->cover_image ?? 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $book->title }}" class="h-48 w-full rounded-[18px] object-cover">
                    <div class="mt-4">
                        <p class="text-xs uppercase tracking-[0.3em] text-[#B98A2C]">{{ $book->genres->first()?->name ?? 'Classic' }}</p>
                        <h2 class="mt-2 font-serif text-2xl text-[#1B0D05]">{{ $book->title }}</h2>
                        <p class="mt-2 text-sm text-[#786A5D]">{{ $book->authors->first()?->name ?? 'Unknown author' }}</p>
                        <a href="/reader/books/{{ $book->slug }}" class="mt-4 inline-flex rounded-full border border-[#d8c9ad] px-4 py-2 text-sm font-semibold text-[#1B0D05]">Open book</a>
                    </div>
                </article>
            @endforeach
        </div>
    </main>
</div>
</body>
</html>
