<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\ReaderNote;
use Illuminate\Http\Request;

class ReaderNoteController extends Controller
{
    public function index(Request $request, Book $book)
    {
        abort_unless($book->status === 'published', 404);

        $notes = ReaderNote::query()
            ->where('user_id', $request->user()->id)
            ->where('book_id', $book->id)
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (ReaderNote $note) => $this->noteData($note));

        return response()->json(['data' => $notes]);
    }

    public function save(Request $request, Book $book, string $noteId)
    {
        abort_unless($book->status === 'published', 404);
        abort_if($noteId === '' || strlen($noteId) > 80, 422, 'Invalid note ID.');

        $pageRules = ['required', 'integer', 'min:1'];
        if ($book->page_count) {
            $pageRules[] = 'max:'.$book->page_count;
        }

        $payload = $request->validate([
            'content' => ['required', 'string', 'max:4000'],
            'page_number' => $pageRules,
        ]);

        $note = ReaderNote::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'book_id' => $book->id,
                'client_id' => $noteId,
            ],
            [
                'content' => $payload['content'],
                'page_number' => (int) $payload['page_number'],
            ],
        );

        return response()->json([
            'message' => 'Reading note saved successfully.',
            'data' => $this->noteData($note),
        ], $note->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, Book $book, string $noteId)
    {
        abort_unless($book->status === 'published', 404);
        abort_if($noteId === '' || strlen($noteId) > 80, 422, 'Invalid note ID.');

        $note = ReaderNote::query()
            ->where('user_id', $request->user()->id)
            ->where('book_id', $book->id)
            ->where('client_id', $noteId)
            ->firstOrFail();

        $note->delete();

        return response()->json(['message' => 'Reading note deleted successfully.']);
    }

    private function noteData(ReaderNote $note): array
    {
        return [
            'id' => $note->client_id,
            'client_id' => $note->client_id,
            'content' => $note->content,
            'page_number' => $note->page_number,
            'created_at' => $note->created_at,
            'updated_at' => $note->updated_at,
        ];
    }
}
