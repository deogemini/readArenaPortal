<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Duels | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#1B0D05] text-[#F4EBD8]">
<div class="min-h-screen">
    <aside class="fixed inset-y-0 left-0 hidden w-72 border-r border-[#3d261b] bg-[#130804] p-6 lg:block">
        <div class="flex items-center gap-3 text-xl font-semibold uppercase tracking-[0.2em]"><span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#d8c9ad] bg-[#F4EBD8] text-sm text-[#1B0D05]">RA</span><span>ReadArena Admin</span></div>
        <nav class="mt-8 space-y-2 text-sm text-[#d8c9ad]">
            <a href="{{ route('admin.dashboard') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Dashboard</a>
            <a href="{{ route('admin.users') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Users</a>
            <a href="{{ route('admin.books') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Books</a>
            <a href="{{ route('admin.quizzes') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Quizzes</a>
            <a href="{{ route('admin.duels') }}" class="block rounded-[14px] bg-[#2B170D] px-4 py-3">Duels</a>
            <a href="{{ route('admin.shows') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Shows</a>
            <a href="{{ route('admin.packages') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Packages</a>
            <a href="{{ route('admin.settings') }}" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Settings</a>
        </nav>
    </aside>
    <main class="lg:ml-72">
        <header class="border-b border-[#3d261b] bg-[#1B0D05] px-6 py-6 lg:px-8">
            <div class="flex items-center justify-between"><div><p class="text-sm uppercase tracking-[0.3em] text-[#D8A83E]">Competition control</p><h1 class="mt-2 font-serif text-3xl">Duel invitations</h1></div><a href="{{ route('admin.dashboard') }}" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Back to dashboard</a></div>
        </header>
        <section class="px-6 py-8 lg:px-8">
            <div class="mx-auto max-w-7xl">
                @if (session('status'))<div class="mb-5 rounded-xl border border-[#3d261b] bg-[#2B170D] px-4 py-3 text-sm">{{ session('status') }}</div>@endif
                @if ($errors->any())<div class="mb-5 rounded-xl border border-[#7a2e22] bg-[#2B170D] px-4 py-3 text-sm text-[#f8d2c8]">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
                <div class="mb-6 grid gap-4 sm:grid-cols-3">
                    @foreach (['pending' => 'Pending', 'accepted' => 'Accepted', 'live' => 'Live'] as $status => $label)
                        <div class="rounded-[18px] border border-[#3d261b] bg-[#2B170D] p-5"><p class="text-xs uppercase tracking-[0.2em] text-[#D8A83E]">{{ $label }}</p><p class="mt-2 font-serif text-3xl">{{ $duels->getCollection()->where('status', $status)->count() }}</p></div>
                    @endforeach
                </div>
                <div class="overflow-hidden rounded-[20px] border border-[#3d261b] bg-[#2B170D]">
                    <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="border-b border-[#3d261b] text-xs uppercase tracking-wider text-[#D8A83E]"><tr><th class="px-5 py-4">Book</th><th class="px-5 py-4">Challenger</th><th class="px-5 py-4">Opponent</th><th class="px-5 py-4">Created</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Moderation</th></tr></thead>
                        <tbody class="divide-y divide-[#3d261b]">
                            @forelse ($duels as $duel)
                                <tr>
                                    <td class="px-5 py-4 font-semibold">{{ $duel->book?->title ?? 'Book removed' }}</td>
                                    <td class="px-5 py-4">{{ $duel->challenger?->name ?? 'Account removed' }}<span class="block text-xs text-[#d8c9ad]">{{ $duel->challenger?->email }}</span></td>
                                    <td class="px-5 py-4">{{ $duel->opponent?->name ?? 'Account removed' }}<span class="block text-xs text-[#d8c9ad]">{{ $duel->opponent?->email }}</span></td>
                                    <td class="px-5 py-4">{{ $duel->created_at->format('M j, Y') }}</td>
                                    <td class="px-5 py-4"><span class="rounded-full border border-[#70533e] px-3 py-1 text-xs uppercase">{{ $duel->status }}</span></td>
                                    <td class="px-5 py-4">
                                        @if (!in_array($duel->status, ['completed', 'rejected', 'cancelled'], true))
                                            <div class="flex gap-2">
                                                @foreach (['cancelled' => 'Cancel', 'disputed' => 'Flag dispute'] as $status => $label)
                                                    <form action="{{ route('admin.duels.update', $duel) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $status }}"><button class="rounded-full border border-[#70533e] px-3 py-1.5 text-xs hover:bg-[#1B0D05]">{{ $label }}</button></form>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-[#d8c9ad]">Closed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-10 text-center text-[#d8c9ad]">No duel invitations have been created.</td></tr>
                            @endforelse
                        </tbody>
                    </table></div>
                    <div class="border-t border-[#3d261b] px-5 py-4">{{ $duels->links() }}</div>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
