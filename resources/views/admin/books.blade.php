<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Books | ReadArena</title>
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
            <a href="/admin/books" class="block rounded-[14px] bg-[#2B170D] px-4 py-3">Books</a>
            <a href="/admin/quizzes" class="block rounded-[14px] px-4 py-3 hover:bg-[#2B170D]">Quizzes</a>
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
                    <p class="text-sm uppercase tracking-[0.3em] text-[#D8A83E]">Catalog management</p>
                    <h1 class="mt-2 font-serif text-3xl">Books</h1>
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

                <section class="mb-6 rounded-[20px] border border-[#3d261b] bg-[#2B170D] p-6">
                    <h2 class="font-serif text-2xl">Upload Book</h2>
                    <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                        @csrf
                        <input name="title" value="{{ old('title') }}" placeholder="Book title" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input name="author_name" value="{{ old('author_name') }}" placeholder="Author name" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input name="genre_name" value="{{ old('genre_name') }}" placeholder="Genre" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2" required>
                        <input name="publisher_name" value="{{ old('publisher_name') }}" placeholder="Publisher" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <input type="number" name="publication_year" value="{{ old('publication_year') }}" placeholder="Publication year" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <input type="number" name="page_count" value="{{ old('page_count') }}" placeholder="Page count" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <input name="language" value="{{ old('language', 'en') }}" placeholder="Language code (en)" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <input name="isbn" value="{{ old('isbn') }}" placeholder="ISBN" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                        <input name="cover_image" value="{{ old('cover_image') }}" placeholder="Cover image URL" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2 md:col-span-2">
                        <div class="md:col-span-2">
                            <input type="file" name="pdf_file" accept="application/pdf" required class="w-full rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                            <p class="mt-1 text-xs text-[#d8c9ad]">PDF required. Current maximum upload size is 3MB.</p>
                        </div>
                        <textarea name="description" placeholder="Description" rows="4" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2 md:col-span-2">{{ old('description') }}</textarea>
                        <select name="status" class="rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2">
                            <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                            <option value="published" @selected(old('status') === 'published')>Published</option>
                        </select>
                        <label class="flex items-center gap-2 rounded-xl border border-[#3d261b] bg-[#1B0D05] px-4 py-2 text-sm">
                            <input type="checkbox" name="featured" value="1" @checked(old('featured'))>
                            Mark as featured
                        </label>
                        <div class="md:col-span-2">
                            <button class="rounded-full bg-[#D8A83E] px-6 py-2 text-sm font-semibold text-[#1B0D05]">Upload book</button>
                        </div>
                    </form>
                </section>

                <div class="overflow-hidden rounded-[18px] border border-[#3d261b]">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#2B170D]">
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Author</th>
                            <th class="px-4 py-3">Genre</th>
                            <th class="px-4 py-3">Publisher</th>
                            <th class="px-4 py-3">Year</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Featured</th>
                            <th class="px-4 py-3">PDF</th>
                            <th class="px-4 py-3">Created</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#1B0D05]">
                        @forelse($books as $book)
                            <tr class="border-t border-[#3d261b]">
                                <td class="px-4 py-3">{{ $book->title }}</td>
                                <td class="px-4 py-3">{{ $book->authors->pluck('name')->join(', ') ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $book->genres->pluck('name')->join(', ') ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $book->publisher->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $book->publication_year ?? '-' }}</td>
                                <td class="px-4 py-3">{{ ucfirst($book->status ?? 'draft') }}</td>
                                <td class="px-4 py-3">{{ $book->featured ? 'Yes' : 'No' }}</td>
                                <td class="px-4 py-3">{{ $book->pdf_path ? 'Uploaded' : 'Missing' }}</td>
                                <td class="px-4 py-3">{{ optional($book->created_at)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">
                                    <form action="{{ route('admin.books.destroy', $book) }}" method="POST" onsubmit="return confirm('Delete this book? This also removes related quizzes and attempts.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-full border border-[#7a2e22] px-3 py-1 text-xs font-semibold text-[#f8d2c8]">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-6 text-[#d8c9ad]">No books found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $books->links() }}</div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
