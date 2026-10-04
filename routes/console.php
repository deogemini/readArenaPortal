<?php

use Illuminate\Foundation\Inspiring;
use App\Models\Book;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('books:protect-pdfs', function () {
    $moved = 0;
    $public = Storage::disk('public');
    $private = Storage::disk('local');

    foreach (Book::query()->whereNotNull('pdf_path')->cursor() as $book) {
        $path = $book->pdf_path;
        if (! Str::startsWith($path, 'books/pdfs/') || ! $public->exists($path)) {
            continue;
        }

        if (! $private->exists($path)) {
            $source = $public->readStream($path);
            if (! is_resource($source)) {
                $this->error('Could not read '.$path.'; public copy was kept.');
                continue;
            }

            $private->writeStream($path, $source);
            fclose($source);
            if (! $private->exists($path)) {
                $this->error('Could not copy '.$path.'; public copy was kept.');
                continue;
            }
        }

        $public->delete($path);
        $moved++;
    }

    $this->info($moved.' public PDF file(s) moved to private storage.');
})->purpose('Move legacy published book PDFs out of the public storage directory');
