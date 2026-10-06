<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reader Ideas | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#1B0D05] text-[#F4EBD8]">
@include('components.language-switcher')
<main class="mx-auto min-h-screen max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div><p class="text-xs uppercase tracking-[0.3em] text-[#D8A83E]">Reader feedback</p><h1 class="mt-2 font-serif text-3xl">Improvement ideas</h1><p class="mt-2 max-w-2xl text-sm text-[#d8c9ad]">Read suggestions submitted from the Android app and update their review status.</p></div>
        <a href="{{ route('admin.dashboard') }}" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Admin dashboard</a>
    </header>

    @if (session('status'))<div class="mt-5 rounded-xl border border-[#3d261b] bg-[#2B170D] px-4 py-3 text-sm">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="mt-5 rounded-xl border border-[#7a2e22] bg-[#2B170D] px-4 py-3 text-sm text-[#f8d2c8]">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

    <form method="GET" action="{{ route('admin.reader-ideas') }}" class="mt-6 flex flex-wrap gap-3 rounded-[22px] border border-[#3d261b] bg-[#2B170D] p-4">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search idea or reader" class="min-w-56 flex-1 rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2 text-sm">
        <select name="status" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2 text-sm">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>@endforeach
        </select>
        <button class="rounded-full bg-[#D8A83E] px-5 py-2 text-sm font-semibold text-[#1B0D05]">Filter</button>
        @if (!empty($filters))<a href="{{ route('admin.reader-ideas') }}" class="rounded-full border border-[#70533e] px-4 py-2 text-sm">Clear</a>@endif
    </form>

    <section class="mt-5 space-y-4">
        @forelse ($ideas as $idea)
            <article class="rounded-[22px] border border-[#3d261b] bg-[#2B170D] p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 text-xs uppercase tracking-wider text-[#D8A83E]"><span>{{ ucfirst($idea->category) }}</span><span>·</span><time>{{ $idea->created_at?->format('M j, Y H:i') }}</time></div>
                        <h2 class="mt-2 font-serif text-2xl">{{ $idea->title }}</h2>
                        <p class="mt-1 text-sm text-[#d8c9ad]">{{ $idea->user?->name ?? 'Deleted reader' }} · {{ $idea->user?->email }}</p>
                    </div>
                    <span class="rounded-full border border-[#70533e] px-3 py-1 text-xs uppercase">{{ $idea->status }}</span>
                </div>
                <p class="mt-5 whitespace-pre-line text-sm leading-7 text-[#e5d7bf]">{{ $idea->description }}</p>

                @if ($idea->attachment_path)
                    <a href="{{ route('admin.reader-ideas.attachment', $idea) }}" class="mt-4 inline-flex max-w-full items-center gap-2 rounded-full border border-[#70533e] px-4 py-2 text-sm hover:bg-[#1B0D05]">
                        <span>Download attachment</span><span class="max-w-64 truncate text-[#d8c9ad]">{{ $idea->attachment_name }}</span><span class="text-xs text-[#d8c9ad]">({{ number_format(($idea->attachment_size ?? 0) / 1048576, 2) }} MB)</span>
                    </a>
                @endif

                <form action="{{ route('admin.reader-ideas.update', $idea) }}" method="POST" class="mt-5 grid gap-3 border-t border-[#3d261b] pt-5 md:grid-cols-[220px_1fr_auto]">
                    @csrf @method('PATCH')
                    <label class="grid gap-1 text-xs text-[#d8c9ad]">Status<select name="status" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-3 py-2 text-sm text-[#F4EBD8]" required>@foreach ($statuses as $status)<option value="{{ $status }}" @selected($idea->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
                    <label class="grid gap-1 text-xs text-[#d8c9ad]">Internal admin notes<textarea name="admin_notes" rows="2" maxlength="5000" placeholder="Private notes for the admin team" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-3 py-2 text-sm text-[#F4EBD8]">{{ $idea->admin_notes }}</textarea></label>
                    <button class="self-end rounded-full bg-[#D8A83E] px-5 py-2 text-sm font-semibold text-[#1B0D05]">Save update</button>
                </form>
            </article>
        @empty
            <p class="rounded-[22px] border border-dashed border-[#3d261b] p-8 text-center text-sm text-[#d8c9ad]">No reader ideas match these filters.</p>
        @endforelse
    </section>
    <div class="mt-5">{{ $ideas->links() }}</div>
</main>
</body>
</html>
