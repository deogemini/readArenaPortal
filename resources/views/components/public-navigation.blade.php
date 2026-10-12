@php
    $onLibrary = request()->routeIs('library', 'books.show');
    $onProArena = request()->routeIs('pro-arena');
    $featuresUrl = route('home').'#features';
    $activityUrl = route('home').'#activity';
    $libraryUrl = route('library');
    $proArenaUrl = route('pro-arena');
    $appUrl = 'https://play.google.com/store/apps/details?id=tz.co.eportsolutions.readarena';
@endphp

<header class="sticky top-0 z-30 border-b border-[#d8c9ad] bg-[#FBF6EA]/95 backdrop-blur">
    <div class="relative mx-auto flex w-full items-center justify-between gap-4 px-2 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" aria-label="ReadArena home" class="inline-flex shrink-0 items-center gap-3 rounded-full text-sm font-semibold uppercase tracking-[0.18em] text-[#1B0D05] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B98A2C] sm:text-xl sm:tracking-[0.2em]">
            <x-brand-mark class="h-9 w-9 rounded-full ring-1 ring-[#d8c9ad] sm:h-11 sm:w-11" />
            <span class="hidden min-[400px]:inline">ReadArena</span>
        </a>

        <nav aria-label="Main navigation" class="absolute left-1/2 hidden -translate-x-1/2 items-center gap-2 text-sm font-medium lg:flex lg:gap-4">
            <a href="{{ $featuresUrl }}" class="rounded-full px-4 py-2 text-[#786A5D] transition hover:bg-[#f1e7d4] hover:text-[#1B0D05] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B98A2C]">Features</a>
            <a href="{{ $activityUrl }}" class="rounded-full px-4 py-2 text-[#786A5D] transition hover:bg-[#f1e7d4] hover:text-[#1B0D05] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B98A2C]">Activity</a>
            <a href="{{ $libraryUrl }}" @if ($onLibrary) aria-current="page" @endif class="rounded-full px-4 py-2 transition hover:bg-[#f1e7d4] hover:text-[#1B0D05] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B98A2C] {{ $onLibrary ? 'font-semibold text-[#1B0D05]' : 'text-[#786A5D]' }}">Library</a>
            <a href="{{ $proArenaUrl }}" @if ($onProArena) aria-current="page" @endif class="rounded-full px-4 py-2 transition hover:bg-[#f1e7d4] hover:text-[#1B0D05] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B98A2C] {{ $onProArena ? 'font-semibold text-[#1B0D05]' : 'text-[#786A5D]' }}">Pro Arena</a>
        </nav>

        <div class="ml-auto flex shrink-0 items-center gap-3">
            <a href="{{ $appUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-10 items-center justify-center rounded-full bg-[#1B0D05] px-3 text-xs font-semibold text-[#FBF6EA] shadow-sm transition hover:bg-[#432919] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B98A2C] focus-visible:ring-offset-2 sm:min-h-11 sm:px-6 sm:text-sm">Get the App</a>

            <details class="relative lg:hidden">
                <summary class="flex min-h-10 cursor-pointer list-none items-center justify-center rounded-full border border-[#d8c9ad] px-3 text-xs font-semibold text-[#1B0D05] transition hover:bg-[#f1e7d4] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#B98A2C] sm:min-h-11 sm:px-4 sm:text-sm [&::-webkit-details-marker]:hidden">
                    Menu
                </summary>
                <nav aria-label="Mobile navigation" class="absolute right-0 top-[calc(100%+0.75rem)] z-40 grid min-w-52 gap-1 rounded-[20px] border border-[#d8c9ad] bg-[#FBF6EA] p-2 text-sm font-medium shadow-xl">
                    <a href="{{ $featuresUrl }}" class="rounded-xl px-4 py-3 text-[#5e544d] transition hover:bg-[#f1e7d4] hover:text-[#1B0D05]">Features</a>
                    <a href="{{ $activityUrl }}" class="rounded-xl px-4 py-3 text-[#5e544d] transition hover:bg-[#f1e7d4] hover:text-[#1B0D05]">Activity</a>
                    <a href="{{ $libraryUrl }}" class="rounded-xl px-4 py-3 transition hover:bg-[#f1e7d4] hover:text-[#1B0D05] {{ $onLibrary ? 'font-semibold text-[#1B0D05]' : 'text-[#5e544d]' }}">Library</a>
                    <a href="{{ $proArenaUrl }}" class="rounded-xl px-4 py-3 transition hover:bg-[#f1e7d4] hover:text-[#1B0D05] {{ $onProArena ? 'font-semibold text-[#1B0D05]' : 'text-[#5e544d]' }}">Pro Arena</a>
                </nav>
            </details>
        </div>
    </div>
</header>
