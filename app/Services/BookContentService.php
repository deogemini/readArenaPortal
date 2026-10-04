<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookContentService
{
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
