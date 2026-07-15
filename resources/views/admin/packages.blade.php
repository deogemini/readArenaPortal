<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Packages | BookDuel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#1B0D05] text-[#F4EBD8]">
<div class="min-h-screen px-6 py-8 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="flex items-center justify-between">
            <h1 class="font-serif text-4xl">Global Arena Packages</h1>
            <a href="/admin" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Back to dashboard</a>
        </div>

        <section class="mt-6 rounded-[20px] border border-[#3d261b] bg-[#2B170D] p-6">
            <h2 class="font-serif text-2xl">Add Package</h2>
            <form action="{{ route('admin.packages.store') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <input name="name" placeholder="Package name" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                <input type="number" name="price_tsh" placeholder="Price in TSH" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                <input type="number" name="games_count" placeholder="Games count" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                <input name="reward_label" placeholder="Reward label" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                <input name="region_scope" placeholder="Region scope" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                <select name="status" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <div class="md:col-span-2">
                    <button class="rounded-full bg-[#D8A83E] px-6 py-2 text-sm font-semibold text-[#1B0D05]">Create package</button>
                </div>
            </form>
        </section>

        <section class="mt-6 overflow-hidden rounded-[18px] border border-[#3d261b]">
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
</div>
</body>
</html>
