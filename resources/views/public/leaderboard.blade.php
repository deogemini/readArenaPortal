<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reader Leaderboard | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<main class="mx-auto min-h-screen max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div><a href="{{ route('home') }}" class="text-sm font-semibold uppercase tracking-[0.25em] text-[#B98A2C]">ReadArena</a><h1 class="mt-2 font-serif text-4xl text-[#1B0D05]">Reader leaderboard</h1></div>
        <a href="{{ route('library') }}" class="rounded-full bg-[#1B0D05] px-5 py-2 text-sm font-semibold text-[#FBF6EA]">Explore library</a>
    </header>
    <section class="mt-8 rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm sm:p-8">
        <p class="max-w-3xl text-sm leading-6 text-[#786A5D]">Points add each reader’s best passing score per published quiz in the selected period. Retaking one quiz does not add its score more than once.</p>
        <nav class="mt-6 flex flex-wrap gap-2">
            @foreach (['daily' => 'Today', 'weekly' => 'This week', 'monthly' => 'This month', 'all_time' => 'All time'] as $value => $label)
                <a href="{{ route('leaderboard', ['period' => $value]) }}" class="rounded-full px-4 py-2 text-sm {{ $period === $value ? 'bg-[#1B0D05] text-[#FBF6EA]' : 'border border-[#d8c9ad] text-[#24150D]' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="mt-6 overflow-hidden rounded-[20px] border border-[#d8c9ad]">
            <div class="overflow-x-auto"><table class="w-full min-w-[480px] text-left">
                <thead class="bg-[#F4EBD8] text-xs uppercase tracking-wider text-[#786A5D]"><tr><th class="px-5 py-4">Rank</th><th class="px-5 py-4">Reader</th><th class="px-5 py-4">Verified quizzes</th><th class="px-5 py-4 text-right">Points</th></tr></thead>
                <tbody class="divide-y divide-[#d8c9ad]">
                    @forelse ($rankings as $player)
                        <tr><td class="px-5 py-4 font-serif text-xl text-[#B98A2C]">{{ $player['rank'] }}</td><td class="px-5 py-4 font-semibold">{{ $player['name'] }}</td><td class="px-5 py-4 text-sm text-[#786A5D]">{{ $player['verified_quizzes'] }}</td><td class="px-5 py-4 text-right font-semibold">{{ number_format($player['points']) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-[#786A5D]">No verified quiz results for this period yet.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </section>
</main>
</body>
</html>
