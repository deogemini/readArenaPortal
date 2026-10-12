<x-auth-recovery-layout
    title="Choose a new password | ReadArena"
    eyebrow="Finish account recovery"
    headline="Your next chapter starts here."
    description="Choose a strong new password, then sign in to continue your reading journey on ReadArena."
>
    <div class="max-w-md">
        <p class="text-sm uppercase tracking-[0.35em] text-[#B98A2C]">New password</p>
        <h2 class="mt-3 font-serif text-4xl text-[#1B0D05]">Choose a new password</h2>
        <p class="mt-3 text-sm leading-7 text-[#5e544d]">Set a password you can use on the website and Android app.</p>
    </div>

    @if (session('status'))
        <div role="status" class="mt-6 rounded-[18px] border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-3 text-sm leading-6 text-[#5e544d]">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="text-sm font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">Email</label>
            <input id="email" class="mt-2 block w-full rounded-[18px] border border-[#d8c9ad] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#B98A2C]" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            @if ($errors->get('email'))
                <p class="mt-2 text-sm text-[#a43c2c]">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <div>
            <label for="password" class="text-sm font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">New password</label>
            <input id="password" class="mt-2 block w-full rounded-[18px] border border-[#d8c9ad] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#B98A2C]" type="password" name="password" required autocomplete="new-password">
            @if ($errors->get('password'))
                <p class="mt-2 text-sm text-[#a43c2c]">{{ $errors->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="password_confirmation" class="text-sm font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">Confirm new password</label>
            <input id="password_confirmation" class="mt-2 block w-full rounded-[18px] border border-[#d8c9ad] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#B98A2C]" type="password" name="password_confirmation" required autocomplete="new-password">
            @if ($errors->get('password_confirmation'))
                <p class="mt-2 text-sm text-[#a43c2c]">{{ $errors->first('password_confirmation') }}</p>
            @endif
        </div>

        <button type="submit" class="w-full rounded-full bg-[#1B0D05] px-6 py-3 text-sm font-semibold text-[#FBF6EA] shadow-sm transition hover:bg-[#2B170D]">
            Reset password
        </button>
    </form>

    <div class="mt-8 border-t border-[#e6dbc8] pt-6 text-sm text-[#5e544d]">
        Need another reset link?
        <a href="{{ route('password.request') }}" class="font-semibold text-[#1B0D05] underline underline-offset-4">Request a new one</a>
    </div>
</x-auth-recovery-layout>
