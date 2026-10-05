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
                    <h2 class="font-serif text-2xl">Add Book Details</h2>
                    <form action="{{ route('admin.books.store') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
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
                            <button class="rounded-full bg-[#D8A83E] px-6 py-2 text-sm font-semibold text-[#1B0D05]">Save book details</button>
                            <p class="mt-2 text-xs text-[#d8c9ad]">The PDF uploads separately after the book is saved, so a failed file upload will not lose these details.</p>
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
                                <td class="px-4 py-3">
                                    <div class="mb-2">{{ $book->pdf_path ? 'PDF uploaded' : 'PDF missing' }}</div>
                                    <form action="{{ route('admin.books.pdf.store', $book) }}" method="POST" enctype="multipart/form-data" data-chunk-url="{{ route('admin.books.pdf.chunks', $book) }}" data-max-bytes="{{ (int) config('uploads.book_pdf_max_kb', 102400) * 1024 }}" class="min-w-44 space-y-2" data-pdf-chunk-upload>
                                        @csrf
                                        <input type="file" name="pdf_file" accept="application/pdf" required class="block w-52 max-w-full rounded-lg border border-[#3d261b] bg-[#2B170D] px-2 py-1 text-xs">
                                        <button type="submit" data-idle-label="{{ $book->pdf_path ? 'Replace PDF' : 'Upload PDF' }}" class="rounded-full border border-[#d8c9ad] px-3 py-1 text-xs">{{ $book->pdf_path ? 'Replace PDF' : 'Upload PDF' }}</button>
                                        <p class="text-xs text-[#d8c9ad]">PDF only, up to {{ (int) ceil(config('uploads.book_pdf_max_kb', 102400) / 1024) }}MB. Large PDFs upload in small pieces.</p>
                                        <p class="text-xs text-[#D8A83E]" role="status" aria-live="polite" data-upload-status></p>
                                    </form>
                                </td>
                                <td class="px-4 py-3">{{ optional($book->created_at)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">
                                    <details class="mb-2">
                                        <summary class="cursor-pointer rounded-full border border-[#d8c9ad] px-3 py-1 text-center text-xs">Edit</summary>
                                        <form action="{{ route('admin.books.update', $book) }}" method="POST" class="mt-3 grid min-w-64 gap-2 rounded-xl border border-[#3d261b] bg-[#2B170D] p-3">
                                            @csrf @method('PATCH')
                                            <input name="title" value="{{ $book->title }}" required class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" placeholder="Title">
                                            <input name="author_name" value="{{ $book->authors->first()?->name }}" required class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" placeholder="Author">
                                            <input name="genre_name" value="{{ $book->genres->first()?->name }}" required class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" placeholder="Genre">
                                            <input name="publisher_name" value="{{ $book->publisher?->name }}" class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" placeholder="Publisher">
                                            <input type="number" name="publication_year" value="{{ $book->publication_year }}" class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" placeholder="Year">
                                            <input type="number" name="page_count" value="{{ $book->page_count }}" class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" placeholder="Pages">
                                            <input name="language" value="{{ $book->language }}" class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" placeholder="Language">
                                            <input name="isbn" value="{{ $book->isbn }}" class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" placeholder="ISBN">
                                            <input name="cover_image" value="{{ $book->cover_image }}" class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" placeholder="Cover URL">
                                            <textarea name="description" rows="3" class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs" placeholder="Description">{{ $book->description }}</textarea>
                                            <select name="status" class="rounded-lg bg-[#1B0D05] px-3 py-2 text-xs"><option value="draft" @selected($book->status === 'draft')>Draft</option><option value="published" @selected($book->status === 'published')>Published</option></select>
                                            <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="featured" value="1" @checked($book->featured)> Featured</label>
                                            <button class="rounded-full bg-[#D8A83E] px-3 py-2 text-xs font-semibold text-[#1B0D05]">Save book</button>
                                        </form>
                                    </details>
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
<script>
    document.querySelectorAll('[data-pdf-chunk-upload]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const input = form.querySelector('input[type="file"]');
            const button = form.querySelector('button[type="submit"]');
            const status = form.querySelector('[data-upload-status]');
            const file = input.files[0];
            const maxBytes = Number(form.dataset.maxBytes);

            if (!file) {
                status.textContent = 'Choose a PDF file first.';
                return;
            }

            if (file.size > maxBytes) {
                status.textContent = `This PDF is larger than the ${Math.floor(maxBytes / 1048576)} MiB limit.`;
                return;
            }

            const chunkBytes = 512 * 1024;
            const chunkCount = Math.ceil(file.size / chunkBytes);
            const uploadId = crypto.randomUUID();
            const csrfToken = form.querySelector('input[name="_token"]').value;

            button.disabled = true;
            input.disabled = true;
            button.textContent = 'Uploading...';

            try {
                for (let index = 0; index < chunkCount; index++) {
                    const start = index * chunkBytes;
                    const chunk = file.slice(start, Math.min(start + chunkBytes, file.size), 'application/octet-stream');
                    status.textContent = `Uploading part ${index + 1} of ${chunkCount}...`;

                    const response = await fetch(form.dataset.chunkUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/octet-stream',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Upload-Id': uploadId,
                            'X-Chunk-Index': String(index),
                            'X-Chunk-Count': String(chunkCount),
                            'X-Total-Size': String(file.size),
                        },
                        body: chunk,
                    });
                    const result = await response.json().catch(() => null);

                    if (!response.ok) {
                        const message = response.status === 413
                            ? 'The server rejected a 512 KB upload part. Raise Apache LimitRequestBody and PHP post_max_size, then retry.'
                            : `Server rejected upload part ${index + 1} (HTTP ${response.status}).`;
                        throw new Error(result?.message ?? message);
                    }

                    if (result?.complete) {
                        status.textContent = result.message ?? 'PDF uploaded. Refreshing...';
                        window.location.reload();
                        return;
                    }
                }

                throw new Error('The server did not confirm the complete PDF. Please retry.');
            } catch (error) {
                status.textContent = `Upload failed: ${error.message}`;
                button.disabled = false;
                input.disabled = false;
                button.textContent = button.dataset.idleLabel;
            }
        });
    });
</script>
</body>
</html>
