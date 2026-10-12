@props([
    'title' => 'Account recovery | ReadArena',
    'eyebrow' => 'Account recovery',
    'headline' => 'Your reading journey is waiting.',
    'description' => 'Recover your account securely and return to your books, progress, and reader community.',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('components.site-icons')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
@include('components.language-switcher')
<div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(216,168,62,0.18),_transparent_35%)]">
    <div class="mx-auto grid min-h-screen max-w-7xl items-center gap-10 px-6 py-10 lg:grid-cols-[1.05fr_0.95fr] lg:px-8">
        <section class="rounded-[32px] border border-[#d8c9ad] bg-[#1B0D05] p-8 text-[#F4EBD8] shadow-xl lg:p-12">
            <a href="/" class="inline-flex items-center gap-3 text-xl font-semibold uppercase tracking-[0.2em]">
                <x-brand-mark class="h-10 w-10 rounded-xl ring-1 ring-[#d8c9ad]" />
                <span>ReadArena</span>
            </a>
            <p class="mt-10 text-sm uppercase tracking-[0.35em] text-[#D8A83E]">{{ $eyebrow }}</p>
            <h1 class="mt-4 font-serif text-5xl leading-tight">{{ $headline }}</h1>
            <p class="mt-6 max-w-xl text-base leading-8 text-[#e5d7bf]">{{ $description }}</p>

            <div class="mt-10 space-y-4">
                <div class="rounded-[22px] border border-[#3d261b] bg-[#2B170D] p-5">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#D8A83E]">Secure recovery</p>
                    <p class="mt-2 text-sm leading-6 text-[#e5d7bf]">We’ll send a private reset link to the email address on your account.</p>
                </div>
                <div class="rounded-[22px] border border-[#3d261b] bg-[#2B170D] p-5">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#D8A83E]">One account</p>
                    <p class="mt-2 text-sm leading-6 text-[#e5d7bf]">Use your ReadArena account on the website and Android app.</p>
                </div>
            </div>
        </section>

        <section class="rounded-[32px] border border-[#d8c9ad] bg-[#FBF6EA] p-8 shadow-lg lg:p-10">
            {{ $slot }}
        </section>
    </div>
</div>
</body>
</html>
