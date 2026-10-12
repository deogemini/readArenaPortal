@php
    $readerNavigation = [
        ['label' => 'Dashboard', 'route' => 'reader.dashboard', 'active' => ['reader.dashboard']],
        ['label' => 'Library', 'route' => 'reader.library', 'active' => ['reader.library', 'reader.books.*']],
        ['label' => 'Goals', 'route' => 'reader.goals', 'active' => ['reader.goals']],
        ['label' => 'Lessons', 'route' => 'reader.lessons', 'active' => ['reader.lessons']],
        ['label' => 'Recommendations', 'route' => 'reader.recommendations', 'active' => ['reader.recommendations']],
        ['label' => 'Shows', 'route' => 'reader.shows', 'active' => ['reader.shows']],
        ['label' => 'Duels', 'route' => 'reader.duels', 'active' => ['reader.duels']],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-[#d8c9ad] bg-[#FBF6EA]/95 shadow-sm backdrop-blur">
    <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#B98A2C]">Reader lounge</p>
            <h1 class="mt-1 font-serif text-2xl text-[#1B0D05]">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-[#786A5D]">{{ auth()->user()->email }}</p>
        </div>

        <nav aria-label="Reader sections" class="flex flex-wrap items-center gap-2">
            @foreach ($readerNavigation as $item)
                @php($isActive = request()->routeIs(...$item['active']))
                <a href="{{ route($item['route']) }}"
                   @if ($isActive) aria-current="page" @endif
                   class="inline-flex shrink-0 items-center rounded-full border px-4 py-2.5 text-sm font-medium transition {{ $isActive ? 'border-[#1B0D05] bg-[#1B0D05] text-[#FBF6EA]' : 'border-[#d8c9ad] bg-[#F4EBD8] text-[#1B0D05] hover:border-[#B98A2C] hover:bg-white' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach

            <a href="https://play.google.com/store/apps/details?id=tz.co.eportsolutions.readarena"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex shrink-0 items-center rounded-full border border-[#B98A2C] px-4 py-2.5 text-sm font-semibold text-[#5c3b08] transition hover:bg-white">
                Download Android app
            </a>
            @include('components.portal-logout')
        </nav>
    </div>
</header>
