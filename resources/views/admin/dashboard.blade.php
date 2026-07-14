<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration | BookDuel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#1B0D05] text-[#F4EBD8]">
<div class="min-h-screen">
    <aside class="fixed inset-y-0 left-0 hidden w-72 border-r border-[#3d261b] bg-[#130804] p-6 lg:block">
        <div class="flex items-center gap-3 text-xl font-semibold uppercase tracking-[0.2em]">
            <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#d8c9ad] bg-[#F4EBD8] text-sm text-[#1B0D05]">BD</span>
            <span>BookDuel Admin</span>
        </div>
        <nav class="mt-8 space-y-2 text-sm text-[#d8c9ad]">
            <a href="/admin" class="block rounded-[14px] bg-[#2B170D] px-4 py-3">Dashboard</a>
            <a href="/admin/books" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Books</a>
            <a href="/admin/quizzes" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Quizzes</a>
            <a href="/admin/duels" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Duels</a>
            <a href="/admin/shows" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Shows</a>
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

        <section class="grid gap-4 px-6 py-8 lg:grid-cols-4 lg:px-8">
            @foreach([
                ['label'=>'Readers','value'=>$readers],
                ['label'=>'Published books','value'=>$books],
                ['label'=>'Quizzes','value'=>$quizzes],
                ['label'=>'Live shows','value'=>$shows],
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
    </main>
</div>
</body>
</html>
