<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('components.site-icons')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Request deletion of your ReadArena account or selected personal data, even when you no longer have the app installed.">
    <title>Account and Data Deletion | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D] antialiased min-h-screen flex flex-col">
@include('components.language-switcher')
<div class="flex flex-1 flex-col bg-[radial-gradient(circle_at_top_left,_rgba(216,168,62,0.16),_transparent_38%)]">
    <x-public-navigation />

    <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-16 lg:px-8">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-medium text-[#786A5D] transition hover:text-[#1B0D05]"><span aria-hidden="true">&larr;</span> Back to ReadArena</a>

        <section class="mt-5 overflow-hidden rounded-[28px] border border-[#dfcfad] bg-[#FBF6EA] shadow-[0_16px_50px_rgba(60,37,17,0.08)]">
            <div class="bg-[linear-gradient(120deg,rgba(251,246,234,0.98),rgba(244,235,216,0.9))] p-6 sm:p-10 lg:p-12">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#A77920]">ReadArena account controls</p>
                <h1 class="mt-3 max-w-3xl font-serif text-4xl leading-tight text-[#1B0D05] sm:text-5xl">Request account and data deletion</h1>
                <p class="mt-5 max-w-3xl text-base leading-7 text-[#5e544d]">You can request deletion of your ReadArena account and its associated personal data here, even if you have uninstalled the Android app. You can also request deletion of specific personal data while keeping your account.</p>

                <div class="mt-8 grid gap-3 sm:grid-cols-2">
                    <a href="mailto:info@eportsolutions.co.tz?subject=ReadArena%20account%20deletion%20request&amp;body=Please%20delete%20my%20ReadArena%20account%20and%20associated%20personal%20data.%0AAccount%20email%3A%20%0AUsername%20(optional)%3A%20" class="flex min-h-16 items-center justify-center rounded-full bg-[#1B0D05] px-6 py-3 text-center font-semibold text-[#FBF6EA] transition hover:bg-[#432919] focus:outline-none focus:ring-2 focus:ring-[#A77920] focus:ring-offset-2">
                        Request account deletion
                    </a>
                    <a href="mailto:info@eportsolutions.co.tz?subject=ReadArena%20data%20deletion%20request&amp;body=Please%20delete%20the%20following%20personal%20data%20from%20my%20ReadArena%20account%20and%20keep%20my%20account%20active%3A%0AData%20to%20delete%3A%20%0AAccount%20email%3A%20%0AUsername%20(optional)%3A%20" class="flex min-h-16 items-center justify-center rounded-full border border-[#cbb992] bg-[#FBF6EA] px-6 py-3 text-center font-semibold text-[#5c3b08] transition hover:bg-[#f5eddf] focus:outline-none focus:ring-2 focus:ring-[#A77920] focus:ring-offset-2">
                        Request deletion of specific data
                    </a>
                </div>
                <p class="mt-4 max-w-3xl text-sm leading-6 text-[#786A5D]">For either request, send the email from the address linked to your ReadArena account and include that account email. For a data-only request, describe which data you want removed; your account will remain active. You may include your username if you have one. Do not send your password. We may verify your identity before completing a request.</p>
            </div>

            <div class="grid gap-4 border-t border-[#e5d8bf] bg-[#f5eddf] p-6 sm:grid-cols-2 sm:p-8">
                <section class="rounded-2xl border border-[#dfcfad] bg-[#FBF6EA] p-5">
                    <h2 class="font-serif text-xl text-[#1B0D05]">What will be deleted</h2>
                    <p class="mt-2 text-sm leading-6 text-[#5e544d]">When a verified request is completed, we delete your account and associated personal records, such as profile details and photo, reading activity, quiz participation and scores, duels, applications, submitted ideas and attachments, notifications, and app access tokens.</p>
                </section>
                <section class="rounded-2xl border border-[#dfcfad] bg-[#FBF6EA] p-5">
                    <h2 class="font-serif text-xl text-[#1B0D05]">Limited retention</h2>
                    <p class="mt-2 text-sm leading-6 text-[#5e544d]">Information required for legal obligations, security, or dispute resolution may be retained where necessary. Backup copies and technical logs may remain until their normal retention period ends.</p>
                </section>
            </div>

            @auth
                <div class="border-t border-[#e5d8bf] px-6 py-5 sm:px-8">
                    <p class="text-sm leading-6 text-[#5e544d]">If you are signed in, you can delete your account directly from your profile settings.</p>
                    <a href="{{ route('profile.edit') }}" class="mt-2 inline-flex font-semibold text-[#6c4b11] underline decoration-[#c6a566] underline-offset-4">Go to profile settings</a>
                </div>
            @endauth
        </section>

    </main>
    <x-site-footer />
</div>
</body>
</html>
