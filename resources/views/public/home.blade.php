<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('components.site-icons')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReadArena | Read the book. Prove it. Then duel.</title>
    <meta name="description" content="Read deeply, verify what you learn, and compete fairly with readers around the world in ReadArena.">
    <meta property="og:title" content="ReadArena | Read deeply. Compete fairly.">
    <meta property="og:description" content="Discover books, pass comprehension quizzes, and challenge readers in the ReadArena league.">
    <meta property="og:image" content="{{ asset('assets/readarena/feature-graphic-1024x500.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
@include('components.language-switcher')
<div class="flex min-h-screen flex-col">
    <x-public-navigation />

    <main>
        <section class="mx-auto grid max-w-7xl gap-10 px-6 py-16 lg:grid-cols-[1.1fr_0.9fr] lg:px-8 lg:py-24">
            <div class="max-w-2xl">
                <p class="mb-5 text-sm font-semibold uppercase tracking-[0.35em] text-[#B98A2C]">A reading club with a scoreboard</p>
                <h1 class="font-serif text-5xl leading-tight text-[#1B0D05] sm:text-6xl">
                    Read the book.<br>
                    <span class="italic text-[#B98A2C]">Prove it.</span> Then duel.
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-[#4f433b]">
                    ReadArena turns finishing a novel into a tournament. Read inside the app, pass the comprehension quiz, and challenge readers around the world.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="rounded-full bg-[#1B0D05] px-6 py-3 text-sm font-semibold text-[#FBF6EA]">{{ auth()->check() ? 'Continue to your portal' : 'Join ReadArena' }}</a>
                    <a href="/library" class="rounded-full border border-[#d8c9ad] bg-[#FBF6EA] px-6 py-3 text-sm font-semibold text-[#1B0D05]">Explore the Library</a>
                </div>
                <div class="mt-10 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-[#d8c9ad] bg-[#FBF6EA] p-4">
                        <p class="text-3xl font-semibold text-[#1B0D05]">12k+</p>
                        <p class="mt-1 text-sm text-[#786A5D]">Verified readers</p>
                    </div>
                    <div class="rounded-2xl border border-[#d8c9ad] bg-[#FBF6EA] p-4">
                        <p class="text-3xl font-semibold text-[#1B0D05]">96</p>
                        <p class="mt-1 text-sm text-[#786A5D]">Books with quizzes</p>
                    </div>
                    <div class="rounded-2xl border border-[#d8c9ad] bg-[#FBF6EA] p-4">
                        <p class="text-3xl font-semibold text-[#1B0D05]">48</p>
                        <p class="mt-1 text-sm text-[#786A5D]">Live shows aired</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-[28px] border border-[#d8c9ad] bg-[#1B0D05] p-2 shadow-xl">
                <img
                    src="{{ asset('assets/readarena/feature-graphic-1024x500.png') }}"
                    srcset="{{ asset('assets/readarena/feature-graphic-1024x500.png') }} 1024w, {{ asset('assets/readarena/feature-graphic-2048x1000.png') }} 2048w"
                    sizes="(min-width: 1024px) 50vw, 100vw"
                    alt="ReadArena: Read deeply. Compete fairly. An open book lights the way."
                    fetchpriority="high"
                    class="aspect-[2/1] w-full rounded-[22px] object-cover"
                >
                <div class="flex items-center gap-4 p-4 sm:p-5">
                    <img src="{{ asset('assets/readarena/app-icon-512.png') }}" alt="" class="h-14 w-14 rounded-xl ring-1 ring-white/20">
                    <div>
                        <p class="font-serif text-xl text-[#F4EBD8]">ReadArena mobile</p>
                        <p class="mt-1 text-sm text-[#d8c9ad]">Read deeply. Compete fairly.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="app-preview" class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
            <div class="mb-8 max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#B98A2C]">ReadArena on Android</p>
                <h2 class="mt-3 font-serif text-4xl text-[#1B0D05]">See how the reading journey works.</h2>
                <p class="mt-3 text-base leading-7 text-[#5e544d]">Explore the app screens for discovering books, proving what you read, and joining the competition.</p>
            </div>
            @php($appScreenshots = [
                ['file' => '01-onboarding.png', 'title' => 'Start the journey', 'description' => 'Find a book and follow each step.'],
                ['file' => '02-quiz-journey.png', 'title' => 'Take the quiz', 'description' => 'Verify your reading and earn points.'],
                ['file' => '03-competition-journey.png', 'title' => 'Compete and grow', 'description' => 'Challenge readers and build your rank.'],
                ['file' => '04-welcome.png', 'title' => 'Join the league', 'description' => 'Keep your reading progress together.'],
                ['file' => '05-sign-in.png', 'title' => 'Sign in', 'description' => 'Use your ReadArena account on mobile.'],
                ['file' => '06-create-account.png', 'title' => 'Create an account', 'description' => 'Join as a reader in a few steps.'],
            ])
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6">
                @foreach ($appScreenshots as $screenshot)
                    <figure class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-3 shadow-sm">
                        <img
                            src="{{ asset('assets/readarena/'.$screenshot['file']) }}"
                            alt="{{ $screenshot['title'] }} screen from the ReadArena Android app"
                            loading="lazy"
                            class="mx-auto w-full rounded-[18px] border border-[#e4d9c4] bg-[#F4EBD8] object-contain"
                        >
                        <figcaption class="px-2 pb-2 pt-4">
                            <h3 class="font-serif text-xl text-[#1B0D05]">{{ $screenshot['title'] }}</h3>
                            <p class="mt-1 text-sm leading-6 text-[#786A5D]">{{ $screenshot['description'] }}</p>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>

        <section id="features" class="mx-auto max-w-7xl scroll-mt-24 px-6 py-24 lg:px-8">
            <div class="mb-10 max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#B98A2C]">Six tools. One reading habit.</p>
                <h2 class="mt-3 font-serif text-4xl text-[#1B0D05]">A premium reading league built for depth, not noise.</h2>
            </div>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @php($features = [
                    ['title'=>'In-App Reader','description'=>'Full book reading, progress tracking, bookmarks, and paper-like typography.'],
                    ['title'=>'Comprehension Quizzes','description'=>'Ten questions after each book, timed scoring, and quiz verification.'],
                    ['title'=>'Live One-to-One Duels','description'=>'Match readers who completed the same book and earn reputation rewards.'],
                    ['title'=>'Podcast and Video Shows','description'=>'Apply as a guest, join debates, and watch recorded episodes.'],
                    ['title'=>'Reading Goals','description'=>'Yearly, monthly, and custom targets with streaks and pace tracking.'],
                    ['title'=>'Lessons and Recommendations','description'=>'Publish reflections and verified recommendations to grow your reputation.']
                ])
                @foreach($features as $feature)
                    <div class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm">
                        <h3 class="font-serif text-2xl text-[#1B0D05]">{{ $feature['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-[#5e544d]">{{ $feature['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="activity" class="scroll-mt-24 bg-[#1B0D05] py-24 text-[#F4EBD8]">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-[1fr_0.8fr]">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#D8A83E]">Recent activity</p>
                        <h2 class="mt-3 font-serif text-4xl">The league is already turning pages.</h2>
                        <div class="mt-8 space-y-4">
                            @forelse ($recentActivity as $item)
                                <div class="rounded-[20px] border border-[#3d261b] bg-[#2B170D] p-4 text-sm text-[#f7e9ca]">
                                    <p class="font-semibold">{{ $item['label'] }}</p>
                                    <p class="mt-1 text-[#d8c9ad]">{{ $item['detail'] }}</p>
                                </div>
                            @empty
                                <div class="rounded-[20px] border border-[#3d261b] bg-[#2B170D] p-4 text-sm text-[#d8c9ad]">Platform activity will appear here as readers join.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="rounded-[28px] border border-[#3d261b] bg-[#2B170D] p-8">
                        <h3 class="font-serif text-3xl text-[#F4EBD8]">A league with rules readers can see.</h3>
                        <p class="mt-4 text-sm leading-7 text-[#d8c9ad]">Readers pass a quiz for a published book before challenging someone to a duel. Each activity is tied to the reading behind it.</p>
                        <div class="mt-8 rounded-[22px] border border-[#d8c9ad]/30 bg-[#FBF6EA] p-6 text-[#24150D]">
                            <p class="font-serif text-xl">Read. Verify. Compete.</p>
                            <p class="mt-3 text-sm leading-6 text-[#5e544d]">Book quizzes create a clear starting point for reader challenges and leaderboard results.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <x-site-footer />
</div>
</body>
</html>
