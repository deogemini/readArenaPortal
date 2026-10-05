<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
            <a href="{{ route('home') }}" class="font-serif text-2xl text-[#1B0D05]">ReadArena</a>
            <nav class="flex items-center gap-5 text-sm text-[#786A5D]">
                <a href="{{ route('library') }}" class="hover:text-[#1B0D05]">Library</a>
                <a href="{{ route('pro-arena') }}" class="hover:text-[#1B0D05]">Pro Arena</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-16 lg:px-8 lg:py-24">
        <section class="max-w-3xl">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#B98A2C]">About us</p>
            <h1 class="mt-4 font-serif text-5xl leading-tight text-[#1B0D05]">A place to read with purpose.</h1>
            <p class="mt-6 text-lg leading-8 text-[#5e544d]">
                ReadArena brings readers together around books, thoughtful discussion, and friendly competition.
                Readers can track their progress, test what they remember, share what they learned, and challenge
                other readers who have finished the same book.
            </p>
        </section>

        <section class="mt-12 grid gap-5 md:grid-cols-3">
            <article class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
                <h2 class="font-serif text-2xl text-[#1B0D05]">Read</h2>
                <p class="mt-3 text-sm leading-7 text-[#5e544d]">Explore the library, read at your own pace, and keep your place with progress tracking and bookmarks.</p>
            </article>
            <article class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
                <h2 class="font-serif text-2xl text-[#1B0D05]">Reflect</h2>
                <p class="mt-3 text-sm leading-7 text-[#5e544d]">Use quizzes, lessons, and recommendations to capture what each book gave you.</p>
            </article>
            <article class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
                <h2 class="font-serif text-2xl text-[#1B0D05]">Connect</h2>
                <p class="mt-3 text-sm leading-7 text-[#5e544d]">Meet other readers through book duels, live shows, and the community leaderboard.</p>
            </article>
        </section>

        <div class="mt-10 flex flex-wrap gap-3">
            <a href="{{ route('library') }}" class="rounded-full bg-[#1B0D05] px-6 py-3 text-sm font-semibold text-[#FBF6EA]">Explore the library</a>
            <a href="{{ route('home') }}" class="rounded-full border border-[#d8c9ad] bg-[#FBF6EA] px-6 py-3 text-sm font-semibold text-[#1B0D05]">Back home</a>
        </div>
    </main>
</div>
</body>
</html>
