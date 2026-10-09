@php
    $logoutTheme = $theme ?? 'light';
    $logoutButtonClasses = $logoutTheme === 'dark'
        ? 'rounded-full border border-[#d8c9ad] px-4 py-2 text-sm text-[#F4EBD8] transition hover:bg-[#2B170D]'
        : 'rounded-full bg-[#1B0D05] px-4 py-2 text-sm text-[#FBF6EA] transition hover:bg-[#432919]';
@endphp

<form method="POST" action="{{ route('logout') }}" class="inline-flex">
    @csrf
    <button type="submit" class="{{ $logoutButtonClasses }}">Log out</button>
</form>
