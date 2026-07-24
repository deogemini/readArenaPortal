<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration | ReadArena</title>
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
            <a href="/admin" class="block rounded-[14px] bg-[#2B170D] px-4 py-3">Dashboard</a>
            <a href="/admin/users" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Users</a>
            <a href="/admin/books" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Books</a>
            <a href="/admin/quizzes" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Quizzes</a>
            <a href="/admin/duels" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Duels</a>
            <a href="/admin/shows" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Shows</a>
            <a href="/admin/packages" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Packages</a>
            <a href="/admin/settings" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Settings</a>
        </nav>
    </aside>

    <main class="lg:ml-72">
        <header class="border-b border-[#3d261b] bg-[#1B0D05] px-6 py-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-[#D8A83E]">Quiet workbench</p>
                    <h1 class="mt-2 font-serif text-3xl">Platform control center</h1>
                </div>
                <a href="/" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">View site</a>
            </div>
        </header>

        @if (session('status'))
            <div class="px-6 pt-6 lg:px-8">
                <div class="rounded-xl border border-[#3d261b] bg-[#2B170D] px-4 py-3 text-sm text-[#F4EBD8]">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="px-6 pt-6 lg:px-8">
                <div class="rounded-xl border border-[#7a2e22] bg-[#2B170D] px-4 py-3 text-sm text-[#f8d2c8]">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <section class="grid gap-4 px-6 py-8 lg:grid-cols-5 lg:px-8">
            @foreach([
                ['label'=>'Readers','value'=>$readers],
                ['label'=>'Published books','value'=>$books],
                ['label'=>'Quizzes','value'=>$quizzes],
                ['label'=>'Live shows','value'=>$shows],
                ['label'=>'Ongoing competitions','value'=>$ongoingCompetitions],
            ] as $card)
                <div class="rounded-[24px] border border-[#3d261b] bg-[#2B170D] p-5">
                    <p class="text-sm uppercase tracking-[0.3em] text-[#D8A83E]">{{ $card['label'] }}</p>
                    <p class="mt-4 font-serif text-4xl">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 px-6 pb-8 lg:grid-cols-[1.2fr_0.8fr] lg:px-8">
            <div class="rounded-[28px] border border-[#3d261b] bg-[#2B170D] p-6">
                <h2 class="font-serif text-2xl">Library management</h2>
                <div class="mt-4 space-y-3">
                    @foreach([['label'=>'Curate the library','detail'=>'Publish new books and assign quizzes.'],['label'=>'Design quizzes with points','detail'=>'Create prompts, answer keys, and scoring logic.'],['label'=>'Run live competitions','detail'=>'Launch duels, schedule shows, and publish results.']] as $item)
                        <div class="rounded-[18px] border border-[#3d261b] bg-[#1B0D05] p-4">
                            <h3 class="font-semibold">{{ $item['label'] }}</h3>
                            <p class="mt-1 text-sm text-[#d8c9ad]">{{ $item['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="rounded-[28px] border border-[#3d261b] bg-[#2B170D] p-6">
                <h2 class="font-serif text-2xl">Recent activity</h2>
                <div class="mt-4 space-y-3">
                    @foreach([['label'=>'Book uploaded','detail'=>'Beloved staged for review'],['label'=>'Quiz published','detail'=>'Things Fall Apart quiz approved'],['label'=>'Show scheduled','detail'=>'The Wednesday Salon confirmed']] as $item)
                        <div class="rounded-[18px] border border-[#3d261b] bg-[#1B0D05] p-4">
                            <h3 class="font-semibold">{{ $item['label'] }}</h3>
                            <p class="mt-1 text-sm text-[#d8c9ad]">{{ $item['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="px-6 pb-8 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
                <div class="rounded-[28px] border border-[#3d261b] bg-[#2B170D] p-6">
                    <h2 class="font-serif text-2xl">Upload Live Competition Video</h2>
                    <form action="{{ route('admin.competition-videos.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-4">
                        @csrf
                        <input name="title" value="{{ old('title') }}" placeholder="Video title" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <select name="live_show_id" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                            <option value="">Attach to live show (optional)</option>
                            @foreach($liveShows as $show)
                                <option value="{{ $show->id }}" @selected((string) old('live_show_id') === (string) $show->id)>{{ $show->title }}</option>
                            @endforeach
                        </select>
                        <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <select name="status" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                            <option value="published" @selected(old('status', 'published') === 'published')>Published</option>
                            <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                        </select>
                        <button class="rounded-full bg-[#D8A83E] px-6 py-2 text-sm font-semibold text-[#1B0D05]">Upload video</button>
                    </form>
                </div>

                <div class="rounded-[28px] border border-[#3d261b] bg-[#2B170D] p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="font-serif text-2xl">Recent Competition Videos</h2>
                        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                            <input
                                type="text"
                                name="video_search"
                                value="{{ $videoSearch }}"
                                placeholder="Search video title"
                                class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-3 py-2 text-sm"
                            >
                            <button class="rounded-full bg-[#D8A83E] px-4 py-2 text-xs font-semibold text-[#1B0D05]">Search</button>
                            @if($videoSearch !== '')
                                <a href="{{ route('admin.dashboard') }}" class="rounded-full border border-[#3d261b] px-3 py-2 text-xs text-[#d8c9ad]">Clear</a>
                            @endif
                        </form>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse($competitionVideos as $video)
                            <div class="rounded-[18px] border border-[#3d261b] bg-[#1B0D05] p-4">
                                <h3 class="font-semibold">{{ $video->title }}</h3>
                                <p class="mt-1 text-sm text-[#d8c9ad]">
                                    {{ $video->liveShow?->title ? 'Show: '.$video->liveShow->title : 'Unattached video' }}
                                </p>

                                <video controls preload="metadata" class="mt-3 w-full rounded-xl border border-[#3d261b] bg-black">
                                    <source src="{{ asset('storage/'.$video->video_path) }}">
                                </video>

                                <form action="{{ route('admin.competition-videos.update', $video) }}" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <input name="title" value="{{ $video->title }}" class="rounded-xl border border-[#3d261b] bg-[#2B170D] px-3 py-2 text-sm" required>
                                    <select name="live_show_id" class="rounded-xl border border-[#3d261b] bg-[#2B170D] px-3 py-2 text-sm">
                                        <option value="">Unattached</option>
                                        @foreach($liveShows as $show)
                                            <option value="{{ $show->id }}" @selected((int) $video->live_show_id === (int) $show->id)>{{ $show->title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <select name="status" class="rounded-xl border border-[#3d261b] bg-[#2B170D] px-3 py-2 text-sm">
                                            <option value="published" @selected($video->status === 'published')>Published</option>
                                            <option value="draft" @selected($video->status === 'draft')>Draft</option>
                                        </select>
                                        <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime" class="rounded-xl border border-[#3d261b] bg-[#2B170D] px-3 py-2 text-sm">
                                    </div>
                                    <button class="w-fit rounded-full bg-[#D8A83E] px-4 py-2 text-xs font-semibold text-[#1B0D05]">Save changes</button>
                                </form>
                                <div class="mt-2">
                                <form action="{{ route('admin.competition-videos.destroy', $video) }}" method="POST" onsubmit="return confirm('Delete this video?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-full border border-[#7a2e22] px-4 py-2 text-xs font-semibold text-[#f8d2c8]">Delete</button>
                                </form>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-[18px] border border-[#3d261b] bg-[#1B0D05] p-4 text-sm text-[#d8c9ad]">
                                No competition videos uploaded yet.
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-4">{{ $competitionVideos->links() }}</div>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
