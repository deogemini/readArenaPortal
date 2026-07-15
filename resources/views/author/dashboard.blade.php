<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Author Dashboard | BookDuel</title>
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
        <section class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
            <h2 class="font-serif text-2xl">Upload a Book</h2>
            <form action="{{ route('author.books.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <input name="title" placeholder="Book title" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required>
                <textarea name="description" placeholder="Description" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2"></textarea>
                <input name="genre" placeholder="Genre" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required>
                <input type="number" name="publication_year" placeholder="Publication year" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2">
                <input type="number" name="page_count" placeholder="Page count" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2">
                <input type="url" name="cover_image" placeholder="Cover image URL" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2">
                <button class="rounded-full bg-[#1B0D05] px-5 py-2 text-sm font-semibold text-[#FBF6EA]">Save book</button>
            </form>
        </section>

        <section class="rounded-[24px] border border-[#d8c9ad] bg-[#FBF6EA] p-6">
            <h2 class="font-serif text-2xl">Create a Quiz</h2>
            <form action="{{ route('author.quizzes.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <select name="book_id" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required>
                    <option value="">Select book</option>
                    @foreach($books as $book)
                        <option value="{{ $book->id }}">{{ $book->title }}</option>
                    @endforeach
                </select>
                <input name="title" placeholder="Quiz title" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required>
                <textarea name="question" placeholder="Question" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required></textarea>
                <input name="correct_answer" placeholder="Correct answer" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required>
                <input name="wrong_answer_1" placeholder="Wrong answer 1" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required>
                <input name="wrong_answer_2" placeholder="Wrong answer 2" class="w-full rounded-xl border border-[#d8c9ad] bg-white px-4 py-2" required>
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
                    </div>
                @empty
                    <p class="text-sm text-[#5e544d]">No books yet. Upload your first title above.</p>
                @endforelse
            </div>
        </section>
    </main>
</div>
</body>
</html>
