<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReaderIdea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StaffReaderIdeaController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureStaff($request);
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['new', 'reviewing', 'planned', 'completed', 'declined'])],
            'q' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $query = ReaderIdea::query()->with('user:id,name,username,email')->latest();
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['q'])) {
            $term = trim($filters['q']);
            $query->where(fn ($builder) => $builder->where('title', 'like', '%'.$term.'%')
                ->orWhere('description', 'like', '%'.$term.'%')
                ->orWhereHas('user', fn ($users) => $users->where('name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%')));
        }
        $ideas = $query->paginate($filters['per_page'] ?? 20);

        return response()->json([
            'data' => $ideas->getCollection()->map(fn (ReaderIdea $idea) => $this->ideaData($idea))->values(),
            'meta' => [
                'current_page' => $ideas->currentPage(),
                'last_page' => $ideas->lastPage(),
                'per_page' => $ideas->perPage(),
                'total' => $ideas->total(),
            ],
        ]);
    }

    public function show(Request $request, ReaderIdea $idea)
    {
        $this->ensureStaff($request);

        return response()->json(['data' => $this->ideaData($idea->load('user:id,name,username,email'))]);
    }

    public function update(Request $request, ReaderIdea $idea)
    {
        $this->ensureStaff($request);
        $payload = $request->validate([
            'status' => ['required', Rule::in(['new', 'reviewing', 'planned', 'completed', 'declined'])],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $idea->update($payload);

        return response()->json([
            'message' => 'Reader idea updated.',
            'data' => $this->ideaData($idea->fresh('user:id,name,username,email')),
        ]);
    }

    public function attachment(Request $request, ReaderIdea $idea)
    {
        $this->ensureStaff($request);
        abort_unless($idea->attachment_path && Storage::disk('local')->exists($idea->attachment_path), 404);

        return Storage::disk('local')->download($idea->attachment_path, $idea->attachment_name ?: 'reader-idea-attachment', [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function ensureStaff(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Administrator access is required.');
    }

    private function ideaData(ReaderIdea $idea): array
    {
        return [
            'id' => $idea->id,
            'title' => $idea->title,
            'description' => $idea->description,
            'category' => $idea->category,
            'status' => $idea->status,
            'admin_notes' => $idea->admin_notes,
            'reader' => $idea->user ? [
                'id' => $idea->user->id,
                'name' => $idea->user->name,
                'username' => $idea->user->username,
                'email' => $idea->user->email,
            ] : null,
            'attachment' => $idea->attachment_path ? [
                'name' => $idea->attachment_name,
                'mime_type' => $idea->attachment_mime,
                'size_bytes' => $idea->attachment_size,
                'download_url' => route('api.staff-ideas.attachment', $idea),
            ] : null,
            'created_at' => $idea->created_at?->toIso8601String(),
            'updated_at' => $idea->updated_at?->toIso8601String(),
        ];
    }
}
