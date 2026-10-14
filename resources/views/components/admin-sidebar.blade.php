@php
    $links = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => ['admin.dashboard']],
        ['label' => 'Users', 'route' => 'admin.users', 'active' => ['admin.users*']],
        ['label' => 'Books', 'route' => 'admin.books', 'active' => ['admin.books*']],
        ['label' => 'Quizzes', 'route' => 'admin.quizzes', 'active' => ['admin.quizzes*', 'admin.quiz-questions*', 'admin.quiz-attempts*']],
        ['label' => 'Duels', 'route' => 'admin.duels', 'active' => ['admin.duels*']],
        ['label' => 'Shows', 'route' => 'admin.shows', 'active' => ['admin.shows*', 'admin.show-applications*']],
        ['label' => 'Review moderation', 'route' => 'admin.reviews', 'active' => ['admin.reviews*']],
        ['label' => 'Reader ideas', 'route' => 'admin.reader-ideas', 'active' => ['admin.reader-ideas*']],
        ['label' => 'Packages', 'route' => 'admin.packages', 'active' => ['admin.packages*']],
        ['label' => 'Settings', 'route' => 'admin.settings', 'active' => ['admin.settings*']],
    ];
@endphp

<aside class="fixed inset-y-0 left-0 z-30 hidden w-72 overflow-y-auto border-r border-[#3d261b] bg-[#130804] p-6 lg:block" aria-label="Admin sidebar">
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 text-xl font-semibold uppercase tracking-[0.2em]">
        <x-brand-mark class="h-10 w-10 shrink-0 rounded-xl ring-1 ring-[#d8c9ad]" />
        <span>ReadArena Admin</span>
    </a>
    <nav class="mt-8 space-y-2 text-sm text-[#d8c9ad]" aria-label="Admin pages">
        @include('components.admin-navigation-links', ['links' => $links])
    </nav>
</aside>

<div class="sticky top-0 z-40 border-b border-[#3d261b] bg-[#130804] p-3 lg:hidden">
    <details class="group">
        <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold uppercase tracking-[0.16em] text-[#F4EBD8] focus:outline-none focus:ring-2 focus:ring-[#D8A83E]">
            <x-brand-mark class="h-9 w-9 rounded-lg ring-1 ring-[#d8c9ad]" />
            <span>ReadArena Admin</span>
            <span class="ml-auto text-xs tracking-normal text-[#D8A83E]">Menu</span>
        </summary>
        <nav class="absolute inset-x-0 top-full max-h-[calc(100vh-4rem)] space-y-1 overflow-y-auto border-b border-[#3d261b] bg-[#130804] p-3 shadow-xl" aria-label="Admin pages">
            @include('components.admin-navigation-links', ['links' => $links])
        </nav>
    </details>
</div>
