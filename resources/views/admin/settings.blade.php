<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings | ReadArena</title>
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
            <a href="/admin/packages" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Packages</a>
            <a href="/admin/settings" class="block rounded-[14px] bg-[#2B170D] px-4 py-3">Settings</a>
        </nav>
    </aside>

    <main class="lg:ml-72">
        <header class="border-b border-[#3d261b] bg-[#1B0D05] px-6 py-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-[#D8A83E]">Platform configuration</p>
                    <h1 class="mt-2 font-serif text-3xl">Settings</h1>
                </div>
                <a href="/admin" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Back to dashboard</a>
            </div>
        </header>

        <section class="px-6 py-8 lg:px-8">
            <div class="mx-auto max-w-7xl space-y-6">
                @if (session('status'))
                    <div class="rounded-xl border border-[#3d261b] bg-[#2B170D] px-4 py-3 text-sm text-[#F4EBD8]">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-xl border border-[#7a2e22] bg-[#2B170D] px-4 py-3 text-sm text-[#f8d2c8]">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="rounded-[18px] border border-[#3d261b] bg-[#2B170D] p-6">
                    <h2 class="font-serif text-2xl">SMS Gateway</h2>
                    <p class="mt-2 text-sm text-[#d8c9ad]">Configure Flex SMS credentials and enable/disable gateway alerts.</p>

                    <form action="{{ route('admin.settings.sms-gateway.update') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                        @csrf
                        <input name="base_url" value="{{ old('base_url', $smsGatewaySetting->base_url ?? config('services.flex_sms.base_url')) }}" placeholder="Base URL" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input name="sender_id" value="{{ old('sender_id', $smsGatewaySetting->sender_id ?? config('services.flex_sms.sender_id')) }}" placeholder="Sender ID" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input name="client_id" value="{{ old('client_id', $smsGatewaySetting->client_id ?? config('services.flex_sms.client_id')) }}" placeholder="Client ID" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input name="client_secret" value="{{ old('client_secret', $smsGatewaySetting->client_secret ?? config('services.flex_sms.client_secret')) }}" placeholder="Client Secret" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <label class="md:col-span-2 flex items-center gap-2 rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2 text-sm">
                            <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $smsGatewaySetting->is_enabled ?? false))>
                            Enable SMS gateway
                        </label>
                        <div class="md:col-span-2">
                            <button class="rounded-full bg-[#D8A83E] px-6 py-2 text-sm font-semibold text-[#1B0D05]">Save SMS settings</button>
                        </div>
                    </form>
                </section>

                <section class="rounded-[18px] border border-[#3d261b] bg-[#2B170D] p-6">
                    <h2 class="font-serif text-2xl">Delay Alert Trigger</h2>
                    <p class="mt-2 text-sm text-[#d8c9ad]">When car travel exceeds allocated time, notify all admin-role users via SMS.</p>

                    <form action="{{ route('admin.settings.sms-gateway.delay-alert') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-3">
                        @csrf
                        <input name="car_label" value="{{ old('car_label', 'CAR-01') }}" placeholder="Car label" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input type="number" min="1" name="allocated_minutes" value="{{ old('allocated_minutes', 30) }}" placeholder="Allocated minutes" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input type="number" min="1" name="elapsed_minutes" value="{{ old('elapsed_minutes', 45) }}" placeholder="Elapsed minutes" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <div class="md:col-span-3">
                            <button class="rounded-full border border-[#D8A83E] px-6 py-2 text-sm font-semibold text-[#F4EBD8]">Send Delay Alert</button>
                        </div>
                    </form>
                </section>

                <section class="overflow-hidden rounded-[18px] border border-[#3d261b]">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#2B170D]">
                            <tr>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Users</th>
                                <th class="px-4 py-3">Purpose</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#1B0D05]">
                            @foreach($roleSummaries as $roleSummary)
                                <tr class="border-t border-[#3d261b]">
                                    <td class="px-4 py-3">{{ ucfirst($roleSummary['role']) }}</td>
                                    <td class="px-4 py-3">{{ $roleSummary['count'] }}</td>
                                    <td class="px-4 py-3">
                                        {{ $roleSummary['role'] === 'admin' ? 'Controls platform operations.' : ($roleSummary['role'] === 'author' ? 'Publishes books and creates quizzes.' : 'Competes and tracks reading goals.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>

                <section class="overflow-hidden rounded-[18px] border border-[#3d261b]">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#2B170D]">
                            <tr>
                                <th class="px-4 py-3">Key</th>
                                <th class="px-4 py-3">Value</th>
                                <th class="px-4 py-3">Type</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#1B0D05]">
                            @forelse($settings as $setting)
                                <tr class="border-t border-[#3d261b]">
                                    <td class="px-4 py-3">{{ $setting->key }}</td>
                                    <td class="px-4 py-3">{{ $setting->value }}</td>
                                    <td class="px-4 py-3">{{ $setting->type }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-[#d8c9ad]">No settings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </section>
            </div>
        </section>
    </main>
</div>
</body>
</html>
