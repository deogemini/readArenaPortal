<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $book->title }} | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
            <a href="/library" class="font-serif text-2xl text-[#1B0D05]">ReadArena</a>
            <a href="/register" class="rounded-full bg-[#1B0D05] px-5 py-2 text-sm font-semibold text-[#FBF6EA]">Join the duel</a>
        </div>
    </header>
    <main class="mx-auto grid max-w-7xl gap-8 px-6 py-12 lg:grid-cols-[0.8fr_1.2fr] lg:px-8">
        <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm">
            <img src="{{ $book->cover_image ?? 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $book->title }}" class="h-80 w-full rounded-[20px] object-cover">
            <div class="mt-6">
                <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">{{ $book->genres->first()?->name ?? 'Classic' }}</p>
                <h1 class="mt-2 font-serif text-3xl text-[#1B0D05]">{{ $book->title }}</h1>
                <p class="mt-2 text-sm text-[#786A5D]">by {{ $book->authors->first()?->name ?? 'Unknown author' }}</p>
                <p class="mt-2 text-sm text-[#786A5D]">{{ $reviews->total() ? number_format($reviewAverage, 1).' / 5 from '.$reviews->total().' reader reviews' : 'No reader reviews yet' }}</p>
                <div class="mt-5 flex flex-wrap gap-3 text-sm">
                    <span class="rounded-full border border-[#d8c9ad] px-3 py-1">{{ $book->publication_year }}</span>
                    <span class="rounded-full border border-[#d8c9ad] px-3 py-1">{{ $book->page_count }} pages</span>
                    <span class="rounded-full border border-[#d8c9ad] px-3 py-1">{{ strtoupper($book->language) }}</span>
                </div>
            </div>
        </div>
        <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm">
            <h2 class="font-serif text-3xl text-[#1B0D05]">About the work</h2>
            <p class="mt-4 text-base leading-8 text-[#5e544d]">{{ $book->description }}</p>
            <div class="mt-8 rounded-[22px] border border-[#d8c9ad] bg-[#F4EBD8] p-5">
                <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Reading journey</p>
                <ul class="mt-4 space-y-3 text-sm text-[#5e544d]">
                    <li>• Start reading inside the app</li>
                    <li>• Complete the comprehension quiz</li>
                    <li>• Enter a verified duel against another reader</li>
                </ul>
            </div>
        </div>
    </main>
    <section class="mx-auto max-w-7xl px-6 pb-12 lg:px-8">
        <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm">
            <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Reader voices</p>
            <h2 class="mt-2 font-serif text-3xl text-[#1B0D05]">Recommendations for {{ $book->title }}</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @forelse ($recommendations as $recommendation)
                    <article class="rounded-[20px] border border-[#d8c9ad] bg-[#F4EBD8] p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold text-[#1B0D05]">{{ $recommendation->user?->name ?? 'ReadArena reader' }}</p>
                            <span class="rounded-full border border-[#d8c9ad] px-3 py-1 text-xs">{{ $recommendation->rating }} / 5</span>
                        </div>
                        <p class="mt-3 text-sm leading-7 text-[#5e544d]">{{ $recommendation->message }}</p>
                    </article>
                @empty
                    <p class="text-sm text-[#786A5D]">No public recommendations for this book yet.</p>
                @endforelse
            </div>
        </div>
    </section>
    <section class="mx-auto max-w-7xl px-6 pb-12 lg:px-8">
        <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm">
            <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Moderated reviews</p>
            <h2 class="mt-2 font-serif text-3xl text-[#1B0D05]">What readers thought</h2>
            <div class="mt-5 space-y-4">
                @forelse ($reviews as $review)
                    <article class="rounded-[20px] border border-[#d8c9ad] bg-[#F4EBD8] p-5">
                        <div class="flex flex-wrap items-center justify-between gap-2"><h3 class="font-semibold text-[#1B0D05]">{{ $review->title ?: 'Reader review' }}</h3><span class="rounded-full border border-[#d8c9ad] px-3 py-1 text-xs">{{ $review->rating }} / 5</span></div>
                        <p class="mt-2 text-sm leading-7 text-[#5e544d]">{{ $review->body }}</p>
                        <p class="mt-3 text-xs text-[#786A5D]">{{ $review->user?->name ?? 'ReadArena reader' }}</p>
                    </article>
                @empty
                    <p class="text-sm text-[#786A5D]">No approved reviews for this book yet.</p>
                @endforelse
            </div>
            <div class="mt-4">{{ $reviews->links() }}</div>
        </div>
    </section>
</div>
</body>
</html>
