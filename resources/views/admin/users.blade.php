<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#1B0D05] text-[#F4EBD8]">
<div class="min-h-screen">
    <aside class="fixed inset-y-0 left-0 hidden w-72 border-r border-[#3d261b] bg-[#130804] p-6 lg:block">
        <div class="flex items-center gap-3 text-xl font-semibold uppercase tracking-[0.2em]">
            <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#d8c9ad] bg-[#F4EBD8] text-sm text-[#1B0D05]">BD</span>
            <span>ReadArena Admin</span>
        </div>
        <nav class="mt-8 space-y-2 text-sm text-[#d8c9ad]">
            <a href="/admin" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Dashboard</a>
            <a href="/admin/users" class="block rounded-[14px] bg-[#2B170D] px-4 py-3">Users</a>
            <a href="/admin/books" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Books</a>
            <a href="/admin/quizzes" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Quizzes</a>
            <a href="/admin/duels" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Duels</a>
            <a href="/admin/shows" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Shows</a>
            <a href="/admin/packages" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Packages</a>
            <a href="/admin/settings" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Settings</a>
        </nav>
    </aside>

    <main class="lg:ml-72">
        <header class="border-b border-[#3d261b] bg-[#1B0D05] px-6 py-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-[#D8A83E]">Member management</p>
                    <h1 class="mt-2 font-serif text-3xl">Users by Role</h1>
                </div>
                <a href="/admin" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Back to dashboard</a>
            </div>
        </header>

        <section class="px-6 py-8 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <form method="GET" action="{{ route('admin.users') }}" class="flex flex-wrap gap-3">
                    <select name="role" class="rounded-xl border border-[#3d261b] bg-[#2B170D] px-4 py-2">
                        <option value="">All roles</option>
                        <option value="reader" @selected($selectedRole === 'reader')>Readers</option>
                        <option value="author" @selected($selectedRole === 'author')>Authors</option>
                        <option value="admin" @selected($selectedRole === 'admin')>Admins</option>
                    </select>
                    <select name="activity" class="rounded-xl border border-[#3d261b] bg-[#2B170D] px-4 py-2">
                        <option value="" @selected($selectedActivity === '')>All activity states</option>
                        <option value="active" @selected($selectedActivity === 'active')>Has ongoing activity</option>
                        <option value="inactive" @selected($selectedActivity === 'inactive')>No ongoing activity</option>
                    </select>
                    <button class="rounded-full bg-[#D8A83E] px-5 py-2 text-sm font-semibold text-[#1B0D05]">Filter</button>
                </form>

                <div class="mt-6 overflow-hidden rounded-[18px] border border-[#3d261b]">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#2B170D]">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Verified</th>
                                <th class="px-4 py-3">Ongoing Activities</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#1B0D05]">
                            @forelse($users as $user)
                                <tr class="border-t border-[#3d261b]">
                                    <td class="px-4 py-3">{{ $user->name }}</td>
                                    <td class="px-4 py-3">{{ $user->email }}</td>
                                    <td class="px-4 py-3">{{ ucfirst($user->role ?? 'reader') }}</td>
                                    <td class="px-4 py-3">{{ $user->email_verified_at ? 'Yes' : 'No' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold">{{ $user->ongoing_activities_count }}</span>
                                        <span class="ml-1 text-xs text-[#d8c9ad]">(Goals: {{ $user->active_goals_count }}, Duels: {{ (int) $user->active_challenger_duels_count + (int) $user->active_opponent_duels_count }})</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-[#d8c9ad]">No users found for this role.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $users->links() }}</div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
