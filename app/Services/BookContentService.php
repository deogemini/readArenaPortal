<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookContentService
{
    public function manifest(Book $book): array
    {
        abort_unless($book->status === 'published' && $book->pdf_path, 404);
        $stored = Storage::disk('local')->exists($book->pdf_path) || Storage::disk('public')->exists($book->pdf_path);
        abort_unless($stored, 404);

        return [
            'book_id' => $book->id,
            'title' => $book->title,
            'format' => 'pdf',
            'page_count' => $book->page_count,
            'chapters' => [[
                'id' => 'full-book',
                'number' => 1,
                'title' => $book->title,
                'start_page' => 1,
                'end_page' => $book->page_count,
                'content_url' => route('api.books.content.pdf', $book),
                'content_type' => 'application/pdf',
            ]],
        ];
    }

    public function stream(Book $book): StreamedResponse
    {
        abort_unless($book->status === 'published' && $book->pdf_path, 404);

        $disk = Storage::disk('local');
        if (! $disk->exists($book->pdf_path)) {
            $disk = Storage::disk('public');
        }
        abort_unless($disk->exists($book->pdf_path), 404);

        return $disk->response($book->pdf_path, $book->slug.'.pdf', [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline; filename="'.$book->slug.'.pdf"',
        ]);
    }
}
