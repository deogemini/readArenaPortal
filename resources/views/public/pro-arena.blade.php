<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pro Arena Packages | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
            <a href="/" class="font-serif text-2xl text-[#1B0D05]">ReadArena</a>
            <a href="/register" class="rounded-full bg-[#1B0D05] px-5 py-2 text-sm font-semibold text-[#FBF6EA]">Join the Arena</a>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="max-w-3xl">
            <p class="text-sm uppercase tracking-[0.35em] text-[#B98A2C]">Global Arena</p>
            <h1 class="mt-3 font-serif text-5xl text-[#1B0D05]">Pro Arena Subscription Packages</h1>
            <p class="mt-4 text-base leading-8 text-[#5e544d]">Choose your package and compete with readers in Tanzania or globally.</p>
        </div>

        <section class="mt-10 grid gap-6 md:grid-cols-2">
            @foreach($packages as $package)
                <article class="rounded-[26px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#B98A2C]">{{ $package->region_scope }}</p>
                    <h2 class="mt-2 font-serif text-3xl text-[#1B0D05]">{{ $package->name }}</h2>
                    <p class="mt-4 text-2xl font-semibold text-[#1B0D05]">{{ number_format($package->price_tsh) }} TSH</p>
                    <p class="mt-2 text-sm text-[#5e544d]">{{ $package->games_count }} games</p>
                    <p class="mt-2 text-sm text-[#5e544d]">Reward: {{ $package->reward_label ?? 'Arena points' }}</p>
                </article>
            @endforeach
        </section>
    </main>
</div>
</body>
</html>
