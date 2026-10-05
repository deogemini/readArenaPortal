<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Quizzes | ReadArena</title>
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
            <a href="/admin/quizzes" class="block rounded-[14px] bg-[#2B170D] px-4 py-3">Quizzes</a>
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
                    <p class="text-sm uppercase tracking-[0.3em] text-[#D8A83E]">Assessment management</p>
                    <h1 class="mt-2 font-serif text-3xl">Quizzes</h1>
                </div>
                <a href="/admin" class="rounded-full border border-[#d8c9ad] px-4 py-2 text-sm">Back to dashboard</a>
            </div>
        </header>

        <section class="px-6 py-8 lg:px-8">
            <div class="mx-auto max-w-7xl">
                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-[#3d261b] bg-[#2B170D] px-4 py-3 text-sm text-[#F4EBD8]">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-[#7a2e22] bg-[#2B170D] px-4 py-3 text-sm text-[#f8d2c8]">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($pendingQuizAttempts->isNotEmpty())
                    <section class="mb-6 rounded-[20px] border border-[#8c6728] bg-[#2B170D] p-6">
                        <h2 class="font-serif text-2xl">Written responses awaiting review</h2>
                        <p class="mt-1 text-sm text-[#d8c9ad]">Scores are finalized after every written explanation is marked.</p>
                        <div class="mt-4 space-y-4">
                            @foreach($pendingQuizAttempts as $pendingAttempt)
                                <form action="{{ route('admin.quiz-attempts.review', $pendingAttempt) }}" method="POST" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] p-4">
                                    @csrf
                                    <h3 class="font-semibold">{{ $pendingAttempt->user?->name ?? 'Reader' }} · {{ $pendingAttempt->quiz?->title }} · {{ $pendingAttempt->quiz?->book?->title }}</h3>
                                    <p class="mt-1 text-xs text-[#b8ab95]">Submitted {{ $pendingAttempt->created_at?->format('Y-m-d H:i') }}</p>
                                    @foreach($pendingAttempt->responses->filter(fn ($response) => $response->question?->question_type === 'written_response') as $response)
                                        <div class="mt-4 rounded-lg border border-[#3d261b] p-3">
                                            <p class="font-medium">{{ $response->question->prompt }}</p>
                                            <p class="mt-2 whitespace-pre-wrap text-sm text-[#d8c9ad]">{{ $response->answer_text }}</p>
                                            <p class="mt-2 text-xs text-[#D8A83E]">Marking guide: {{ $response->question->answers->firstWhere('is_correct', true)?->body }}</p>
                                            <label class="mt-3 flex items-center gap-2 text-sm">Points (0–{{ $response->question->points }})
                                                <input type="number" name="points_awarded[{{ $response->quiz_question_id }}]" min="0" max="{{ $response->question->points }}" required class="w-24 rounded-lg border border-[#3d261b] bg-[#2B170D] px-3 py-2">
                                            </label>
                                        </div>
                                    @endforeach
                                    <button class="mt-4 rounded-full bg-[#D8A83E] px-5 py-2 text-sm font-semibold text-[#1B0D05]">Finalize score</button>
                                </form>
                            @endforeach
                        </div>
                    </section>
                @endif

                <div class="mb-6 grid gap-6 lg:grid-cols-2">
                    <section class="rounded-[20px] border border-[#3d261b] bg-[#2B170D] p-6">
                        <h2 class="font-serif text-2xl">Create Quiz</h2>
                        <form action="{{ route('admin.quizzes.store') }}" method="POST" class="mt-5 grid gap-4">
                            @csrf
                            <select name="book_id" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                                <option value="">Select book</option>
                                @foreach($books as $book)
                                    <option value="{{ $book->id }}" @selected((string) old('book_id') === (string) $book->id)>{{ $book->title }}</option>
                                @endforeach
                            </select>
                            <input name="title" value="{{ old('title') }}" placeholder="Quiz title" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                            <textarea name="instructions" rows="3" placeholder="Instructions" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">{{ old('instructions') }}</textarea>
                            <div class="grid items-start gap-4 sm:grid-cols-3">
                                <label class="block">
                                    <span class="mb-1 block text-sm font-medium">Pass mark (%)</span>
                                    <input type="number" name="pass_mark" value="{{ old('pass_mark', 70) }}" min="1" max="100" class="w-full rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                                    <span class="mt-1 block text-xs leading-5 text-[#d8c9ad]">Minimum percentage a reader needs to pass.</span>
                                </label>
                                <label class="block">
                                    <span class="mb-1 block text-sm font-medium">Attempt limit</span>
                                    <input type="number" name="attempt_limit" value="{{ old('attempt_limit', 3) }}" min="1" class="w-full rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                                    <span class="mt-1 block text-xs leading-5 text-[#d8c9ad]">Maximum number of tries allowed per reader.</span>
                                </label>
                                <label class="block">
                                    <span class="mb-1 block text-sm font-medium">Time limit (minutes)</span>
                                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 10) }}" min="1" class="w-full rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                                    <span class="mt-1 block text-xs leading-5 text-[#d8c9ad]">Minutes allowed to complete one attempt.</span>
                                </label>
                            </div>
                            <select name="status" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                                <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                                <option value="published" @selected(old('status') === 'published')>Published</option>
                            </select>
                            <button class="rounded-full bg-[#D8A83E] px-6 py-2 text-sm font-semibold text-[#1B0D05]">Create quiz</button>
                        </form>
                    </section>

                    <section class="rounded-[20px] border border-[#3d261b] bg-[#2B170D] p-6">
                        <h2 class="font-serif text-2xl">Add Question to Quiz</h2>
                        <form action="{{ route('admin.quizzes.questions.store', ['quiz' => old('quiz_id', $quizzes->first()?->id ?? 0)]) }}" method="POST" class="mt-5 grid gap-4" id="question-form" data-question-builder>
                            @csrf
                            <select name="quiz_id" id="quiz-select" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                                <option value="">Select quiz</option>
                                @foreach($quizzes as $quiz)
                                    <option value="{{ $quiz->id }}" @selected((string) old('quiz_id') === (string) $quiz->id)>{{ $quiz->title }}</option>
                                @endforeach
                            </select>
                            <textarea name="prompt" rows="3" placeholder="Question prompt" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>{{ old('prompt') }}</textarea>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <input type="number" name="points" value="{{ old('points', 10) }}" placeholder="Points" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                                <input type="number" name="sort_order" value="{{ old('sort_order') }}" placeholder="Sort order (optional)" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                            </div>
                            @php
                                $oldCorrectOptions = old('correct_options', ['0']);
                                $selectedCorrectOptions = is_array($oldCorrectOptions) ? array_map('strval', $oldCorrectOptions) : [];
                            @endphp
                            @include('admin.partials.question-answer-fields', [
                                'typeField' => 'question_type',
                                'selectedType' => old('question_type', 'multiple_choice'),
                                'answerOptionsField' => 'answer_options',
                                'correctOptionsField' => 'correct_options[]',
                                'trueFalseField' => 'true_false_correct',
                                'acceptedAnswersField' => 'accepted_answers',
                                'answerGuideField' => 'answer_guide',
                                'optionBodies' => old('answer_options', []),
                                'selectedCorrectOptions' => $selectedCorrectOptions,
                                'trueFalseCorrect' => old('true_false_correct'),
                                'acceptedAnswersText' => old('accepted_answers', ''),
                                'answerGuideText' => old('answer_guide', ''),
                            ])
                            <button class="rounded-full bg-[#D8A83E] px-6 py-2 text-sm font-semibold text-[#1B0D05]">Add question</button>
                        </form>
                    </section>
                </div>

                <div class="overflow-hidden rounded-[18px] border border-[#3d261b]">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#2B170D]">
                            <tr>
                                <th class="px-4 py-3">Title</th>
                                <th class="px-4 py-3">Book</th>
                                <th class="px-4 py-3">Questions</th>
                                <th class="px-4 py-3">Attempts Made</th>
                                <th class="px-4 py-3">Pass Mark</th>
                                <th class="px-4 py-3">Attempts</th>
                                <th class="px-4 py-3">Duration</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#1B0D05]">
                            @forelse($quizzes as $quiz)
                                <tr class="border-t border-[#3d261b]">
                                    <td class="px-4 py-3">
                                        {{ $quiz->title }}
                                        <details class="mt-2">
                                            <summary class="cursor-pointer text-xs text-[#D8A83E]">Edit quiz settings</summary>
                                            <form action="{{ route('admin.quizzes.update', $quiz) }}" method="POST" class="mt-2 grid min-w-64 gap-2 rounded-xl border border-[#3d261b] bg-[#2B170D] p-3">
                                                @csrf @method('PATCH')
                                                <input name="title" value="{{ $quiz->title }}" required class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" placeholder="Title">
                                                <textarea name="instructions" rows="2" class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" placeholder="Instructions">{{ $quiz->instructions }}</textarea>
                                                <input type="number" name="pass_mark" value="{{ $quiz->pass_mark }}" min="1" max="100" required class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" aria-label="Pass mark">
                                                <input type="number" name="attempt_limit" value="{{ $quiz->attempt_limit }}" min="1" required class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" aria-label="Attempt limit">
                                                <input type="number" name="duration_minutes" value="{{ $quiz->duration_minutes }}" min="1" required class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" aria-label="Duration">
                                                <select name="status" class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs"><option value="draft" @selected($quiz->status === 'draft')>Draft</option><option value="published" @selected($quiz->status === 'published')>Published</option></select>
                                                <button class="rounded-full bg-[#D8A83E] px-3 py-2 text-xs font-semibold text-[#1B0D05]">Save quiz</button>
                                            </form>
                                        </details>
                                    </td>
                                    <td class="px-4 py-3">{{ $quiz->book->title ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $quiz->questions->count() }}</td>
                                    <td class="px-4 py-3">{{ $quiz->attempts_count }}</td>
                                    <td class="px-4 py-3">{{ $quiz->pass_mark }}%</td>
                                    <td class="px-4 py-3">{{ $quiz->attempt_limit }}</td>
                                    <td class="px-4 py-3">{{ $quiz->duration_minutes }} min</td>
                                    <td class="px-4 py-3">{{ ucfirst($quiz->status ?? 'draft') }}</td>
                                    <td class="px-4 py-3">
                                        @if($quiz->attempts_count > 0)
                                            <span class="text-xs text-[#d8c9ad]">Locked (attempted)</span>
                                        @else
                                            <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" onsubmit="return confirm('Delete this quiz?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-full border border-[#7a2e22] px-3 py-1 text-xs font-semibold text-[#f8d2c8]">Delete quiz</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>

                                @foreach($quiz->questions->sortBy('sort_order') as $question)
                                    @php
                                        $questionAnswers = $question->answers->sortBy('id')->values();
                                        $oldQuestionOptions = old('question_answer_options.'.$question->id, []);
                                        $savedCorrectOptions = $questionAnswers->filter(fn ($answer) => $answer->is_correct)->keys()->map(fn ($index) => (string) $index)->all();
                                        $oldQuestionCorrectOptions = old('question_correct_options.'.$question->id);
                                        $selectedQuestionCorrectOptions = is_array($oldQuestionCorrectOptions)
                                            ? array_map('strval', $oldQuestionCorrectOptions)
                                            : (is_array($oldQuestionOptions) && $oldQuestionOptions !== [] ? [] : $savedCorrectOptions);
                                        $questionType = old('question_type.'.$question->id, $question->question_type ?: 'multiple_choice');
                                        $questionOptionBodies = $oldQuestionOptions !== [] ? $oldQuestionOptions : $questionAnswers->pluck('body')->all();
                                        $acceptedAnswersText = old('question_accepted_answers.'.$question->id, $questionAnswers->where('is_correct', true)->pluck('body')->implode("\n"));
                                        $answerGuideText = old('question_answer_guide.'.$question->id, $questionType === 'written_response' ? ($questionAnswers->firstWhere('is_correct', true)?->body ?? '') : '');
                                        $trueFalseBody = $questionAnswers->firstWhere('is_correct', true)?->body;
                                        $trueFalseCorrect = old('question_true_false_correct.'.$question->id, $trueFalseBody === 'False' ? '1' : ($trueFalseBody === 'True' ? '0' : ''));
                                    @endphp
                                    <tr class="border-t border-[#3d261b]/60 bg-[#130804]">
                                        <td colspan="9" class="px-4 py-4">
                                            <div class="mb-2 text-xs text-[#b8ab95]">
                                                @if($question->last_edited_at)
                                                    Last edited {{ $question->last_edited_at->format('Y-m-d H:i') }} by {{ $question->editor?->name ?? 'Unknown' }}
                                                @else
                                                    Not edited after creation
                                                @endif
                                            </div>
                                            @if ($quiz->attempts_count === 0)
                                                <form action="{{ route('admin.quiz-questions.update', $question) }}" method="POST" class="grid gap-3 md:grid-cols-2" data-question-builder>
                                                    @csrf
                                                    @method('PATCH')
                                                    <input name="prompt" value="{{ $question->prompt }}" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-3 py-2 md:col-span-2" required>
                                                    <input type="number" name="points" value="{{ $question->points }}" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-3 py-2" min="1" required>
                                                    <input type="number" name="sort_order" value="{{ $question->sort_order }}" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-3 py-2" min="1" required>
                                                    @include('admin.partials.question-answer-fields', [
                                                        'typeField' => 'question_type['.$question->id.']',
                                                        'selectedType' => $questionType,
                                                        'answerOptionsField' => 'question_answer_options['.$question->id.']',
                                                        'correctOptionsField' => 'question_correct_options['.$question->id.'][]',
                                                        'trueFalseField' => 'question_true_false_correct['.$question->id.']',
                                                        'acceptedAnswersField' => 'question_accepted_answers['.$question->id.']',
                                                        'answerGuideField' => 'question_answer_guide['.$question->id.']',
                                                        'optionBodies' => $questionOptionBodies,
                                                        'selectedCorrectOptions' => $selectedQuestionCorrectOptions,
                                                        'trueFalseCorrect' => $trueFalseCorrect,
                                                        'acceptedAnswersText' => $acceptedAnswersText,
                                                        'answerGuideText' => $answerGuideText,
                                                    ])
                                                    <div class="md:col-span-2">
                                                        <button class="rounded-full bg-[#D8A83E] px-4 py-2 text-xs font-semibold text-[#1B0D05]">Save question changes</button>
                                                    </div>
                                                </form>
                                                <form action="{{ route('admin.quiz-questions.destroy', $question) }}" method="POST" class="mt-3" onsubmit="return confirm('Delete this question?')">@csrf @method('DELETE')<button class="rounded-full border border-[#7a2e22] px-4 py-2 text-xs text-[#f8d2c8]">Delete question</button></form>
                                            @else
                                                <p class="mt-3 text-xs text-[#b8ab95]">Question editing and deletion are locked because readers have quiz attempts.</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-[#d8c9ad]">No quizzes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $quizzes->links() }}</div>
            </div>
        </section>

        <script>
            (function () {
                const form = document.getElementById('question-form');
                const select = document.getElementById('quiz-select');
                document.querySelectorAll('[data-question-builder]').forEach((builder) => {
                    const typeSelect = builder.querySelector('[data-question-type-select]');
                    if (!typeSelect) return;

                    const updateFields = () => {
                        const type = typeSelect.value;
                        builder.querySelectorAll('[data-type-fields]').forEach((section) => {
                            const sectionType = section.dataset.typeFields;
                            const active = sectionType === 'choice'
                                ? ['single_choice', 'multiple_choice'].includes(type)
                                : sectionType === type || (sectionType === 'accepted' && ['one_word', 'short_answer'].includes(type));
                            section.hidden = !active;
                            section.querySelectorAll('input, textarea, select').forEach((input) => {
                                input.disabled = !active;
                                input.required = active && (input.hasAttribute('data-choice-input') || sectionType !== 'choice');
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

                if (form && select) {
                    const updateAction = () => {
                        if (select.value) form.action = '{{ url('/admin/quizzes') }}/' + select.value + '/questions';
                    };
                    select.addEventListener('change', updateAction);
                    updateAction();
                }
            })();
        </script>
    </main>
</div>
</body>
</html>
