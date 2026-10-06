<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReaderIdea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReaderIdeaController extends Controller
{
    private const CATEGORIES = ['app', 'books', 'quizzes', 'community', 'other'];

    public function index(Request $request)
    {
        $this->ensureReader($request);
        $perPage = min(max((int) $request->query('per_page', 15), 1), 50);

        $ideas = ReaderIdea::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $ideas->getCollection()->map(fn (ReaderIdea $idea) => $this->ideaData($idea)),
            'meta' => [
                'current_page' => $ideas->currentPage(),
                'last_page' => $ideas->lastPage(),
                'per_page' => $ideas->perPage(),
                'total' => $ideas->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureReader($request);

        $payload = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:10000'],
            'category' => ['nullable', 'string', Rule::in(self::CATEGORIES)],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,txt,doc,docx'],
        ]);

        $attachment = $request->file('attachment');
        $path = $attachment?->store('reader-ideas', 'local');

        if ($attachment && ! $path) {
            return response()->json(['message' => 'The attachment could not be saved. Please try again.'], 500);
        }

        $idea = $request->user()->readerIdeas()->create([
            'title' => $payload['title'],
            'description' => $payload['description'],
            'category' => $payload['category'] ?? 'other',
            'attachment_path' => $path,
            'attachment_name' => $attachment ? mb_substr(basename(str_replace('\\', '/', $attachment->getClientOriginalName())), 0, 255) : null,
            'attachment_mime' => $attachment?->getMimeType(),
            'attachment_size' => $attachment?->getSize(),
        ]);

        return response()->json([
            'message' => __('Your improvement idea was submitted.'),
            'data' => $this->ideaData($idea),
        ], 201);
    }

    public function show(Request $request, ReaderIdea $idea)
    {
        $this->ensureReader($request);
        abort_unless((int) $idea->user_id === (int) $request->user()->id, 404);

        return response()->json(['data' => $this->ideaData($idea)]);
    }

    public function downloadAttachment(Request $request, ReaderIdea $idea)
    {
        abort_unless($request->user()->isAdmin() || ($request->user()->isReader() && (int) $idea->user_id === (int) $request->user()->id), 404);
        abort_unless($idea->attachment_path && Storage::disk('local')->exists($idea->attachment_path), 404);

        return Storage::disk('local')->download($idea->attachment_path, $idea->attachment_name ?: 'reader-idea-attachment', [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function ensureReader(Request $request): void
    {
        abort_unless($request->user()?->isReader(), 403, 'Only reader accounts can submit improvement ideas.');
    }

    private function ideaData(ReaderIdea $idea): array
    {
        return [
            'id' => $idea->id,
            'title' => $idea->title,
            'description' => $idea->description,
            'category' => $idea->category,
            'status' => $idea->status,
            'attachment' => $idea->attachment_path ? [
                'name' => $idea->attachment_name,
                'mime_type' => $idea->attachment_mime,
                'size_bytes' => $idea->attachment_size,
                'download_url' => route('api.reader-ideas.attachment', $idea),
            ] : null,
            'created_at' => $idea->created_at?->toISOString(),
            'updated_at' => $idea->updated_at?->toISOString(),
        ];
    }
}
