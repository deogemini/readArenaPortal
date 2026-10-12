<x-auth-recovery-layout
    title="Forgot your password | ReadArena"
    eyebrow="Get back to reading"
    headline="A fresh start is only a link away."
    description="Enter the email address you use for ReadArena. We’ll send you a secure link to choose a new password."
>
    <div class="max-w-md">
        <p class="text-sm uppercase tracking-[0.35em] text-[#B98A2C]">Password reset</p>
        <h2 class="mt-3 font-serif text-4xl text-[#1B0D05]">Forgot your password?</h2>
        <p class="mt-3 text-sm leading-7 text-[#5e544d]">We’ll email a reset link if an account exists for that address.</p>
    </div>

    @if (session('status'))
        <div role="status" class="mt-6 rounded-[18px] border border-[#d8c9ad] bg-[#F4EBD8] px-4 py-3 text-sm leading-6 text-[#5e544d]">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="text-sm font-semibold uppercase tracking-[0.2em] text-[#1B0D05]">Email</label>
            <input id="email" class="mt-2 block w-full rounded-[18px] border border-[#d8c9ad] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#B98A2C]" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @if ($errors->get('email'))
                <p class="mt-2 text-sm text-[#a43c2c]">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <button type="submit" class="w-full rounded-full bg-[#1B0D05] px-6 py-3 text-sm font-semibold text-[#FBF6EA] shadow-sm transition hover:bg-[#2B170D]">
            Email password reset link
        </button>
    </form>

    <div class="mt-8 border-t border-[#e6dbc8] pt-6 text-sm text-[#5e544d]">
        Remembered your password?
        <a href="{{ route('login') }}" class="font-semibold text-[#1B0D05] underline underline-offset-4">Back to sign in</a>
    </div>
</x-auth-recovery-layout>
