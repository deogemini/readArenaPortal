<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommendations | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
@include('components.language-switcher')
<div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(216,168,62,0.18),_transparent_40%)]">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/90">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <div><p class="text-sm uppercase tracking-[0.35em] text-[#B98A2C]">Reader lounge</p><h1 class="font-serif text-2xl text-[#1B0D05]">Your recommendations</h1></div>
            <nav class="flex flex-wrap gap-2 text-sm">
                <a href="{{ route('reader.dashboard') }}" class="rounded-full border border-[#d8c9ad] px-4 py-2">Dashboard</a>
                <a href="{{ route('reader.library') }}" class="rounded-full border border-[#d8c9ad] px-4 py-2">Library</a>
                <a href="{{ route('reader.lessons') }}" class="rounded-full border border-[#d8c9ad] px-4 py-2">Lessons</a>
                <a href="{{ route('reader.recommendations') }}" class="rounded-full bg-[#1B0D05] px-4 py-2 text-[#FBF6EA]">Recommendations</a>
            </nav>
            @include('components.portal-logout')
        </div>
    </header>
    <main class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:px-8">
        @if (session('status'))<div class="rounded-xl border border-[#d8c9ad] bg-[#FBF6EA] p-4 text-sm lg:col-span-2">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="rounded-xl border border-[#c17b6f] bg-[#FBF6EA] p-4 text-sm text-[#7a2e22] lg:col-span-2"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="h-fit rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#B98A2C]">Book recommendation</p>
            <h2 class="mt-2 font-serif text-3xl text-[#1B0D05]">Share a good read</h2>
            <p class="mt-2 text-sm leading-6 text-[#786A5D]">Keep a private draft or publish after passing a quiz for the book.</p>
            <form action="{{ route('reader.recommendations.store') }}" method="POST" class="mt-5 grid gap-4">
                @csrf
                <label class="grid gap-1 text-sm">Book<select name="book_id" required class="rounded-xl border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2"><option value="">Choose a published book</option>@foreach ($books as $book)<option data-no-translate value="{{ $book->id }}" @selected(old('book_id') == $book->id)>{{ $book->title }}</option>@endforeach</select></label>
                <label class="grid gap-1 text-sm">Your recommendation<textarea name="message" rows="5" maxlength="5000" required class="rounded-xl border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2">{{ old('message') }}</textarea></label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm">Rating<select name="rating" class="rounded-xl border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2">@for ($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}">{{ $rating }} / 5</option>@endfor</select></label>
                    <label class="grid gap-1 text-sm">Visibility<select name="visibility" class="rounded-xl border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2"><option value="private">Private</option><option value="followers">Followers</option><option value="public">Public</option></select></label>
                </div>
                <label class="grid gap-1 text-sm">Status<select name="status" class="rounded-xl border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-2"><option value="draft">Draft</option><option value="published">Publish</option></select></label>
                <button class="w-fit rounded-full bg-[#1B0D05] px-5 py-2 text-sm font-semibold text-[#FBF6EA]">Save recommendation</button>
            </form>
        </section>

        <section class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm">
            <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#B98A2C]">Your writing</p><h2 class="mt-2 font-serif text-3xl text-[#1B0D05]">Recommendations</h2></div><a href="{{ route('reader.lessons') }}" class="text-sm font-semibold underline">Manage lessons</a></div>
            <div class="mt-5 space-y-4">
                @forelse ($recommendations as $recommendation)
                    <details class="rounded-[20px] border border-[#d8c9ad] bg-[#F4EBD8] p-5">
                        <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-3"><span><span class="block font-semibold text-[#1B0D05]" data-no-translate>{{ $recommendation->book?->title }}</span><span class="mt-1 block text-xs text-[#786A5D]">{{ $recommendation->rating }}/5 · {{ ucfirst($recommendation->status) }} · {{ ucfirst($recommendation->visibility) }}</span><span class="mt-2 block text-sm text-[#5e544d]" data-no-translate>{{ $recommendation->message }}</span></span><span class="text-sm text-[#B98A2C]">Edit</span></summary>
                        <form action="{{ route('reader.recommendations.update', $recommendation) }}" method="POST" class="mt-5 grid gap-3">
                            @csrf @method('PATCH')
                            <label class="grid gap-1 text-sm">Book<select name="book_id" required class="rounded-xl border border-[#d8c9ad] bg-[#FBF6EA] px-4 py-2">@foreach ($books as $book)<option data-no-translate value="{{ $book->id }}" @selected($recommendation->book_id === $book->id)>{{ $book->title }}</option>@endforeach</select></label>
                            <label class="grid gap-1 text-sm">Recommendation<textarea name="message" rows="4" maxlength="5000" required class="rounded-xl border border-[#d8c9ad] bg-[#FBF6EA] px-4 py-2">{{ $recommendation->message }}</textarea></label>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <label class="grid gap-1 text-sm">Rating<select name="rating" class="rounded-xl border border-[#d8c9ad] bg-[#FBF6EA] px-4 py-2">@for ($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}" @selected($recommendation->rating === $rating)>{{ $rating }} / 5</option>@endfor</select></label>
                                <label class="grid gap-1 text-sm">Visibility<select name="visibility" class="rounded-xl border border-[#d8c9ad] bg-[#FBF6EA] px-4 py-2">@foreach (['private', 'followers', 'public'] as $visibility)<option value="{{ $visibility }}" @selected($recommendation->visibility === $visibility)>{{ ucfirst($visibility) }}</option>@endforeach</select></label>
                                <label class="grid gap-1 text-sm">Status<select name="status" class="rounded-xl border border-[#d8c9ad] bg-[#FBF6EA] px-4 py-2">@foreach (['draft', 'published'] as $status)<option value="{{ $status }}" @selected($recommendation->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
                            </div>
                            <button class="w-fit rounded-full bg-[#1B0D05] px-5 py-2 text-sm font-semibold text-[#FBF6EA]">Save changes</button>
                        </form>
                        <form action="{{ route('reader.recommendations.destroy', $recommendation) }}" method="POST" class="mt-3" onsubmit="return confirm('Delete this recommendation?')">@csrf @method('DELETE')<button class="rounded-full border border-[#7a2e22] px-5 py-2 text-sm text-[#7a2e22]">Delete recommendation</button></form>
                    </details>
                @empty
                    <p class="rounded-[18px] border border-[#d8c9ad] p-5 text-sm text-[#786A5D]">You have not recommended a book yet.</p>
                @endforelse
            </div>
        </section>
    </main>
</div>
</body>
</html>
