<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookDuel | Read the book. Prove it. Then duel.</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen">
    <header class="sticky top-0 z-20 border-b border-[#d8c9ad] bg-[#FBF6EA]/90 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
            <a href="/" class="flex items-center gap-3 text-xl font-semibold tracking-[0.2em] uppercase">
                <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#d8c9ad] bg-[#1B0D05] text-sm text-[#F4EBD8]">BD</span>
                <span class="text-[#1B0D05]">BookDuel</span>
            </a>
            <nav class="hidden items-center gap-8 text-sm font-medium text-[#786A5D] md:flex">
                <a href="#features" class="hover:text-[#1B0D05]">Features</a>
                <a href="#activity" class="hover:text-[#1B0D05]">Activity</a>
                <a href="/library" class="hover:text-[#1B0D05]">Library</a>
                <a href="/admin" class="hover:text-[#1B0D05]">Admin</a>
            </nav>
            <a href="/register" class="rounded-full border border-[#1B0D05] bg-[#1B0D05] px-5 py-2 text-sm font-semibold text-[#FBF6EA] shadow-sm">Get the App</a>
        </div>
    </header>

    <main>
        <section class="mx-auto grid max-w-7xl gap-10 px-6 py-16 lg:grid-cols-[1.1fr_0.9fr] lg:px-8 lg:py-24">
            <div class="max-w-2xl">
                <p class="mb-5 text-sm font-semibold uppercase tracking-[0.35em] text-[#B98A2C]">A reading club with a scoreboard</p>
                <h1 class="font-serif text-5xl leading-tight text-[#1B0D05] sm:text-6xl">
                    Read the book.<br>
                    <span class="italic text-[#B98A2C]">Prove it.</span> Then duel.
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-[#4f433b]">
                    BookDuel turns finishing a novel into a tournament. Read inside the app, pass the comprehension quiz, and challenge readers around the world.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="/register" class="rounded-full bg-[#1B0D05] px-6 py-3 text-sm font-semibold text-[#FBF6EA]">Join BookDuel</a>
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

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([['title'=>'Discover books','label'=>'01 — Discover'],['title'=>'Read in-app','label'=>'02 — Read In-App'],['title'=>'Prove you read it','label'=>'03 — Prove You Read It'],['title'=>'Duel readers','label'=>'04 — Duel Readers']] as $card)
                    <div class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-5 shadow-sm">
                        <div class="mb-5 h-36 rounded-[18px] bg-[#1B0D05] p-4 text-[#F4EBD8]">
                            <div class="flex h-full items-center justify-center rounded-[14px] border border-[#d8c9ad]/70 text-center text-sm font-medium">
                                {{ $card['title'] }}
                            </div>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#B98A2C]">{{ $card['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-6 py-8 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([['title'=>'Apply to the live show','label'=>'05 — Apply to the Live Show'],['title'=>'Set your reading goals','label'=>'06 — Set Your Reading Goals'],['title'=>'Write what you learned','label'=>'07 — Write What You Learned'],['title'=>'Recommend the book','label'=>'08 — Recommend the Book']] as $card)
                    <div class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-5">
                        <div class="mb-5 h-28 rounded-[18px] bg-[#2B170D] p-4 text-[#F4EBD8]">
                            <div class="flex h-full items-center justify-center rounded-[14px] border border-[#d8c9ad]/70 text-center text-sm">
                                {{ $card['title'] }}
                            </div>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#B98A2C]">{{ $card['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="features" class="mx-auto max-w-7xl px-6 py-24 lg:px-8">
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

        <section id="activity" class="bg-[#1B0D05] py-24 text-[#F4EBD8]">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-[1fr_0.8fr]">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#D8A83E]">Recent activity</p>
                        <h2 class="mt-3 font-serif text-4xl">The league is already turning pages.</h2>
                        <div class="mt-8 space-y-4">
                            @foreach ([
                                'A reader won a duel and earned fresh reputation points.',
                                'A reader passed a quiz and unlocked the next challenge.',
                                'A live show is starting soon and seats are filling fast.',
                                'A verified reader published a lesson and topped the weekly board.'
                            ] as $item)
                                <div class="rounded-[20px] border border-[#3d261b] bg-[#2B170D] p-4 text-sm text-[#f7e9ca]">{{ $item }}</div>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-[28px] border border-[#3d261b] bg-[#2B170D] p-8">
                        <h3 class="font-serif text-3xl text-[#F4EBD8]">Built for readers tired of the honor system.</h3>
                        <p class="mt-4 text-sm leading-7 text-[#d8c9ad]">Readers must complete verified quizzes before competing, which keeps the duels fair and the prestige earned.</p>
                        <div class="mt-8 rounded-[22px] border border-[#d8c9ad]/30 bg-[#FBF6EA] p-6 text-[#24150D]">
                            <p class="font-serif text-xl">“The first time I passed a verification quiz, the whole tournament felt real.”</p>
                            <div class="mt-5 flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#D8A83E] font-semibold text-[#1B0D05]">MR</div>
                                <div>
                                    <p class="font-semibold">Mina Rivera</p>
                                    <p class="text-sm text-[#786A5D]">Week 3 Champion</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-[#d8c9ad] bg-[#FBF6EA]">
        <div class="mx-auto grid max-w-7xl gap-8 px-6 py-12 lg:grid-cols-[1.2fr_0.8fr_0.8fr_0.8fr] lg:px-8">
            <div>
                <div class="flex items-center gap-3 text-xl font-semibold uppercase tracking-[0.2em]">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#d8c9ad] bg-[#1B0D05] text-sm text-[#F4EBD8]">BD</span>
                    <span class="text-[#1B0D05]">BookDuel</span>
                </div>
                <p class="mt-4 max-w-sm text-sm leading-7 text-[#5e544d]">A premium digital reading league for verified readers, live discussions, and literary competition.</p>
                <div class="mt-5 flex items-center gap-3 text-[#1B0D05]">
                    <a href="#" aria-label="BookDuel on Facebook" class="flex h-10 w-10 items-center justify-center rounded-full border border-[#d8c9ad] bg-[#F4EBD8] transition hover:bg-[#1B0D05] hover:text-[#F4EBD8]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current" aria-hidden="true">
                            <path d="M13.5 9H16V6h-2.5C10.8 6 9 7.8 9 10.5V13H7v3h2v6h3v-6h3l.6-3H12v-2.5c0-.8.7-1.5 1.5-1.5z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="BookDuel on X" class="flex h-10 w-10 items-center justify-center rounded-full border border-[#d8c9ad] bg-[#F4EBD8] transition hover:bg-[#1B0D05] hover:text-[#F4EBD8]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current" aria-hidden="true">
                            <path d="M18.9 2H22l-6.8 7.8L23 22h-6.3l-4.9-6.4L6.2 22H3l7.3-8.3L1 2h6.4l4.4 5.9L18.9 2zm-1.1 18h1.8L6.4 3.9H4.5L17.8 20z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="BookDuel on Instagram" class="flex h-10 w-10 items-center justify-center rounded-full border border-[#d8c9ad] bg-[#F4EBD8] transition hover:bg-[#1B0D05] hover:text-[#F4EBD8]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current" aria-hidden="true">
                            <path d="M7.5 2h9A5.5 5.5 0 0 1 22 7.5v9a5.5 5.5 0 0 1-5.5 5.5h-9A5.5 5.5 0 0 1 2 16.5v-9A5.5 5.5 0 0 1 7.5 2zm0 2A3.5 3.5 0 0 0 4 7.5v9A3.5 3.5 0 0 0 7.5 20h9a3.5 3.5 0 0 0 3.5-3.5v-9A3.5 3.5 0 0 0 16.5 4h-9zm10.8 1.5a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="BookDuel on YouTube" class="flex h-10 w-10 items-center justify-center rounded-full border border-[#d8c9ad] bg-[#F4EBD8] transition hover:bg-[#1B0D05] hover:text-[#F4EBD8]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current" aria-hidden="true">
                            <path d="M23 12s0-3.3-.4-4.9c-.2-.9-.9-1.6-1.8-1.8C19.2 5 12 5 12 5s-7.2 0-8.8.3c-.9.2-1.6.9-1.8 1.8C1 8.7 1 12 1 12s0 3.3.4 4.9c.2.9.9 1.6 1.8 1.8C4.8 19 12 19 12 19s7.2 0 8.8-.3c.9-.2 1.6-.9 1.8-1.8.4-1.6.4-4.9.4-4.9zm-14 3.5v-7l6 3.5-6 3.5z"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div>
                <h4 class="font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">Quick links</h4>
                <ul class="mt-4 space-y-2 text-sm text-[#5e544d]">
                    <li><a href="/library">Library</a></li>
                    <li><a href="/about">About</a></li>
                    <li><a href="/register">Join</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">Legal</h4>
                <ul class="mt-4 space-y-2 text-sm text-[#5e544d]">
                    <li>Privacy Policy</li>
                    <li>Terms & Conditions</li>
                    <li>Reader’s Code</li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">Newsletter</h4>
                <div class="mt-4 rounded-full border border-[#d8c9ad] bg-[#F4EBD8] p-2">
                    <input class="w-full bg-transparent px-3 py-2 text-sm outline-none" placeholder="Email address" />
                </div>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
