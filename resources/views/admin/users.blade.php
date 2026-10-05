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
                    <p class="text-sm uppercase tracking-[0.3em] text-[#D8A83E]">Live member activity</p>
                    <h1 class="mt-2 font-serif text-3xl">Users &amp; activity</h1>
                </div>
                <a href="/admin" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Back to dashboard</a>
            </div>
        </header>

        <section class="px-6 py-8 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-[#d8c9ad]">Presence and activity from the web portal and Android app. Updates every 5 seconds.</p>
                    <p id="live-sync-state" class="flex items-center gap-2 text-xs text-[#d8c9ad]" aria-live="polite">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-[#54c47a]"></span> Live sync connecting…
                    </p>
                </div>

                <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach([
                        ['key' => 'online_now', 'label' => 'Online now'],
                        ['key' => 'active_24_hours', 'label' => 'Active in 24 hours'],
                        ['key' => 'actions_last_hour', 'label' => 'Actions this hour'],
                        ['key' => 'new_today', 'label' => 'New users today'],
                    ] as $metric)
                        <div class="rounded-2xl border border-[#3d261b] bg-[#2B170D] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-[#D8A83E]">{{ $metric['label'] }}</p>
                            <p class="mt-2 font-serif text-3xl" data-live-metric="{{ $metric['key'] }}">{{ $activitySnapshot['metrics'][$metric['key']] }}</p>
                        </div>
                    @endforeach
                </div>

                <section class="mb-6 rounded-[22px] border border-[#3d261b] bg-[#2B170D] p-5" aria-labelledby="live-activity-title">
                    <div class="flex flex-wrap items-baseline justify-between gap-3">
                        <div>
                            <h2 id="live-activity-title" class="font-serif text-2xl">Recent activity</h2>
                            <p class="mt-1 text-xs text-[#b8ab95]">Account, reading, quiz, goal, review, and community actions.</p>
                        </div>
                        <p class="text-xs text-[#b8ab95]">Updated <time id="live-updated-at">just now</time></p>
                    </div>
                    <div id="live-activity-feed" class="mt-4 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                        @forelse($activitySnapshot['events'] as $event)
                            <article class="rounded-xl border border-[#3d261b] bg-[#1B0D05] p-3" data-activity-event="{{ $event['id'] }}">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-semibold">{{ $event['user_name'] }}</p>
                                    <time class="shrink-0 text-xs text-[#b8ab95]">{{ $event['time_ago'] }}</time>
                                </div>
                                <p class="mt-1 text-sm text-[#d8c9ad]">{{ $event['activity'] }}</p>
                                <p class="mt-1 text-xs text-[#9d8b74]">{{ $event['role'] }} · {{ $event['platform'] }}</p>
                            </article>
                        @empty
                            <p class="rounded-xl border border-[#3d261b] bg-[#1B0D05] p-4 text-sm text-[#d8c9ad]" data-empty-activity>No activity yet. New events will appear here as people use ReadArena.</p>
                        @endforelse
                    </div>
                </section>

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
                    <select name="presence" class="rounded-xl border border-[#3d261b] bg-[#2B170D] px-4 py-2">
                        <option value="" @selected($selectedPresence === '')>All presence states</option>
                        <option value="online" @selected($selectedPresence === 'online')>Online now</option>
                        <option value="offline" @selected($selectedPresence === 'offline')>Offline</option>
                    </select>
                    <button class="rounded-full bg-[#D8A83E] px-5 py-2 text-sm font-semibold text-[#1B0D05]">Filter</button>
                </form>

                <div class="mt-6 overflow-x-auto rounded-[18px] border border-[#3d261b]">
                    <table class="w-full min-w-[1050px] text-left text-sm">
                        <thead class="bg-[#2B170D]">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Verified</th>
                                <th class="px-4 py-3">Presence</th>
                                <th class="px-4 py-3">Last channel</th>
                                <th class="px-4 py-3">Last active</th>
                                <th class="px-4 py-3">Ongoing Activities</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#1B0D05]">
                            @forelse($users as $user)
                                <tr class="border-t border-[#3d261b]" data-user-row="{{ $user->id }}">
                                    <td class="px-4 py-3">{{ $user->name }}</td>
                                    <td class="px-4 py-3">{{ $user->email }}</td>
                                    <td class="px-4 py-3">{{ ucfirst($user->role ?? 'reader') }}</td>
                                    <td class="px-4 py-3">{{ $user->email_verified_at ? 'Yes' : 'No' }}</td>
                                    <td class="px-4 py-3">
                                        <span data-presence-for="{{ $user->id }}" class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-xs {{ $user->is_online ? 'border-[#287342] bg-[#14361f] text-[#9be3ad]' : 'border-[#594535] bg-[#26170e] text-[#b8ab95]' }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $user->is_online ? 'bg-[#54c47a]' : 'bg-[#76634f]' }}"></span>
                                            <span data-presence-label>{{ $user->is_online ? 'Online' : 'Offline' }}</span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-[#d8c9ad]" data-platform-for="{{ $user->id }}">
                                        {{ $user->last_seen_platform === 'android_app' ? 'Android app' : ($user->last_seen_platform === 'web_portal' ? 'Web portal' : '—') }}
                                    </td>
                                    <td class="px-4 py-3 text-[#d8c9ad]" data-last-seen-for="{{ $user->id }}" data-last-seen-at="{{ $user->last_seen_at?->toIso8601String() }}">
                                        {{ $user->last_seen_at?->diffForHumans() ?? 'Never' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold" data-user-activities-for="{{ $user->id }}">{{ $user->ongoing_activities_count }} (Goals: {{ $user->active_goals_count }}, Duels: {{ (int) $user->active_challenger_duels_count + (int) $user->active_opponent_duels_count }})</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-[#d8c9ad]">No users found for these filters.</td>
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
<script>
    (() => {
        const endpoint = @json(route('admin.users.activity'));
        const userIds = @json($users->getCollection()->pluck('id')->values());
        const syncState = document.getElementById('live-sync-state');
        const feed = document.getElementById('live-activity-feed');
        const updatedAt = document.getElementById('live-updated-at');
        let refreshing = false;

        const setText = (selector, value) => {
            const element = document.querySelector(selector);
            if (element) element.textContent = value;
        };

        const timeAgo = (isoDate) => {
            if (!isoDate) return 'Never';
            const seconds = Math.max(0, Math.floor((Date.now() - new Date(isoDate).getTime()) / 1000));
            if (seconds < 60) return 'Just now';
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return `${minutes}m ago`;
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return `${hours}h ago`;
            return `${Math.floor(hours / 24)}d ago`;
        };

        const renderEvents = (events) => {
            feed.replaceChildren();
            if (!events.length) {
                const empty = document.createElement('p');
                empty.className = 'rounded-xl border border-[#3d261b] bg-[#1B0D05] p-4 text-sm text-[#d8c9ad]';
                empty.textContent = 'No activity yet. New events will appear here as people use ReadArena.';
                feed.append(empty);
                return;
            }

            events.forEach((event) => {
                const article = document.createElement('article');
                article.className = 'rounded-xl border border-[#3d261b] bg-[#1B0D05] p-3';
                article.dataset.activityEvent = event.id;
                const heading = document.createElement('div');
                heading.className = 'flex items-start justify-between gap-2';
                const actor = document.createElement('p');
                actor.className = 'text-sm font-semibold';
                actor.textContent = event.user_name;
                const time = document.createElement('time');
                time.className = 'shrink-0 text-xs text-[#b8ab95]';
                time.textContent = event.time_ago;
                heading.append(actor, time);
                const action = document.createElement('p');
                action.className = 'mt-1 text-sm text-[#d8c9ad]';
                action.textContent = event.activity;
                const source = document.createElement('p');
                source.className = 'mt-1 text-xs text-[#9d8b74]';
                source.textContent = `${event.role} · ${event.platform}`;
                article.append(heading, action, source);
                feed.append(article);
            });
        };

        const refresh = async () => {
            if (refreshing) return;
            refreshing = true;
            try {
                const query = new URLSearchParams();
                userIds.forEach((id) => query.append('user_ids[]', id));
                const response = await fetch(`${endpoint}?${query.toString()}`, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('Activity refresh failed');
                const snapshot = await response.json();

                Object.entries(snapshot.metrics).forEach(([key, value]) => setText(`[data-live-metric="${key}"]`, value));
                renderEvents(snapshot.events);
                snapshot.users.forEach((user) => {
                    const presence = document.querySelector(`[data-presence-for="${user.id}"]`);
                    if (presence) {
                        presence.querySelector('[data-presence-label]').textContent = user.is_online ? 'Online' : 'Offline';
                        presence.classList.toggle('border-[#287342]', user.is_online);
                        presence.classList.toggle('bg-[#14361f]', user.is_online);
                        presence.classList.toggle('text-[#9be3ad]', user.is_online);
                        presence.classList.toggle('border-[#594535]', !user.is_online);
                        presence.classList.toggle('bg-[#26170e]', !user.is_online);
                        presence.classList.toggle('text-[#b8ab95]', !user.is_online);
                        presence.firstElementChild.classList.toggle('bg-[#54c47a]', user.is_online);
                        presence.firstElementChild.classList.toggle('bg-[#76634f]', !user.is_online);
                    }
                    const platform = document.querySelector(`[data-platform-for="${user.id}"]`);
                    if (platform) platform.textContent = user.platform ?? '—';
                    const lastSeen = document.querySelector(`[data-last-seen-for="${user.id}"]`);
                    if (lastSeen) {
                        lastSeen.dataset.lastSeenAt = user.last_seen_at ?? '';
                        lastSeen.textContent = timeAgo(user.last_seen_at);
                    }
                    const activities = document.querySelector(`[data-user-activities-for="${user.id}"]`);
                    if (activities) activities.textContent = `${user.active_goals + user.active_duels} (Goals: ${user.active_goals}, Duels: ${user.active_duels})`;
                });
                updatedAt.textContent = new Date(snapshot.generated_at).toLocaleTimeString();
                syncState.textContent = 'Live · refreshed just now';
            } catch (error) {
                syncState.textContent = 'Reconnecting…';
            } finally {
                refreshing = false;
            }
        };

        refresh();
        window.setInterval(() => {
            if (!document.hidden) refresh();
        }, 5000);
    })();
</script>
</body>
</html>
