<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Shows | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(216,168,62,0.18),_transparent_40%)]">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/90">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <div><p class="text-sm uppercase tracking-[0.35em] text-[#B98A2C]">Reader lounge</p><h1 class="font-serif text-2xl text-[#1B0D05]">Live shows</h1></div>
            <nav class="flex flex-wrap gap-2 text-sm">
                <a href="{{ route('reader.dashboard') }}" class="rounded-full border border-[#d8c9ad] px-4 py-2">Dashboard</a>
                <a href="{{ route('reader.library') }}" class="rounded-full border border-[#d8c9ad] px-4 py-2">Library</a>
                <a href="{{ route('reader.shows') }}" class="rounded-full bg-[#1B0D05] px-4 py-2 text-[#FBF6EA]">Shows</a>
            </nav>
        </div>
    </header>
    <main class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))<div class="rounded-xl border border-[#d8c9ad] bg-[#FBF6EA] px-4 py-3 text-sm">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="rounded-xl border border-[#c17b6f] bg-[#FBF6EA] px-4 py-3 text-sm text-[#7a2e22]"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <section class="rounded-[28px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 shadow-sm sm:p-8">
            <p class="text-xs uppercase tracking-[0.3em] text-[#B98A2C]">Community calendar</p><h2 class="mt-2 font-serif text-3xl text-[#1B0D05]">Upcoming sessions</h2>
            <div class="mt-6 space-y-4">
                @forelse ($shows as $show)
                    @php($application = $applications->get($show->id))
                    @php($isRsvped = in_array($show->id, $rsvpIds))
                    <article class="rounded-[20px] border border-[#d8c9ad] bg-[#F4EBD8] p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="font-serif text-2xl text-[#1B0D05]">{{ $show->title }}</h3>
                                <p class="mt-1 text-sm text-[#5e544d]">{{ $show->start_at->format('M j, Y · g:i A') }}</p>
                                @if ($show->book)<p class="mt-1 text-sm text-[#786A5D]">Book: {{ $show->book->title }}</p>@endif
                                @if ($show->description)<p class="mt-3 max-w-3xl text-sm leading-6 text-[#5e544d]">{{ $show->description }}</p>@endif
                                <p class="mt-3 text-xs text-[#786A5D]">{{ $show->rsvps_count }} reader(s) registered</p>
                            </div>
                            <div class="shrink-0">
                                @if ($isRsvped)
                                    <form action="{{ route('reader.shows.rsvp.destroy', $show) }}" method="POST">@csrf @method('DELETE')<button class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Cancel RSVP</button></form>
                                @else
                                    <form action="{{ route('reader.shows.rsvp.store', $show) }}" method="POST">@csrf<button class="rounded-full bg-[#1B0D05] px-4 py-2 text-sm font-semibold text-[#FBF6EA]">RSVP</button></form>
                                @endif
                            </div>
                        </div>
                        @if ($show->book)
                            <div class="mt-5 border-t border-[#d8c9ad] pt-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h4 class="font-semibold text-[#1B0D05]">Apply to join as a guest</h4>
                                    @if ($application)<span class="rounded-full border border-[#d8c9ad] px-3 py-1 text-xs uppercase">{{ $application->status }} · quiz score {{ $application->quiz_score }}%</span>@endif
                                </div>
                                @if ($application && $application->status === 'pending')
                                    <p class="mt-2 text-sm text-[#786A5D]">Your application is waiting for review.</p>
                                    <form action="{{ route('reader.show-applications.destroy', $application) }}" method="POST" class="mt-3">@csrf @method('DELETE')<button class="rounded-full border border-[#c17b6f] px-4 py-2 text-sm text-[#7a2e22]">Withdraw application</button></form>
                                @elseif ($application && $application->status === 'approved')
                                    <p class="mt-2 text-sm text-[#2F7D4A]">Your guest application has been approved.</p>
                                @else
                                    <form action="{{ route('reader.shows.applications.store', $show) }}" method="POST" class="mt-3 grid gap-3 sm:grid-cols-[1fr_auto]">@csrf<textarea name="motivation" rows="3" minlength="20" maxlength="2000" required placeholder="Why would you like to join this discussion?" class="rounded-xl border border-[#d8c9ad] bg-[#FBF6EA] px-4 py-3 text-sm"></textarea><button class="self-end rounded-full border border-[#d8c9ad] px-4 py-2 text-sm font-semibold">Submit application</button></form>
                                    <p class="mt-2 text-xs text-[#786A5D]">Guest applications require a passing quiz for the related book.</p>
                                @endif
                            </div>
                        @endif
                    </article>
                @empty
                    <p class="rounded-[18px] border border-dashed border-[#d8c9ad] p-5 text-sm text-[#5e544d]">No future shows are scheduled yet.</p>
                @endforelse
            </div>
        </section>
    </main>
</div>
</body>
</html>
