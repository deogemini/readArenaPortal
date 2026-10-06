<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Moderation | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#1B0D05] text-[#F4EBD8]">
@include('components.language-switcher')
<main class="mx-auto min-h-screen max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <header class="flex flex-wrap items-center justify-between gap-4"><div><p class="text-xs uppercase tracking-[0.3em] text-[#D8A83E]">Community safety</p><h1 class="mt-2 font-serif text-3xl">Book reviews</h1></div><a href="{{ route('admin.dashboard') }}" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Admin dashboard</a></header>
    @if (session('status'))<div class="mt-5 rounded-xl border border-[#3d261b] bg-[#2B170D] px-4 py-3 text-sm">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="mt-5 rounded-xl border border-[#7a2e22] bg-[#2B170D] px-4 py-3 text-sm text-[#f8d2c8]">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <section class="mt-6 space-y-4">
        @forelse ($reviews as $review)
            <article class="rounded-[22px] border border-[#3d261b] bg-[#2B170D] p-5">
                <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="text-xs uppercase tracking-wider text-[#D8A83E]">{{ $review->book?->title ?? 'Book removed' }} · {{ $review->rating }}/5</p><h2 class="mt-1 font-serif text-2xl">{{ $review->title ?: 'Reader review' }}</h2><p class="mt-1 text-xs text-[#d8c9ad]">{{ $review->user?->name ?? 'Account removed' }} · {{ $review->user?->email }}</p></div><span class="rounded-full border border-[#70533e] px-3 py-1 text-xs uppercase">{{ $review->status }}</span></div>
                <p class="mt-4 whitespace-pre-line text-sm leading-7 text-[#e5d7bf]">{{ $review->body }}</p>
                @if ($review->status !== 'published')
                    <div class="mt-4 flex gap-2">
                        @foreach (['published' => 'Approve', 'rejected' => 'Reject'] as $status => $label)
                            <form action="{{ route('admin.reviews.update', $review) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $status }}"><button class="rounded-full {{ $status === 'published' ? 'bg-[#D8A83E] text-[#1B0D05]' : 'border border-[#70533e]' }} px-4 py-2 text-xs font-semibold">{{ $label }}</button></form>
                        @endforeach
                    </div>
                @else
                    <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="mt-4">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="rounded-full border border-[#70533e] px-4 py-2 text-xs">Remove from public view</button></form>
                @endif
            </article>
        @empty
            <p class="rounded-[22px] border border-dashed border-[#3d261b] p-8 text-center text-sm text-[#d8c9ad]">No reviews have been submitted.</p>
        @endforelse
    </section>
    <div class="mt-5">{{ $reviews->links() }}</div>
</main>
</body>
</html>
