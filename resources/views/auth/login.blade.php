<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(216,168,62,0.18),_transparent_35%)]">
    <div class="mx-auto grid min-h-screen max-w-7xl items-center gap-10 px-6 py-10 lg:grid-cols-[1.05fr_0.95fr] lg:px-8">
        <section class="rounded-[32px] border border-[#d8c9ad] bg-[#1B0D05] p-8 text-[#F4EBD8] shadow-xl lg:p-12">
            <a href="/" class="inline-flex items-center gap-3 text-xl font-semibold uppercase tracking-[0.2em]">
                <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#d8c9ad] bg-[#F4EBD8] text-sm text-[#1B0D05]">BD</span>
                <span>ReadArena</span>
            </a>
            <p class="mt-10 text-sm uppercase tracking-[0.35em] text-[#D8A83E]">Reader league access</p>
            <h1 class="mt-4 font-serif text-5xl leading-tight">Return to the arena with a verified account.</h1>
            <p class="mt-6 max-w-xl text-base leading-8 text-[#e5d7bf]">
                Sign in to keep reading, challenge other readers, manage your author studio, or enter the admin control center.
            </p>
            <div class="mt-10 grid gap-4 sm:grid-cols-3">
                <div class="rounded-[22px] border border-[#3d261b] bg-[#2B170D] p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#D8A83E]">Readers</p>
                    <p class="mt-2 text-sm text-[#e5d7bf]">Continue books, duels, and shows.</p>
                </div>
                <div class="rounded-[22px] border border-[#3d261b] bg-[#2B170D] p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#D8A83E]">Authors</p>
                    <p class="mt-2 text-sm text-[#e5d7bf]">Upload titles and write quiz drafts.</p>
                </div>
                <div class="rounded-[22px] border border-[#3d261b] bg-[#2B170D] p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#D8A83E]">Admins</p>
                    <p class="mt-2 text-sm text-[#e5d7bf]">Oversee users, books, and packages.</p>
                </div>
            </div>
        </section>

        <section class="rounded-[32px] border border-[#d8c9ad] bg-[#FBF6EA] p-8 shadow-lg lg:p-10">
            <div class="max-w-md">
                <p class="text-sm uppercase tracking-[0.35em] text-[#B98A2C]">Sign in</p>
                <h2 class="mt-3 font-serif text-4xl text-[#1B0D05]">Welcome back</h2>
                <p class="mt-3 text-sm leading-7 text-[#5e544d]">Use your ReadArena account to continue from where you left off.</p>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-[18px] border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-3 text-sm text-[#5e544d]">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="text-sm font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">Email</label>
                    <input id="email" class="mt-2 block w-full rounded-[18px] border border-[#d8c9ad] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#B98A2C]" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                    @if ($errors->get('email'))
                        <p class="mt-2 text-sm text-[#a43c2c]">{{ $errors->first('email') }}</p>
                    @endif
                </div>

                <div>
                    <label for="password" class="text-sm font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">Password</label>
                    <input id="password" class="mt-2 block w-full rounded-[18px] border border-[#d8c9ad] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#B98A2C]" type="password" name="password" required autocomplete="current-password">
                    @if ($errors->get('password'))
                        <p class="mt-2 text-sm text-[#a43c2c]">{{ $errors->first('password') }}</p>
                    @endif
                </div>

                <label for="remember_me" class="flex items-center gap-3 text-sm text-[#5e544d]">
                    <input id="remember_me" type="checkbox" class="rounded border-[#d8c9ad] text-[#1B0D05] focus:ring-[#B98A2C]" name="remember">
                    <span>Keep me signed in</span>
                </label>

                <div class="flex flex-col gap-4 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    @if (Route::has('password.request'))
                        <a class="text-sm font-semibold text-[#5e544d] underline underline-offset-4 hover:text-[#1B0D05]" href="{{ route('password.request') }}">
                            Forgot your password?
                        </a>
                    @endif

                    <button type="submit" class="rounded-full bg-[#1B0D05] px-6 py-3 text-sm font-semibold text-[#FBF6EA] shadow-sm transition hover:bg-[#2B170D]">
                        Log in
                    </button>
                </div>
            </form>

            <div class="mt-8 border-t border-[#e6dbc8] pt-6 text-sm text-[#5e544d]">
                New here?
                <a href="{{ route('register') }}" class="font-semibold text-[#1B0D05] underline underline-offset-4">Create your ReadArena account</a>
            </div>
        </section>
    </div>
</div>
</body>
</html>
