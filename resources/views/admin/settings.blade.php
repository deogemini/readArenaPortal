<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#1B0D05] text-[#F4EBD8]">
@include('components.language-switcher')
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
                <div class="flex flex-wrap items-center gap-2"><a href="/admin" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Back to dashboard</a>@include('components.portal-logout', ['theme' => 'dark'])</div>
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
                    <h2 class="font-serif text-2xl">Notification Delivery</h2>
                    <p class="mt-2 text-sm text-[#d8c9ad]">Configure outbound email and Firebase Cloud Messaging. Credentials are encrypted in the database and secret fields stay blank after saving.</p>

                    <form action="{{ route('admin.settings.notification-channels.update') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                        @csrf
                        <label class="md:col-span-2 flex items-center gap-2 rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-3 text-sm">
                            <input type="checkbox" name="email_enabled" value="1" @checked(old('email_enabled', $notificationChannelSetting->email_enabled ?? false))>
                            Enable email notifications
                        </label>
                        <input name="smtp_host" value="{{ old('smtp_host', $notificationChannelSetting->smtp_host ?? 'mail.eportsolutions.co.tz') }}" placeholder="SMTP host" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <input type="number" name="smtp_port" value="{{ old('smtp_port', $notificationChannelSetting->smtp_port ?? 587) }}" placeholder="SMTP port" min="1" max="65535" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <input name="smtp_username" value="{{ old('smtp_username', $notificationChannelSetting->smtp_username ?? 'info@eportsolutions.co.tz') }}" placeholder="SMTP username" autocomplete="off" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <input type="password" name="smtp_password" placeholder="{{ $notificationChannelSetting?->smtp_password ? 'Saved; leave blank to keep current password' : 'SMTP password' }}" autocomplete="new-password" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <select name="smtp_encryption" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                            @foreach(['tls' => 'TLS / STARTTLS', 'ssl' => 'SSL', 'none' => 'No encryption'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('smtp_encryption', $notificationChannelSetting->smtp_encryption ?? 'tls') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $notificationChannelSetting->mail_from_address ?? 'info@eportsolutions.co.tz') }}" placeholder="From email address" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <input name="mail_from_name" value="{{ old('mail_from_name', $notificationChannelSetting->mail_from_name ?? 'READ ARENA') }}" placeholder="From name" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        @if($notificationChannelSetting?->smtp_password)
                            <label class="md:col-span-2 flex items-center gap-2 text-sm text-[#d8c9ad]"><input type="checkbox" name="clear_smtp_password" value="1"> Clear saved SMTP password</label>
                        @endif

                        <label class="md:col-span-2 mt-3 flex items-center gap-2 rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-3 text-sm">
                            <input type="checkbox" name="push_enabled" value="1" @checked(old('push_enabled', $notificationChannelSetting->push_enabled ?? false))>
                            Enable push notifications
                        </label>
                        <input name="firebase_project_id" value="{{ old('firebase_project_id', $notificationChannelSetting->firebase_project_id ?? '') }}" placeholder="Firebase project ID" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <p class="self-center text-sm text-[#d8c9ad]">{{ $notificationChannelSetting?->firebase_service_account_json ? 'Firebase service account is saved.' : 'No Firebase service account saved. Paste the project service-account JSON below.' }}</p>
                        <textarea name="firebase_service_account_json" rows="5" placeholder="Paste Firebase service-account JSON{{ $notificationChannelSetting?->firebase_service_account_json ? ' (leave blank to keep saved credentials)' : '' }}" autocomplete="off" class="md:col-span-2 rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-3 font-mono text-xs"></textarea>
                        @if($notificationChannelSetting?->firebase_service_account_json)
                            <label class="md:col-span-2 flex items-center gap-2 text-sm text-[#d8c9ad]"><input type="checkbox" name="clear_firebase_credentials" value="1"> Remove saved Firebase service-account credentials</label>
                        @endif
                        <div class="md:col-span-2">
                            <button class="rounded-full bg-[#D8A83E] px-6 py-2 text-sm font-semibold text-[#1B0D05]">Save notification settings</button>
                        </div>
                    </form>
                </section>

                <section class="rounded-[18px] border border-[#3d261b] bg-[#2B170D] p-6">
                    <h2 class="font-serif text-2xl">SMS Gateway</h2>
                    <p class="mt-2 text-sm text-[#d8c9ad]">Configure Flex SMS credentials. The client secret is encrypted at rest and masked after saving.</p>

                    <form action="{{ route('admin.settings.sms-gateway.update') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                        @csrf
                        <input name="base_url" value="{{ old('base_url', $smsGatewaySetting->base_url ?? config('services.flex_sms.base_url')) }}" placeholder="Base URL" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input name="sender_id" value="{{ old('sender_id', $smsGatewaySetting->sender_id ?? config('services.flex_sms.sender_id')) }}" placeholder="Sender ID" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input name="client_id" value="{{ old('client_id', $smsGatewaySetting->client_id ?? config('services.flex_sms.client_id')) }}" placeholder="Client ID" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input type="password" name="client_secret" placeholder="{{ $smsGatewaySetting?->client_secret ? 'Saved; leave blank to keep current secret' : 'Client Secret' }}" autocomplete="new-password" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <label class="md:col-span-2 flex items-center gap-2 rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2 text-sm">
                            <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $smsGatewaySetting->is_enabled ?? false))>
                            Enable SMS gateway
                        </label>
                        <div class="md:col-span-2">
                            <button class="rounded-full bg-[#D8A83E] px-6 py-2 text-sm font-semibold text-[#1B0D05]">Save SMS settings</button>
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
