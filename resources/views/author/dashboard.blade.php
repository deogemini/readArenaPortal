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
                        <p class="mt-1 text-sm text-[#5e544d]">PDF: {{ $book->pdf_path ? 'Uploaded' : 'Missing' }}</p>
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
