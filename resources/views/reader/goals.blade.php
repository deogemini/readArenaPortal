<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading Goals | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/90">
        <div class="mx-auto max-w-7xl px-6 py-4 lg:px-8">
            <a href="/reader/dashboard" class="font-serif text-2xl text-[#1B0D05]">Your goals</a>
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-8 shadow-sm">
            <h1 class="font-serif text-3xl text-[#1B0D05]">Reading goals</h1>
            <div class="mt-6 space-y-4">
                @forelse($goals as $goal)
                    <div class="rounded-[20px] border border-[#d8c9ad] bg-[#F4EBD8] p-5">
                        <h2 class="font-semibold text-[#1B0D05]">{{ $goal->title }}</h2>
                        <p class="mt-2 text-sm text-[#5e544d]">{{ $goal->description }}</p>
                    </div>
                @empty
                    <p class="text-sm text-[#5e544d]">No goals yet. Create one to keep your reading streak alive.</p>
                @endforelse
            </div>
        </div>
    </main>
</div>
</body>
</html>
