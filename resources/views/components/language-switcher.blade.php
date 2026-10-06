<div class="fixed bottom-3 right-3 z-[100]" data-readarena-translations-url="{{ route('translations.show', ['locale' => 'sw']) }}">
    <form method="POST" action="{{ route('language.update') }}" class="flex items-center gap-2 rounded-full border border-[#d8c9ad] bg-[#FBF6EA]/95 px-3 py-2 text-sm text-[#1B0D05] shadow-lg backdrop-blur">
        @csrf
        <label for="readarena-locale" class="sr-only">{{ __('Language') }}</label>
        <span aria-hidden="true">&#127760;</span>
        <select id="readarena-locale" name="locale" onchange="this.form.submit()" class="border-0 bg-transparent py-0 pl-1 pr-7 text-sm text-[#1B0D05] focus:ring-0">
            <option value="en" @selected(app()->getLocale() === 'en')>English</option>
            <option value="sw" @selected(app()->getLocale() === 'sw')>Kiswahili</option>
        </select>
    </form>
</div>
