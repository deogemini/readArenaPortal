<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Shows | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#1B0D05] text-[#F4EBD8]">
<div class="min-h-screen">
    <aside class="fixed inset-y-0 left-0 hidden w-72 border-r border-[#3d261b] bg-[#130804] p-6 lg:block">
        <div class="flex items-center gap-3 text-xl font-semibold uppercase tracking-[0.2em]">
            <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#d8c9ad] bg-[#F4EBD8] text-sm text-[#1B0D05]">BD</span>
            <span>ReadArena Admin</span>
        </div>
        <nav class="mt-8 space-y-2 text-sm text-[#d8c9ad]">
            <a href="{{ route('admin.dashboard') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Dashboard</a>
            <a href="{{ route('admin.users') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Users</a>
            <a href="{{ route('admin.books') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Books</a>
            <a href="{{ route('admin.quizzes') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Quizzes</a>
            <a href="{{ route('admin.duels') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Duels</a>
            <a href="{{ route('admin.shows') }}" class="block rounded-[14px] bg-[#2B170D] px-4 py-3">Shows</a>
            <a href="{{ route('admin.packages') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Packages</a>
            <a href="{{ route('admin.settings') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Settings</a>
        </nav>
    </aside>

    <main class="lg:ml-72">
        <header class="border-b border-[#3d261b] bg-[#1B0D05] px-6 py-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-[#D8A83E]">Live event management</p>
                    <h1 class="mt-2 font-serif text-3xl">Shows</h1>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Back to dashboard</a>
            </div>
        </header>

        <section class="px-6 py-8 lg:px-8">
            <div class="mx-auto max-w-7xl">
                @if (session('status'))
                    <div class="mb-5 rounded-xl border border-[#3d261b] bg-[#2B170D] px-4 py-3 text-sm">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-[#7a2e22] bg-[#2B170D] px-4 py-3 text-sm text-[#f8d2c8]">
                        <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <section class="mb-8 rounded-[20px] border border-[#3d261b] bg-[#2B170D] p-6">
                    <h2 class="font-serif text-2xl">Schedule a show</h2>
                    <form action="{{ route('admin.shows.store') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                        @csrf
                        <label class="grid gap-1 text-sm">Title<input name="title" value="{{ old('title') }}" maxlength="255" required class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2"></label>
                        <label class="grid gap-1 text-sm">Related book
                            <select name="book_id" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                                <option value="">No book selected</option>
                                @foreach ($books as $book)<option value="{{ $book->id }}">{{ $book->title }}</option>@endforeach
                            </select>
                        </label>
                        <label class="grid gap-1 text-sm">Start date and time<input name="start_at" type="datetime-local" value="{{ old('start_at') }}" required class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2"></label>
                        <label class="grid gap-1 text-sm">Status
                            <select name="status" required class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                                @foreach (['scheduled' => 'Scheduled', 'live' => 'Live', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                            </select>
                        </label>
                        <label class="grid gap-1 text-sm md:col-span-2">Description<textarea name="description" rows="3" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">{{ old('description') }}</textarea></label>
                        <div class="md:col-span-2"><button class="rounded-full bg-[#D8A83E] px-6 py-2 text-sm font-semibold text-[#1B0D05]">Create show</button></div>
                    </form>
                </section>

                <div class="space-y-4">
                    @forelse ($shows as $show)
                        <article class="rounded-[20px] border border-[#3d261b] bg-[#2B170D] p-5">
                            <form action="{{ route('admin.shows.update', $show) }}" method="POST" class="grid gap-4 md:grid-cols-2">
                                @csrf
                                @method('PATCH')
                                <label class="grid gap-1 text-sm">Title<input name="title" value="{{ $show->title }}" maxlength="255" required class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2"></label>
                                <label class="grid gap-1 text-sm">Related book
                                    <select name="book_id" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                                        <option value="">No book selected</option>
                                        @foreach ($books as $book)<option value="{{ $book->id }}" @selected($show->book_id === $book->id)>{{ $book->title }}</option>@endforeach
                                    </select>
                                </label>
                                <label class="grid gap-1 text-sm">Start date and time<input name="start_at" type="datetime-local" value="{{ $show->start_at?->format('Y-m-d\TH:i') }}" required class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2"></label>
                                <label class="grid gap-1 text-sm">Status
                                    <select name="status" required class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                                        @foreach (['scheduled' => 'Scheduled', 'live' => 'Live', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)<option value="{{ $value }}" @selected($show->status === $value)>{{ $label }}</option>@endforeach
                                    </select>
                                </label>
                                <label class="grid gap-1 text-sm md:col-span-2">Description<textarea name="description" rows="2" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">{{ $show->description }}</textarea></label>
                                <div class="flex flex-wrap gap-3 md:col-span-2">
                                    <button class="rounded-full bg-[#D8A83E] px-5 py-2 text-sm font-semibold text-[#1B0D05]">Save changes</button>
                                </div>
                            </form>
                            <form action="{{ route('admin.shows.destroy', $show) }}" method="POST" class="mt-3" onsubmit="return confirm('Delete this show?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-full border border-[#7a2e22] px-5 py-2 text-sm text-[#f8d2c8]">Delete show</button>
                            </form>
                            @if ($show->applications->isNotEmpty())
                                <div class="mt-5 border-t border-[#3d261b] pt-5">
                                    <h3 class="font-serif text-xl">Guest applications <span class="text-sm text-[#d8c9ad]">({{ $show->applications_count }})</span></h3>
                                    <div class="mt-3 space-y-3">
                                        @foreach ($show->applications as $application)
                                            <div class="rounded-[16px] border border-[#3d261b] bg-[#1B0D05] p-4">
                                                <div class="flex flex-wrap items-center justify-between gap-2"><p class="font-semibold">{{ $application->user?->name ?? 'Account removed' }} <span class="text-xs font-normal text-[#d8c9ad]">· passed quiz score {{ $application->quiz_score }}%</span></p><span class="rounded-full border border-[#70533e] px-3 py-1 text-xs uppercase">{{ $application->status }}</span></div>
                                                <p class="mt-2 text-sm leading-6 text-[#d8c9ad]">{{ $application->motivation }}</p>
                                                @if ($application->status === 'pending')
                                                    <div class="mt-3 flex gap-2">
                                                        @foreach (['approved' => 'Approve', 'rejected' => 'Reject'] as $status => $label)
                                                            <form action="{{ route('admin.show-applications.update', $application) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $status }}"><button class="rounded-full {{ $status === 'approved' ? 'bg-[#D8A83E] text-[#1B0D05]' : 'border border-[#70533e]' }} px-4 py-1.5 text-xs font-semibold">{{ $label }}</button></form>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </article>
                    @empty
                        <p class="rounded-[20px] border border-[#3d261b] bg-[#2B170D] p-6 text-sm text-[#d8c9ad]">No shows have been scheduled yet.</p>
                    @endforelse
                </div>

                <div class="mt-5">{{ $shows->links() }}</div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
