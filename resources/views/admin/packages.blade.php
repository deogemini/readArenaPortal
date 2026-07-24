<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Packages | ReadArena</title>
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
            <a href="/admin/users" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Users</a>
            <a href="/admin/books" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Books</a>
            <a href="/admin/quizzes" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Quizzes</a>
            <a href="/admin/duels" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Duels</a>
            <a href="/admin/shows" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Shows</a>
            <a href="/admin/packages" class="block rounded-[14px] bg-[#2B170D] px-4 py-3">Packages</a>
            <a href="/admin/settings" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Settings</a>
        </nav>
    </aside>

    <main class="lg:ml-72">
        <header class="border-b border-[#3d261b] bg-[#1B0D05] px-6 py-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-[#D8A83E]">Monetization setup</p>
                    <h1 class="mt-2 font-serif text-3xl">Global Arena Packages</h1>
                </div>
                <a href="/admin" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Back to dashboard</a>
            </div>
        </header>

        <section class="px-6 py-8 lg:px-8">
            <div class="mx-auto max-w-7xl">
                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-[#3d261b] bg-[#2B170D] px-4 py-3 text-sm text-[#F4EBD8]">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-[#7a2e22] bg-[#2B170D] px-4 py-3 text-sm text-[#f8d2c8]">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="mb-6 rounded-[20px] border border-[#3d261b] bg-[#2B170D] p-6">
                    <h2 class="font-serif text-2xl">Add Package</h2>
                    <form action="{{ route('admin.packages.store') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                        @csrf
                        <input name="name" value="{{ old('name') }}" placeholder="Package name" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input type="number" name="price_tsh" value="{{ old('price_tsh') }}" placeholder="Price in TSH" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input type="number" name="games_count" value="{{ old('games_count') }}" placeholder="Games count" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input name="reward_label" value="{{ old('reward_label') }}" placeholder="Reward label" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <input name="region_scope" value="{{ old('region_scope') }}" placeholder="Region scope" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <select name="status" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                        </select>
                        <div class="md:col-span-2">
                            <button class="rounded-full bg-[#D8A83E] px-6 py-2 text-sm font-semibold text-[#1B0D05]">Create package</button>
                        </div>
                    </form>
                </section>

                <section class="overflow-hidden rounded-[18px] border border-[#3d261b]">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#2B170D]">
                            <tr>
                                <th class="px-4 py-3">Package</th>
                                <th class="px-4 py-3">Price</th>
                                <th class="px-4 py-3">Games</th>
                                <th class="px-4 py-3">Reward</th>
                                <th class="px-4 py-3">Scope</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#1B0D05]">
                            @forelse($packages as $package)
                                <tr class="border-t border-[#3d261b]">
                                    <td class="px-4 py-3">{{ $package->name }}</td>
                                    <td class="px-4 py-3">{{ number_format($package->price_tsh) }} TSH</td>
                                    <td class="px-4 py-3">{{ $package->games_count }}</td>
                                    <td class="px-4 py-3">{{ $package->reward_label ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $package->region_scope }}</td>
                                    <td class="px-4 py-3">{{ ucfirst($package->status) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-[#d8c9ad]">No packages created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </section>

                <div class="mt-4">{{ $packages->links() }}</div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
