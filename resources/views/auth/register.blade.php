<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Account | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen bg-[radial-gradient(circle_at_top_right,_rgba(216,168,62,0.18),_transparent_35%)]">
    <div class="mx-auto grid min-h-screen max-w-7xl items-center gap-10 px-6 py-10 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
        <section class="rounded-[32px] border border-[#d8c9ad] bg-[#FBF6EA] p-8 shadow-lg lg:p-10">
            <div class="max-w-md">
                <a href="/" class="inline-flex items-center gap-3 text-xl font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#d8c9ad] bg-[#1B0D05] text-sm text-[#F4EBD8]">BD</span>
                    <span>ReadArena</span>
                </a>
                <p class="mt-8 text-sm uppercase tracking-[0.35em] text-[#B98A2C]">Create account</p>
                <h1 class="mt-3 font-serif text-4xl text-[#1B0D05]">Join the league your way</h1>
                <p class="mt-4 text-sm leading-7 text-[#5e544d]">Register as a reader to compete and track your progress, or as an author to upload books and draft verified quizzes.</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="name" class="text-sm font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">Full name</label>
                    <input id="name" class="mt-2 block w-full rounded-[18px] border border-[#d8c9ad] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#B98A2C]" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                    @if ($errors->get('name'))
                        <p class="mt-2 text-sm text-[#a43c2c]">{{ $errors->first('name') }}</p>
                    @endif
                </div>

                <div>
                    <label for="email" class="text-sm font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">Email</label>
                    <input id="email" class="mt-2 block w-full rounded-[18px] border border-[#d8c9ad] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#B98A2C]" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                    @if ($errors->get('email'))
                        <p class="mt-2 text-sm text-[#a43c2c]">{{ $errors->first('email') }}</p>
                    @endif
                </div>

                <div>
                    <label for="role" class="text-sm font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">I am joining as</label>
                    <select id="role" name="role" class="mt-2 block w-full rounded-[18px] border border-[#d8c9ad] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#B98A2C]" required>
                        <option value="reader" @selected(old('role', 'reader') === 'reader')>Reader</option>
                        <option value="author" @selected(old('role') === 'author')>Writer / Author</option>
                    </select>
                    @if ($errors->get('role'))
                        <p class="mt-2 text-sm text-[#a43c2c]">{{ $errors->first('role') }}</p>
                    @endif
                </div>

                <div>
                    <label for="password" class="text-sm font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">Password</label>
                    <input id="password" class="mt-2 block w-full rounded-[18px] border border-[#d8c9ad] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#B98A2C]" type="password" name="password" required autocomplete="new-password">
                    @if ($errors->get('password'))
                        <p class="mt-2 text-sm text-[#a43c2c]">{{ $errors->first('password') }}</p>
                    @endif
                </div>

                <div>
                    <label for="password_confirmation" class="text-sm font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">Confirm password</label>
                    <input id="password_confirmation" class="mt-2 block w-full rounded-[18px] border border-[#d8c9ad] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#B98A2C]" type="password" name="password_confirmation" required autocomplete="new-password">
                    @if ($errors->get('password_confirmation'))
                        <p class="mt-2 text-sm text-[#a43c2c]">{{ $errors->first('password_confirmation') }}</p>
                    @endif
                </div>

                <button type="submit" class="w-full rounded-full bg-[#1B0D05] px-6 py-3 text-sm font-semibold text-[#FBF6EA] shadow-sm transition hover:bg-[#2B170D]">
                    Create account
                </button>
            </form>

            <div class="mt-8 border-t border-[#e6dbc8] pt-6 text-sm text-[#5e544d]">
                Already registered?
                <a href="{{ route('login') }}" class="font-semibold text-[#1B0D05] underline underline-offset-4">Sign in</a>
            </div>
        </section>

        <section class="rounded-[32px] border border-[#d8c9ad] bg-[#1B0D05] p-8 text-[#F4EBD8] shadow-xl lg:p-12">
            <p class="text-sm uppercase tracking-[0.35em] text-[#D8A83E]">What you unlock</p>
            <h2 class="mt-4 font-serif text-5xl leading-tight">The app experience should begin before the first page is read.</h2>
            <div class="mt-8 space-y-4">
                <div class="rounded-[22px] border border-[#3d261b] bg-[#2B170D] p-5">
                    <h3 class="font-semibold">Reader Path</h3>
                    <p class="mt-2 text-sm leading-7 text-[#e5d7bf]">Join duels, set reading goals, attend live shows, and build your reputation through verified reading.</p>
                </div>
                <div class="rounded-[22px] border border-[#3d261b] bg-[#2B170D] p-5">
                    <h3 class="font-semibold">Author Path</h3>
                    <p class="mt-2 text-sm leading-7 text-[#e5d7bf]">Upload titles, create questions and answers, and prepare quiz experiences for readers around the world.</p>
                </div>
                <div class="rounded-[22px] border border-[#3d261b] bg-[#2B170D] p-5">
                    <h3 class="font-semibold">Pro Arena</h3>
                    <p class="mt-2 text-sm leading-7 text-[#e5d7bf]">Move from casual participation into subscription-backed competition through regional and global game packages.</p>
                </div>
            </div>
        </section>
    </div>
</div>
</body>
</html>
