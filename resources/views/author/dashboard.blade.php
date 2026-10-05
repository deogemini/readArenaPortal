<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Author Dashboard | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D]">
<div class="min-h-screen">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-[#B98A2C]">Author studio</p>
                <h1 class="font-serif text-3xl text-[#1B0D05]">Welcome, {{ auth()->user()->name }}</h1>
            </div>
            <a href="/logout" class="rounded-full bg-[#1B0D05] px-4 py-2 text-sm text-[#FBF6EA]" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            <form id="logout-form" action="/logout" method="POST" class="hidden">@csrf</form>
        </div>
    </header>

    <main class="mx-auto grid max-w-7xl gap-8 px-6 py-10 lg:grid-cols-[1fr_1fr] lg:px-8">
        @if (session('status'))
            <section class="rounded-[18px] border border-[#B98A2C] bg-[#FDF3D9] px-4 py-3 text-sm font-medium text-[#5e3d08] lg:col-span-2">
                {{ session('status') }}
            </section>
        @endif

        @if ($errors->any())
            <section class="rounded-[18px] border border-[#a53b2a] bg-[#fde7e2] px-4 py-3 text-sm text-[#7a2416] lg:col-span-2">
                <p class="font-semibold">Please fix the following:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
            <h2 class="font-serif text-2xl">Upload a Book</h2>
            <form action="{{ route('author.books.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <input name="title" value="{{ old('title') }}" placeholder="Book title" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required>
                <textarea name="description" placeholder="Description" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2">{{ old('description') }}</textarea>
                <input name="genre" value="{{ old('genre') }}" placeholder="Genre" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required>
                <input type="number" name="publication_year" value="{{ old('publication_year') }}" placeholder="Publication year" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2">
                <input type="number" name="page_count" value="{{ old('page_count') }}" placeholder="Page count" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2">
                <input type="url" name="cover_image" value="{{ old('cover_image') }}" placeholder="Cover image URL" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2">
                <div>
                    <input type="file" name="pdf_file" accept="application/pdf" required class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2">
                    <p class="mt-1 text-xs text-[#5e544d]">PDF required. Current maximum upload size is 3MB.</p>
                </div>
                <button class="rounded-full bg-[#1B0D05] px-5 py-2 text-sm font-semibold text-[#FBF6EA]">Save book</button>
            </form>
        </section>

        <section class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
            <h2 class="font-serif text-2xl">Create a Quiz</h2>
            <form action="{{ route('author.quizzes.store') }}" method="POST" class="mt-5 space-y-4" data-question-builder>
                @csrf
                <select name="book_id" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required>
                    <option value="">Select book</option>
                    @foreach($books as $book)
                        <option value="{{ $book->id }}">{{ $book->title }}</option>
                    @endforeach
                </select>
                <input name="title" placeholder="Quiz title" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required>
                <textarea name="question" placeholder="Question" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required></textarea>
                <select name="question_type" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" data-question-type-select>
                    <option value="single_choice" @selected(old('question_type') === 'single_choice')>Single choice</option>
                    <option value="multiple_choice" @selected(old('question_type', 'multiple_choice') === 'multiple_choice')>Multiple correct choices</option>
                    <option value="true_false" @selected(old('question_type') === 'true_false')>True or false</option>
                    <option value="one_word" @selected(old('question_type') === 'one_word')>One-word answer</option>
                    <option value="short_answer" @selected(old('question_type') === 'short_answer')>Short answer</option>
                    <option value="written_response" @selected(old('question_type') === 'written_response')>Written explanation (manual review)</option>
                </select>
                @php
                    $oldCorrectOptions = old('correct_options', ['0']);
                    $selectedCorrectOptions = is_array($oldCorrectOptions) ? array_map('strval', $oldCorrectOptions) : [];
                @endphp
                <div data-type-fields="choice">
                    <p class="mb-3 text-sm text-[#5e544d]" data-choice-help></p>
                    @for($optionIndex = 0; $optionIndex < 4; $optionIndex++)
                        <div class="mb-3 grid gap-2 sm:grid-cols-[1fr_auto] sm:items-center">
                            <input name="answer_options[{{ $optionIndex }}][body]" value="{{ old('answer_options.'.$optionIndex.'.body') }}" placeholder="Answer choice {{ $optionIndex + 1 }}" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" data-choice-input>
                            <label class="flex items-center gap-2 text-sm text-[#5e544d]"><input type="checkbox" name="correct_options[]" value="{{ $optionIndex }}" @checked(in_array((string) $optionIndex, $selectedCorrectOptions, true)) data-correct-option> Correct answer</label>
                        </div>
                    @endfor
                </div>
                <div data-type-fields="true_false" hidden>
                    <label class="block text-sm text-[#5e544d]">Correct answer
                        <select name="true_false_correct" class="mt-2 w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2"><option value="">Choose true or false</option><option value="0" @selected(old('true_false_correct') === '0')>True</option><option value="1" @selected(old('true_false_correct') === '1')>False</option></select>
                    </label>
                </div>
                <div data-type-fields="accepted" hidden>
                    <label class="block text-sm text-[#5e544d]">Accepted answer(s), one per line
                        <textarea name="accepted_answers" rows="4" class="mt-2 w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" placeholder="Add acceptable answer variants, one per line.">{{ old('accepted_answers') }}</textarea>
                    </label>
                    <p class="mt-1 text-xs text-[#5e544d]">These are automatically marked without regard to case or punctuation.</p>
                </div>
                <div data-type-fields="written_response" hidden>
                    <label class="block text-sm text-[#5e544d]">Private marking guide
                        <textarea name="answer_guide" rows="4" maxlength="5000" class="mt-2 w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" placeholder="Tell the reviewer what a good explanation should include.">{{ old('answer_guide') }}</textarea>
                    </label>
                    <p class="mt-1 text-xs text-[#5e544d]">An administrator will grade written explanations manually.</p>
                </div>
                <button class="rounded-full bg-[#1B0D05] px-5 py-2 text-sm font-semibold text-[#FBF6EA]">Create quiz</button>
            </form>
        </section>

        <section class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-6 lg:col-span-2">
            <h2 class="font-serif text-2xl">Your Uploaded Books</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @forelse($books as $book)
                    <div class="rounded-[16px] border border-[#d8c9ad] bg-[#F4EBD8] p-4">
                        <p class="font-semibold text-[#1B0D05]">{{ $book->title }}</p>
                        <p class="mt-1 text-sm text-[#5e544d]">Status: {{ ucfirst($book->status) }}</p>
                        <p class="mt-1 text-sm text-[#5e544d]">PDF: {{ $book->pdf_path ? 'Uploaded' : 'Missing' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-[#5e544d]">No books yet. Upload your first title above.</p>
                @endforelse
            </div>
        </section>
    </main>
</div>
<script>
    document.querySelectorAll('[data-question-builder]').forEach((builder) => {
        const typeSelect = builder.querySelector('[data-question-type-select]');
        if (!typeSelect) return;
        const updateFields = () => {
            const type = typeSelect.value;
            builder.querySelectorAll('[data-type-fields]').forEach((section) => {
                const kind = section.dataset.typeFields;
                const active = kind === 'choice'
                    ? ['single_choice', 'multiple_choice'].includes(type)
                    : kind === type || (kind === 'accepted' && ['one_word', 'short_answer'].includes(type));
                section.hidden = !active;
                section.querySelectorAll('input, textarea, select').forEach((input) => {
                    input.disabled = !active;
                    input.required = active && (input.hasAttribute('data-choice-input') || kind !== 'choice');
                });
            });
            builder.querySelectorAll('[data-correct-option]').forEach((input) => {
                input.type = type === 'single_choice' ? 'radio' : 'checkbox';
            });
            const help = builder.querySelector('[data-choice-help]');
            if (help) help.textContent = type === 'single_choice'
                ? 'Enter four different choices and mark exactly one correct answer.'
                : 'Enter four different choices and mark all correct answers.';
        };
        typeSelect.addEventListener('change', updateFields);
        updateFields();
    });
</script>
</body>
</html>
