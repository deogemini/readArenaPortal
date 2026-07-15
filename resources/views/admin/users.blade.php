<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users | BookDuel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#1B0D05] text-[#F4EBD8]">
<div class="min-h-screen px-6 py-8 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="flex items-center justify-between">
            <h1 class="font-serif text-4xl">Users by Role</h1>
            <a href="/admin" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Back to dashboard</a>
        </div>

        <form method="GET" action="{{ route('admin.users') }}" class="mt-6 flex flex-wrap gap-3">
            <select name="role" class="rounded-xl border border-[#3d261b] bg-[#2B170D] px-4 py-2">
                <option value="">All roles</option>
                <option value="reader" @selected($selectedRole === 'reader')>Readers</option>
                <option value="author" @selected($selectedRole === 'author')>Authors</option>
                <option value="admin" @selected($selectedRole === 'admin')>Admins</option>
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
                    </tr>
                </thead>
                <tbody class="bg-[#1B0D05]">
                    @forelse($users as $user)
                        <tr class="border-t border-[#3d261b]">
                            <td class="px-4 py-3">{{ $user->name }}</td>
                            <td class="px-4 py-3">{{ $user->email }}</td>
                            <td class="px-4 py-3">{{ ucfirst($user->role ?? 'reader') }}</td>
                            <td class="px-4 py-3">{{ $user->email_verified_at ? 'Yes' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-[#d8c9ad]">No users found for this role.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </div>
</div>
</body>
</html>
