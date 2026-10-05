<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookReview;
use App\Models\Duel;
use App\Models\Genre;
use App\Models\Lesson;
use App\Models\LiveCompetitionVideo;
use App\Models\LiveShow;
use App\Models\PlatformSetting;
use App\Models\Publisher;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Recommendation;
use App\Models\SmsGatewaySetting;
use App\Models\ShowApplication;
use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Services\QuizAnswerOptions;
use App\Services\ShowParticipationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    private const BOOK_PDF_CHUNK_BYTES = 524288;

    public function index(Request $request)
    {
        $ongoingDuelStatuses = ['pending', 'ongoing', 'in_progress', 'live'];
        $ongoingShowStatuses = ['scheduled', 'ongoing', 'live'];
        $videoSearch = trim($request->string('video_search')->toString());

        $competitionVideosQuery = LiveCompetitionVideo::query()
            ->with('liveShow')
            ->latest();

        if ($videoSearch !== '') {
            $competitionVideosQuery->where('title', 'like', '%'.$videoSearch.'%');
        }

        $recentActivity = collect()
            ->merge(QuizAttempt::query()->with(['user:id,name', 'quiz.book:id,title'])->latest()->limit(5)->get()->map(fn (QuizAttempt $attempt) => [
                'label' => $attempt->passed ? 'Quiz passed' : 'Quiz attempted',
                'detail' => ($attempt->user?->name ?? 'Reader').' scored '.$attempt->score.'% on '.($attempt->quiz?->title ?? 'a quiz'),
                'date' => $attempt->created_at,
            ]))
            ->merge(Duel::query()->with(['book:id,title', 'challenger:id,name', 'opponent:id,name'])->latest()->limit(5)->get()->map(fn (Duel $duel) => [
                'label' => 'Duel '.$duel->status,
                'detail' => ($duel->challenger?->name ?? 'Reader').' challenged '.($duel->opponent?->name ?? 'another reader').' on '.($duel->book?->title ?? 'a book'),
                'date' => $duel->updated_at,
            ]))
            ->merge(LiveShow::query()->latest('updated_at')->limit(5)->get()->map(fn (LiveShow $show) => [
                'label' => 'Show '.$show->status,
                'detail' => $show->title.' · '.$show->start_at?->format('M j, Y g:i A'),
                'date' => $show->updated_at,
            ]))
            ->merge(Lesson::query()->with(['user:id,name', 'book:id,title'])->where('status', 'published')->latest()->limit(5)->get()->map(fn (Lesson $lesson) => [
                'label' => 'Lesson published',
                'detail' => ($lesson->user?->name ?? 'Reader').' shared a lesson from '.($lesson->book?->title ?? 'a book'),
                'date' => $lesson->updated_at,
            ]))
            ->merge(Recommendation::query()->with(['user:id,name', 'book:id,title'])->where('status', 'published')->latest()->limit(5)->get()->map(fn (Recommendation $recommendation) => [
                'label' => 'Book recommended',
                'detail' => ($recommendation->user?->name ?? 'Reader').' recommended '.($recommendation->book?->title ?? 'a book'),
                'date' => $recommendation->updated_at,
            ]))
            ->sortByDesc('date')
            ->take(8)
            ->values();

        return view('admin.dashboard', [
            'readers' => User::where('role', 'reader')->count(),
            'books' => Book::where('status', 'published')->count(),
            'quizzes' => Quiz::count(),
            'shows' => LiveShow::count(),
            'lessons' => Lesson::count(),
            'recommendations' => Recommendation::count(),
            'pendingReviews' => BookReview::where('status', 'pending')->count(),
            'ongoingCompetitions' => Duel::query()->whereIn('status', $ongoingDuelStatuses)->count()
                + LiveShow::query()
                    ->whereIn('status', $ongoingShowStatuses)
                    ->where('start_at', '<=', now())
                    ->count(),
            'liveShows' => LiveShow::query()->orderBy('start_at')->get(['id', 'title']),
            'competitionVideos' => $competitionVideosQuery->paginate(4)->withQueryString(),
            'videoSearch' => $videoSearch,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function storeCompetitionVideo(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'live_show_id' => ['nullable', 'exists:live_shows,id'],
            'video_file' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        $path = $request->file('video_file')->store('competition-videos', 'public');

        LiveCompetitionVideo::create([
            'title' => $payload['title'],
            'live_show_id' => $payload['live_show_id'] ?? null,
            'video_path' => $path,
            'uploaded_by' => $request->user()?->id,
            'status' => $payload['status'] ?? 'published',
        ]);

        return redirect()->route('admin.dashboard')->with('status', 'Competition video uploaded successfully.');
    }

    public function updateCompetitionVideo(Request $request, LiveCompetitionVideo $competitionVideo): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'live_show_id' => ['nullable', 'exists:live_shows,id'],
            'video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $data = [
            'title' => $payload['title'],
            'live_show_id' => $payload['live_show_id'] ?? null,
            'status' => $payload['status'],
        ];

        if ($request->hasFile('video_file')) {
            if ($competitionVideo->video_path && Storage::disk('public')->exists($competitionVideo->video_path)) {
                Storage::disk('public')->delete($competitionVideo->video_path);
            }

            $data['video_path'] = $request->file('video_file')->store('competition-videos', 'public');
        }

        $competitionVideo->update($data);

        return redirect()->route('admin.dashboard')->with('status', 'Competition video updated successfully.');
    }

    public function destroyCompetitionVideo(LiveCompetitionVideo $competitionVideo): RedirectResponse
    {
        if ($competitionVideo->video_path && Storage::disk('public')->exists($competitionVideo->video_path)) {
            Storage::disk('public')->delete($competitionVideo->video_path);
        }

        $competitionVideo->delete();

        return redirect()->route('admin.dashboard')->with('status', 'Competition video deleted successfully.');
    }

    public function books()
    {
        return view('admin.books', [
            'books' => Book::query()->withQuizPerformanceStats()->with(['publisher', 'authors', 'genres'])->latest()->paginate(10),
        ]);
    }

    public function storeBook(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'author_name' => ['required', 'string', 'max:255'],
            'genre_name' => ['required', 'string', 'max:120'],
            'publisher_name' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1400', 'max:2100'],
            'page_count' => ['nullable', 'integer', 'min:1'],
            'language' => ['nullable', 'string', 'max:10'],
            'isbn' => ['nullable', 'string', 'max:100'],
            'cover_image' => ['nullable', 'url'],
            'status' => ['nullable', 'in:draft,published'],
            'featured' => ['nullable', 'boolean'],
        ]);

        $publisher = Publisher::firstOrCreate([
            'name' => $payload['publisher_name'] ?? 'Independent Press',
        ]);

        $book = Book::create([
            'title' => $payload['title'],
            'slug' => Str::slug($payload['title']).'-'.Str::lower(Str::random(6)),
            'description' => $payload['description'] ?? null,
            'publisher_id' => $publisher->id,
            'publication_year' => $payload['publication_year'] ?? null,
            'page_count' => $payload['page_count'] ?? null,
            'language' => $payload['language'] ?? 'en',
            'isbn' => $payload['isbn'] ?? null,
            'cover_image' => $payload['cover_image'] ?? null,
            'pdf_path' => null,
            'featured' => (bool) ($payload['featured'] ?? false),
            'status' => $payload['status'] ?? 'draft',
        ]);

        $author = Author::firstOrCreate(['name' => $payload['author_name']]);
        $genre = Genre::firstOrCreate(
            ['slug' => Str::slug($payload['genre_name'])],
            ['name' => $payload['genre_name']]
        );

        $book->authors()->syncWithoutDetaching([$author->id]);
        $book->genres()->syncWithoutDetaching([$genre->id]);

        return redirect()->route('admin.books')->with('status', 'Book details saved. Upload its PDF from the books list when ready.');
    }

    public function uploadBookPdf(Request $request, Book $book): RedirectResponse
    {
        $maximumPdfSizeKb = (int) config('uploads.book_pdf_max_kb', 102400);
        $maximumPdfSizeMb = (int) ceil($maximumPdfSizeKb / 1024);

        $request->validate([
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:'.$maximumPdfSizeKb],
        ], [
            'pdf_file.required' => 'Choose a PDF file to upload.',
            'pdf_file.mimes' => 'Only PDF files are allowed.',
            'pdf_file.max' => 'PDF must be '.$maximumPdfSizeMb.'MB or smaller.',
        ]);

        $oldPath = $book->pdf_path;
        $pdfPath = $request->file('pdf_file')->store('books/pdfs', 'local');

        if (! $pdfPath) {
            return redirect()->route('admin.books')->withErrors([
                'pdf_file' => 'The PDF could not be saved. Please try again.',
            ]);
        }

        $book->update(['pdf_path' => $pdfPath]);

        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('admin.books')->with('status', 'PDF uploaded for "'.$book->title.'".');
    }

    public function uploadBookPdfChunk(Request $request, Book $book): JsonResponse
    {
        $maximumBytes = (int) config('uploads.book_pdf_max_kb', 102400) * 1024;
        $uploadId = (string) $request->header('X-Upload-Id', '');
        $chunkIndex = filter_var($request->header('X-Chunk-Index'), FILTER_VALIDATE_INT);
        $chunkCount = filter_var($request->header('X-Chunk-Count'), FILTER_VALIDATE_INT);
        $totalSize = filter_var($request->header('X-Total-Size'), FILTER_VALIDATE_INT);

        if (! Str::isUuid($uploadId)
            || $chunkIndex === false
            || $chunkCount === false
            || $totalSize === false
            || $totalSize < 1
            || $totalSize > $maximumBytes
            || $chunkCount !== (int) ceil($totalSize / self::BOOK_PDF_CHUNK_BYTES)
            || $chunkIndex < 0
            || $chunkIndex >= $chunkCount) {
            return response()->json(['message' => 'Invalid PDF upload information. Please choose the PDF again.'], 422);
        }

        $body = $request->getContent();
        $expectedChunkBytes = min(self::BOOK_PDF_CHUNK_BYTES, $totalSize - ($chunkIndex * self::BOOK_PDF_CHUNK_BYTES));

        if (strlen($body) !== $expectedChunkBytes) {
            return response()->json(['message' => 'A PDF upload part was incomplete. Please retry the upload.'], 422);
        }

        $disk = Storage::disk('local');
        $directory = 'tmp/book-pdf-uploads/'.$request->user()->id.'/'.$book->id.'/'.$uploadId;
        $chunkPath = $directory.'/'.sprintf('%06d.part', $chunkIndex);
        $disk->makeDirectory($directory);

        if (! $disk->put($chunkPath, $body)) {
            return response()->json(['message' => 'The server could not save an upload part. Check available disk space and try again.'], 500);
        }

        $chunkPaths = [];
        for ($index = 0; $index < $chunkCount; $index++) {
            $path = $directory.'/'.sprintf('%06d.part', $index);
            if (! $disk->exists($path)) {
                return response()->json([
                    'complete' => false,
                    'received_parts' => count($disk->files($directory)),
                    'total_parts' => $chunkCount,
                ]);
            }

            $chunkPaths[] = $path;
        }

        $temporaryPdf = tempnam(sys_get_temp_dir(), 'readarena-pdf-');
        if ($temporaryPdf === false) {
            return response()->json(['message' => 'The server could not prepare the PDF. Please try again.'], 500);
        }

        $output = fopen($temporaryPdf, 'wb');
        if ($output === false) {
            @unlink($temporaryPdf);

            return response()->json(['message' => 'The server could not prepare the PDF. Please try again.'], 500);
        }

        $assembledBytes = 0;
        $copyFailed = false;
        foreach ($chunkPaths as $path) {
            $input = fopen($disk->path($path), 'rb');
            if ($input === false) {
                fclose($output);
                @unlink($temporaryPdf);

                return response()->json(['message' => 'The server could not read an upload part. Please try again.'], 500);
            }

            $copiedBytes = stream_copy_to_stream($input, $output);
            if ($copiedBytes === false) {
                $copyFailed = true;
            } else {
                $assembledBytes += $copiedBytes;
            }
            fclose($input);
        }
        fclose($output);

        $header = file_get_contents($temporaryPdf, false, null, 0, 1024);
        if ($copyFailed || $assembledBytes !== $totalSize || $header === false || ! str_contains($header, '%PDF-')) {
            @unlink($temporaryPdf);
            $disk->deleteDirectory($directory);

            return response()->json(['message' => 'The uploaded file is not a valid PDF. Please choose a PDF and retry.'], 422);
        }

        $newPath = 'books/pdfs/'.Str::uuid().'.pdf';
        $pdfStream = fopen($temporaryPdf, 'rb');
        $stored = $pdfStream !== false && $disk->writeStream($newPath, $pdfStream);
        if (is_resource($pdfStream)) {
            fclose($pdfStream);
        }
        @unlink($temporaryPdf);

        if (! $stored) {
            return response()->json(['message' => 'The server could not save the finished PDF. Check available disk space and try again.'], 500);
        }

        $oldPath = $book->pdf_path;
        try {
            $book->update(['pdf_path' => $newPath]);
        } catch (\Throwable $exception) {
            $disk->delete($newPath);
            $disk->deleteDirectory($directory);

            throw $exception;
        }

        $disk->deleteDirectory($directory);

        if ($oldPath) {
            $disk->delete($oldPath);
            Storage::disk('public')->delete($oldPath);
        }

        return response()->json(['complete' => true, 'message' => 'PDF uploaded successfully.']);
    }

    public function destroyBook(Book $book): RedirectResponse
    {
        if ($book->quizzes()->whereHas('attempts')->exists()) {
            return redirect()->route('admin.books')->withErrors([
                'book_delete' => 'This book has quiz history and cannot be deleted.',
            ]);
        }

        if ($book->pdf_path) {
            Storage::disk('local')->delete($book->pdf_path);
            Storage::disk('public')->delete($book->pdf_path);
        }

        $book->delete();

        return redirect()->route('admin.books')->with('status', 'Book deleted successfully.');
    }

    public function updateBook(Request $request, Book $book): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'author_name' => ['required', 'string', 'max:255'],
            'genre_name' => ['required', 'string', 'max:120'],
            'publisher_name' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1400', 'max:2100'],
            'page_count' => ['nullable', 'integer', 'min:1'],
            'language' => ['nullable', 'string', 'max:10'],
            'isbn' => ['nullable', 'string', 'max:100'],
            'cover_image' => ['nullable', 'url'],
            'status' => ['required', 'in:draft,published'],
            'featured' => ['nullable', 'boolean'],
        ]);

        $publisher = Publisher::firstOrCreate(['name' => $payload['publisher_name'] ?: 'Independent Press']);
        $data = [
            'title' => $payload['title'],
            'slug' => $book->title === $payload['title'] ? $book->slug : Str::slug($payload['title']).'-'.Str::lower(Str::random(6)),
            'description' => $payload['description'] ?? null,
            'publisher_id' => $publisher->id,
            'publication_year' => $payload['publication_year'] ?? null,
            'page_count' => $payload['page_count'] ?? null,
            'language' => $payload['language'] ?? 'en',
            'isbn' => $payload['isbn'] ?? null,
            'cover_image' => $payload['cover_image'] ?? null,
            'featured' => (bool) ($payload['featured'] ?? false),
            'status' => $payload['status'],
        ];

        $book->update($data);
        $book->authors()->sync([Author::firstOrCreate(['name' => $payload['author_name']])->id]);
        $genre = Genre::firstOrCreate(['slug' => Str::slug($payload['genre_name'])], ['name' => $payload['genre_name']]);
        $book->genres()->sync([$genre->id]);

        return redirect()->route('admin.books')->with('status', 'Book updated successfully.');
    }

    public function quizzes()
    {
        return view('admin.quizzes', [
            'quizzes' => Quiz::with(['book', 'questions.answers', 'questions.editor'])->withCount('attempts')->latest()->paginate(10),
            'books' => Book::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function updateQuiz(Request $request, Quiz $quiz): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'pass_mark' => ['required', 'integer', 'min:1', 'max:100'],
            'attempt_limit' => ['required', 'integer', 'min:1'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published'],
        ]);

        if ($payload['status'] === 'published' && ! Book::whereKey($quiz->book_id)->where('status', 'published')->exists()) {
            return back()->withInput()->withErrors(['status' => 'Publish the related book before publishing its quiz.']);
        }

        $quiz->update($payload);

        return redirect()->route('admin.quizzes')->with('status', 'Quiz updated successfully.');
    }

    public function updateQuizQuestion(Request $request, QuizQuestion $question): RedirectResponse
    {
        $payload = $request->validate([
            'prompt' => [
                'required',
                'string',
                Rule::unique('quiz_questions', 'prompt')->where('quiz_id', $question->quiz_id)->ignore($question->id),
            ],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ], [
            'prompt.unique' => 'This question already exists in this quiz.',
        ]);
        $answerPayload = QuizAnswerOptions::validate(
            $request->input('question_answer_options.'.$question->id),
            $request->input('question_correct_options.'.$question->id),
        );

        DB::transaction(function () use ($question, $payload, $answerPayload): void {
            $question->update([
                'prompt' => $payload['prompt'],
                'points' => (int) $payload['points'],
                'sort_order' => (int) $payload['sort_order'],
                'last_edited_by' => auth()->id(),
                'last_edited_at' => now(),
            ]);

            QuizAnswerOptions::sync($question, $answerPayload);
        });

        return redirect()->route('admin.quizzes')->with('status', 'Question updated successfully.');
    }

    public function destroyQuizQuestion(QuizQuestion $question): RedirectResponse
    {
        if ($question->quiz()->whereHas('attempts')->exists()) {
            return redirect()->route('admin.quizzes')->withErrors([
                'question_delete' => 'Questions on a quiz with reader attempts cannot be deleted.',
            ]);
        }

        $question->delete();

        return redirect()->route('admin.quizzes')->with('status', 'Question deleted successfully.');
    }

    public function destroyQuiz(Quiz $quiz): RedirectResponse
    {
        if ($quiz->attempts()->exists()) {
            return redirect()->route('admin.quizzes')->withErrors([
                'quiz_delete' => 'This quiz already has reader attempts and cannot be deleted.',
            ]);
        }

        $quiz->delete();

        return redirect()->route('admin.quizzes')->with('status', 'Quiz deleted successfully.');
    }

    public function storeQuiz(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'pass_mark' => ['nullable', 'integer', 'min:1', 'max:100'],
            'attempt_limit' => ['nullable', 'integer', 'min:1'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        Quiz::create([
            'book_id' => (int) $payload['book_id'],
            'title' => $payload['title'],
            'instructions' => $payload['instructions'] ?? null,
            'pass_mark' => $payload['pass_mark'] ?? 70,
            'attempt_limit' => $payload['attempt_limit'] ?? 3,
            'duration_minutes' => $payload['duration_minutes'] ?? 10,
            'status' => $payload['status'] ?? 'draft',
        ]);

        return redirect()->route('admin.quizzes')->with('status', 'Quiz created successfully.');
    }

    public function storeQuizQuestion(Request $request, Quiz $quiz): RedirectResponse
    {
        $payload = $request->validate([
            'prompt' => [
                'required',
                'string',
                Rule::unique('quiz_questions', 'prompt')->where('quiz_id', $quiz->id),
            ],
            'points' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
        ], [
            'prompt.unique' => 'This question already exists in this quiz.',
        ]);
        $answerPayload = QuizAnswerOptions::validate(
            $request->input('answer_options'),
            $request->input('correct_options'),
        );

        DB::transaction(function () use ($quiz, $payload, $answerPayload): void {
            $question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'prompt' => $payload['prompt'],
                'question_type' => 'multiple_choice',
                'points' => $payload['points'] ?? 10,
                'sort_order' => $payload['sort_order'] ?? ((int) $quiz->questions()->max('sort_order') + 1 ?: 1),
            ]);

            QuizAnswerOptions::sync($question, $answerPayload);
        });

        return redirect()->route('admin.quizzes')->with('status', 'Question added to quiz.');
    }

    public function duels()
    {
        return view('admin.duels', [
            'duels' => Duel::query()
                ->with(['book:id,title', 'challenger:id,name,email', 'opponent:id,name,email'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function updateDuelStatus(Request $request, Duel $duel): RedirectResponse
    {
        $payload = $request->validate(['status' => ['required', 'in:cancelled,disputed']]);
        if (in_array($duel->status, ['completed', 'rejected', 'cancelled'], true)) {
            return redirect()->route('admin.duels')->withErrors(['duel' => 'This duel is already closed.']);
        }

        $duel->update(['status' => $payload['status']]);

        return redirect()->route('admin.duels')->with('status', 'Duel status updated.');
    }

    public function reviews()
    {
        return view('admin.reviews', [
            'reviews' => BookReview::query()->with(['book:id,title,slug', 'user:id,name,email'])->latest()->paginate(20),
        ]);
    }

    public function moderateReview(Request $request, BookReview $review): RedirectResponse
    {
        $payload = $request->validate(['status' => ['required', 'in:published,rejected']]);
        $review->update(['status' => $payload['status']]);

        return redirect()->route('admin.reviews')->with('status', 'Review '.$payload['status'].'.');
    }

    public function shows()
    {
        return view('admin.shows', [
            'shows' => LiveShow::with(['book', 'applications.user'])->withCount('applications')->orderByDesc('start_at')->paginate(10),
            'books' => Book::where('status', 'published')->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function reviewShowApplication(Request $request, ShowApplication $application, ShowParticipationService $participation): RedirectResponse
    {
        $payload = $request->validate(['status' => ['required', 'in:approved,rejected']]);
        $participation->review($application, $payload['status']);

        return redirect()->route('admin.shows')->with('status', 'Guest application '.$payload['status'].'.');
    }

    public function storeShow(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'book_id' => ['nullable', 'integer', 'exists:books,id'],
            'start_at' => ['required', 'date'],
            'status' => ['required', 'in:scheduled,live,completed,cancelled'],
        ]);

        if (! empty($payload['book_id']) && ! Book::whereKey($payload['book_id'])->where('status', 'published')->exists()) {
            return back()->withInput()->withErrors(['book_id' => 'Choose a published book.']);
        }

        LiveShow::create($payload);

        return redirect()->route('admin.shows')->with('status', 'Show created successfully.');
    }

    public function updateShow(Request $request, LiveShow $show): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'book_id' => ['nullable', 'integer', 'exists:books,id'],
            'start_at' => ['required', 'date'],
            'status' => ['required', 'in:scheduled,live,completed,cancelled'],
        ]);

        if (! empty($payload['book_id']) && ! Book::whereKey($payload['book_id'])->where('status', 'published')->exists()) {
            return back()->withInput()->withErrors(['book_id' => 'Choose a published book.']);
        }

        $show->update($payload);

        return redirect()->route('admin.shows')->with('status', 'Show updated successfully.');
    }

    public function destroyShow(LiveShow $show): RedirectResponse
    {
        $show->delete();

        return redirect()->route('admin.shows')->with('status', 'Show deleted successfully.');
    }

    public function settings()
    {
        $roles = ['admin', 'author', 'reader'];
        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->whereIn('role', $roles)
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('admin.settings', [
            'settings' => PlatformSetting::orderBy('key')->get(),
            'smsGatewaySetting' => SmsGatewaySetting::query()->latest('id')->first(),
            'roleSummaries' => collect($roles)->map(function (string $role) use ($roleCounts) {
                return [
                    'role' => $role,
                    'count' => (int) ($roleCounts[$role] ?? 0),
                ];
            }),
        ]);
    }

    public function updateSmsGatewaySettings(Request $request)
    {
        $payload = $request->validate([
            'base_url' => ['required', 'url', 'max:255'],
            'client_id' => ['required', 'string', 'max:255'],
            'client_secret' => ['required', 'string', 'max:255'],
            'sender_id' => ['required', 'string', 'max:20'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $settings = SmsGatewaySetting::query()->latest('id')->first();

        if (!$settings) {
            SmsGatewaySetting::create([
                'base_url' => $payload['base_url'],
                'client_id' => $payload['client_id'],
                'client_secret' => $payload['client_secret'],
                'sender_id' => $payload['sender_id'],
                'is_enabled' => (bool) ($payload['is_enabled'] ?? false),
            ]);
        } else {
            $settings->update([
                'base_url' => $payload['base_url'],
                'client_id' => $payload['client_id'],
                'client_secret' => $payload['client_secret'],
                'sender_id' => $payload['sender_id'],
                'is_enabled' => (bool) ($payload['is_enabled'] ?? false),
            ]);
        }

        return redirect()->route('admin.settings')->with('status', 'SMS gateway settings saved successfully.');
    }

    public function users(Request $request)
    {
        $role = $request->string('role')->toString();
        $activity = $request->string('activity')->toString();
        $ongoingDuelStatuses = ['pending', 'ongoing', 'in_progress', 'live'];
        $query = User::query()
            ->withCount([
                'readingGoals as active_goals_count' => function (Builder $builder) {
                    $builder->where('status', 'active');
                },
                'challengerDuels as active_challenger_duels_count' => function (Builder $builder) use ($ongoingDuelStatuses) {
                    $builder->whereIn('status', $ongoingDuelStatuses);
                },
                'opponentDuels as active_opponent_duels_count' => function (Builder $builder) use ($ongoingDuelStatuses) {
                    $builder->whereIn('status', $ongoingDuelStatuses);
                },
            ])
            ->latest();

        if (in_array($role, ['reader', 'author', 'admin'], true)) {
            $query->where('role', $role);
        }

        if ($activity === 'active') {
            $query->where(function (Builder $builder) use ($ongoingDuelStatuses) {
                $builder
                    ->whereHas('readingGoals', function (Builder $goals) {
                        $goals->where('status', 'active');
                    })
                    ->orWhereHas('challengerDuels', function (Builder $duels) use ($ongoingDuelStatuses) {
                        $duels->whereIn('status', $ongoingDuelStatuses);
                    })
                    ->orWhereHas('opponentDuels', function (Builder $duels) use ($ongoingDuelStatuses) {
                        $duels->whereIn('status', $ongoingDuelStatuses);
                    });
            });
        }

        if ($activity === 'inactive') {
            $query
                ->whereDoesntHave('readingGoals', function (Builder $goals) {
                    $goals->where('status', 'active');
                })
                ->whereDoesntHave('challengerDuels', function (Builder $duels) use ($ongoingDuelStatuses) {
                    $duels->whereIn('status', $ongoingDuelStatuses);
                })
                ->whereDoesntHave('opponentDuels', function (Builder $duels) use ($ongoingDuelStatuses) {
                    $duels->whereIn('status', $ongoingDuelStatuses);
                });
        }

        $users = $query->paginate(20)->withQueryString();

        $users->getCollection()->transform(function (User $user) {
            $ongoingDuels = (int) $user->active_challenger_duels_count + (int) $user->active_opponent_duels_count;
            $user->ongoing_activities_count = (int) $user->active_goals_count + $ongoingDuels;

            return $user;
        });

        return view('admin.users', [
            'users' => $users,
            'selectedRole' => $role,
            'selectedActivity' => $activity,
        ]);
    }

    public function packages()
    {
        return view('admin.packages', [
            'packages' => SubscriptionPackage::latest()->paginate(20),
        ]);
    }

    public function storePackage(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:subscription_packages,name'],
            'price_tsh' => ['required', 'integer', 'min:1'],
            'games_count' => ['required', 'integer', 'min:1'],
            'reward_label' => ['nullable', 'string', 'max:255'],
            'region_scope' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        SubscriptionPackage::create([
            'name' => $payload['name'],
            'price_tsh' => $payload['price_tsh'],
            'games_count' => $payload['games_count'],
            'reward_label' => $payload['reward_label'] ?? null,
            'region_scope' => $payload['region_scope'] ?? 'global',
            'status' => $payload['status'] ?? 'active',
        ]);

        return redirect()->route('admin.packages')->with('status', 'Subscription package created.');
    }

    public function updatePackage(Request $request, SubscriptionPackage $package): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subscription_packages', 'name')->ignore($package->id)],
            'price_tsh' => ['required', 'integer', 'min:1'],
            'games_count' => ['required', 'integer', 'min:1'],
            'reward_label' => ['nullable', 'string', 'max:255'],
            'region_scope' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ]);
        $package->update($payload);

        return redirect()->route('admin.packages')->with('status', 'Subscription package updated.');
    }

    public function destroyPackage(SubscriptionPackage $package): RedirectResponse
    {
        $package->delete();

        return redirect()->route('admin.packages')->with('status', 'Subscription package deleted.');
    }
}
