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
                            <div class="grid gap-4 sm:grid-cols-3">
                                <input type="number" name="pass_mark" value="{{ old('pass_mark', 70) }}" placeholder="Pass %" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                                <input type="number" name="attempt_limit" value="{{ old('attempt_limit', 3) }}" placeholder="Attempts" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 10) }}" placeholder="Minutes" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
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
                        <form action="{{ route('admin.quizzes.questions.store', ['quiz' => old('quiz_id', $quizzes->first()?->id ?? 0)]) }}" method="POST" class="mt-5 grid gap-4" id="question-form">
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
                            <input name="correct_answer" value="{{ old('correct_answer') }}" placeholder="Correct answer" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                            <input name="wrong_answer_1" value="{{ old('wrong_answer_1') }}" placeholder="Wrong answer 1" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                            <input name="wrong_answer_2" value="{{ old('wrong_answer_2') }}" placeholder="Wrong answer 2" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
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
                                    <td class="px-4 py-3">{{ $quiz->title }}</td>
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
                                        $correctAnswer = $question->answers->firstWhere('is_correct', true);
                                        $wrongAnswers = $question->answers->where('is_correct', false)->values();
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
                                            <form action="{{ route('admin.quiz-questions.update', $question) }}" method="POST" class="grid gap-3 md:grid-cols-2">
                                                @csrf
                                                @method('PATCH')
                                                <input name="prompt" value="{{ $question->prompt }}" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-3 py-2 md:col-span-2" required>
                                                <input type="number" name="points" value="{{ $question->points }}" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-3 py-2" min="1" required>
                                                <input type="number" name="sort_order" value="{{ $question->sort_order }}" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-3 py-2" min="1" required>
                                                <input name="correct_answer" value="{{ $correctAnswer?->body }}" placeholder="Correct answer" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-3 py-2" required>
                                                <input name="wrong_answer_1" value="{{ $wrongAnswers->get(0)?->body }}" placeholder="Wrong answer 1" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-3 py-2" required>
                                                <input name="wrong_answer_2" value="{{ $wrongAnswers->get(1)?->body }}" placeholder="Wrong answer 2" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-3 py-2 md:col-span-2" required>
                                                <div class="md:col-span-2">
                                                    <button class="rounded-full bg-[#D8A83E] px-4 py-2 text-xs font-semibold text-[#1B0D05]">Save question changes</button>
                                                </div>
                                            </form>
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
                if (!form || !select) {
                    return;
                }

                function updateAction() {
                    const quizId = select.value;
                    if (!quizId) {
                        return;
                    }
                    form.action = '{{ url('/admin/quizzes') }}/' + quizId + '/questions';
                }

                select.addEventListener('change', updateAction);
                updateAction();
            })();
        </script>
    </main>
</div>
</body>
</html>
