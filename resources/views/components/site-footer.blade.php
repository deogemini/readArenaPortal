@props(['theme' => 'light'])

@php
    $isDark = $theme === 'dark';
    $footerClass = $isDark ? 'border-[#3d261b] bg-[#130804] text-[#d8c9ad]' : 'border-[#d8c9ad] bg-[#FBF6EA] text-[#5e544d]';
    $headingClass = $isDark ? 'text-[#F4EBD8]' : 'text-[#1B0D05]';
    $linkClass = $isDark ? 'text-[#d8c9ad] hover:text-[#F4EBD8]' : 'text-[#5e544d] hover:text-[#1B0D05]';
    $mutedClass = $isDark ? 'text-[#a99378]' : 'text-[#786A5D]';
    $markClass = $isDark ? 'ring-[#3d261b]' : 'ring-[#d8c9ad]';
@endphp

<footer class="mt-auto border-t {{ $footerClass }}">
    <div class="mx-auto grid max-w-7xl gap-8 px-6 py-10 sm:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1fr] lg:px-8">
        <div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 text-lg font-semibold uppercase tracking-[0.18em] {{ $headingClass }}">
                <x-brand-mark class="h-10 w-10 rounded-xl ring-1 {{ $markClass }}" />
                <span>ReadArena</span>
            </a>
            <p class="mt-4 max-w-sm text-sm leading-7 {{ $mutedClass }}">A reading community for thoughtful discovery, verified progress, and friendly competition.</p>
            <a href="https://play.google.com/store/apps/details?id=tz.co.eportsolutions.readarena" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex text-sm font-semibold underline underline-offset-4 {{ $linkClass }}">Get the Android app</a>
        </div>

        <nav aria-label="Explore ReadArena">
            <h2 class="text-xs font-semibold uppercase tracking-[0.2em] {{ $headingClass }}">Explore</h2>
            <ul class="mt-4 space-y-3 text-sm">
                <li><a href="{{ route('library') }}" class="transition {{ $linkClass }}">Library</a></li>
                <li><a href="{{ route('leaderboard') }}" class="transition {{ $linkClass }}">Leaderboard</a></li>
                <li><a href="{{ route('pro-arena') }}" class="transition {{ $linkClass }}">Pro Arena</a></li>
                <li><a href="{{ route('about') }}" class="transition {{ $linkClass }}">About ReadArena</a></li>
            </ul>
        </nav>

        <nav aria-label="Account and policies">
            <h2 class="text-xs font-semibold uppercase tracking-[0.2em] {{ $headingClass }}">Account &amp; policies</h2>
            <ul class="mt-4 space-y-3 text-sm">
                <li><a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="transition {{ $linkClass }}">{{ auth()->check() ? 'Your portal' : 'Create an account' }}</a></li>
                @guest
                    <li><a href="{{ route('login') }}" class="transition {{ $linkClass }}">Sign in</a></li>
                @endguest
                <li><a href="{{ route('privacy-policy') }}" class="transition {{ $linkClass }}">Privacy Policy</a></li>
                <li><a href="{{ route('account-deletion') }}" class="transition {{ $linkClass }}">Account and data deletion</a></li>
            </ul>
        </nav>

        <div>
            <h2 class="text-xs font-semibold uppercase tracking-[0.2em] {{ $headingClass }}">Contact</h2>
            <p class="mt-4 text-sm leading-6 {{ $mutedClass }}">For help with your account, privacy, or the ReadArena service:</p>
            <a href="mailto:info@eportsolutions.co.tz" class="mt-2 inline-flex text-sm font-semibold underline underline-offset-4 {{ $linkClass }}">info@eportsolutions.co.tz</a>
        </div>
    </div>

    <div class="border-t {{ $isDark ? 'border-[#3d261b]' : 'border-[#e5d8bf]' }}">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-6 py-4 text-xs sm:flex-row sm:items-center sm:justify-between lg:px-8">
            <p class="{{ $mutedClass }}">&copy; {{ now()->year }} ReadArena. All rights reserved.</p>
            <a href="https://www.instagram.com/arenayakusomavitabu/" target="_blank" rel="noopener noreferrer" class="transition {{ $linkClass }}">Follow ReadArena on Instagram</a>
        </div>
    </div>
</footer>
