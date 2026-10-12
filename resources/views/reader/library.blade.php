<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('components.site-icons')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reader Library | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
@include('components.language-switcher')
<div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(216,168,62,0.18),_transparent_40%)]">
    @include('reader.partials.navigation')
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach($books as $book)
                <article class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-5 shadow-sm">
                    <img src="{{ $book->cover_image ?? 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $book->title }}" class="h-48 w-full rounded-[18px] object-cover">
                    <div class="mt-4">
                        <p class="text-xs uppercase tracking-[0.3em] text-[#B98A2C]">{{ $book->genres->first()?->name ?? 'Classic' }}</p>
                        <h2 class="mt-2 font-serif text-2xl text-[#1B0D05]" data-no-translate>{{ $book->title }}</h2>
                        <p class="mt-2 text-sm text-[#786A5D]" data-no-translate>{{ $book->authors->first()?->name ?? 'Unknown author' }}</p>
                        <p class="mt-2 text-xs text-[#786A5D]">{{ (int) $book->published_quizzes_count }} quizzes · {{ (int) $book->quiz_readers_count }} readers · {{ (int) $book->quiz_attempts_count }} attempts</p>
                        <p class="mt-1 text-xs text-[#786A5D]">Average score: {{ $book->quiz_average_score !== null ? number_format((float) $book->quiz_average_score, 1).'%' : '—' }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="/reader/books/{{ $book->slug }}" class="inline-flex rounded-full border border-[#d8c9ad] px-4 py-2 text-sm font-semibold text-[#1B0D05]">Open book</a>
                            @if($book->pdf_path)
                                <a href="{{ route('reader.books.content', $book->slug) }}" target="_blank" rel="noopener" class="inline-flex rounded-full bg-[#1B0D05] px-4 py-2 text-sm font-semibold text-[#FBF6EA]">Read PDF</a>
                            @endif
                        </div>
                        @php($shelf = $shelves->get($book->id))
                        <form action="{{ route('reader.books.shelf.update', $book->slug) }}" method="POST" class="mt-4 flex flex-wrap gap-2">@csrf
                            <select name="status" class="min-w-0 flex-1 rounded-xl border border-[#d8c9ad] bg-[#F4EBD8] px-3 py-2 text-sm" aria-label="Reading shelf status">
                                @foreach (['want_to_read' => 'Want to Read', 'currently_reading' => 'Currently Reading', 'completed' => 'Completed', 'paused' => 'Paused', 'abandoned' => 'Abandoned'] as $value => $label)<option value="{{ $value }}" @selected($shelf?->status === $value)>{{ $label }}</option>@endforeach
                            </select>
                            <button class="rounded-full bg-[#1B0D05] px-4 py-2 text-sm font-semibold text-[#FBF6EA]">{{ $shelf ? 'Update shelf' : 'Add to shelf' }}</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    </main>
</div>
</body>
</html>
