<?php

use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function bookWithPrivatePdf(string $slug): Book
{
    return Book::create([
        'title' => 'Private Read',
        'slug' => $slug,
        'page_count' => 12,
        'status' => 'published',
        'pdf_path' => 'books/pdfs/'.$slug.'.pdf',
    ]);
}

test('web PDF reader streams book files only to authenticated readers', function () {
    Storage::fake('local');
    $book = bookWithPrivatePdf('private-web-book');
    Storage::disk('local')->put($book->pdf_path, '%PDF-1.4 web book');
    $url = route('reader.books.content', $book->slug);

    $this->get($url)->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create(['role' => 'author']))->get($url)->assertForbidden();
    $this->actingAs(User::factory()->create())->get($url)
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Cache-Control', 'max-age=0, no-store, private');
});

test('android API streams private PDFs with a reader bearer token', function () {
    Storage::fake('local');
    $book = bookWithPrivatePdf('private-api-book');
    Storage::disk('local')->put($book->pdf_path, '%PDF-1.4 android book');
    $url = '/api/books/'.$book->id.'/content';

    $this->get($url)->assertUnauthorized();
    $reader = User::factory()->create();
    $this->actingAs($reader, 'sanctum')->get($url)
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    $this->actingAs($reader, 'sanctum')->getJson('/api/books/'.$book->id)
        ->assertOk()
        ->assertJsonPath('data.pdf_url', route('api.books.content', ['book' => $book->id]));
});
