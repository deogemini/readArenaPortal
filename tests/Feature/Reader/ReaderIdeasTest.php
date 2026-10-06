<?php

use App\Models\ReaderIdea;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('android readers can submit an improvement idea with a private attachment and view its status', function () {
    Storage::fake('local');
    $reader = User::factory()->create();
    $attachment = UploadedFile::fake()->create('screen.pdf', 120, 'application/pdf');

    $created = $this->actingAs($reader, 'sanctum')->post('/api/feedback', [
        'title' => 'Make quiz progress easier to find',
        'description' => 'Please show completed quiz progress on the book page.',
        'category' => 'quizzes',
        'attachment' => $attachment,
    ])->assertCreated()->assertJsonPath('data.status', 'new')->assertJsonPath('data.category', 'quizzes')->json('data');

    $idea = ReaderIdea::findOrFail($created['id']);
    Storage::disk('local')->assertExists($idea->attachment_path);
    expect($idea->attachment_name)->toBe('screen.pdf');

    $this->actingAs($reader, 'sanctum')->getJson('/api/feedback')
        ->assertOk()->assertJsonPath('data.0.id', $idea->id)->assertJsonPath('meta.total', 1);
    $this->actingAs($reader, 'sanctum')->getJson('/api/feedback/'.$idea->id)
        ->assertOk()->assertJsonPath('data.attachment.name', 'screen.pdf')->assertJsonMissingPath('data.admin_notes');
    $this->actingAs($reader, 'sanctum')->get('/api/feedback/'.$idea->id.'/attachment')->assertOk();
});

test('reader ideas and their attachments are private to the submitting reader', function () {
    Storage::fake('local');
    $reader = User::factory()->create();
    $otherReader = User::factory()->create();
    $idea = $reader->readerIdeas()->create([
        'title' => 'Reader idea',
        'description' => 'Suggestion details.',
        'category' => 'app',
        'attachment_path' => 'reader-ideas/private.txt',
        'attachment_name' => 'private.txt',
    ]);
    Storage::disk('local')->put($idea->attachment_path, 'private attachment');

    $this->actingAs($otherReader, 'sanctum')->getJson('/api/feedback/'.$idea->id)->assertNotFound();
    $this->actingAs($otherReader, 'sanctum')->get('/api/feedback/'.$idea->id.'/attachment')->assertNotFound();
    $this->actingAs($otherReader, 'sanctum')->getJson('/api/feedback')->assertJsonCount(0, 'data');
});

test('non-readers cannot submit feedback and invalid attachment types are rejected', function () {
    $author = User::factory()->create(['role' => 'author']);
    $admin = User::factory()->admin()->create();
    $payload = ['title' => 'An idea', 'description' => 'An improvement for readers.'];

    $this->actingAs($author, 'sanctum')->postJson('/api/feedback', $payload)->assertForbidden();
    $this->actingAs($admin, 'sanctum')->postJson('/api/feedback', $payload)->assertForbidden();
    $this->actingAs(User::factory()->create(), 'sanctum')->withHeader('Accept', 'application/json')->post('/api/feedback', $payload + [
        'attachment' => UploadedFile::fake()->create('program.exe', 10, 'application/octet-stream'),
    ])->assertUnprocessable()->assertJsonValidationErrors('attachment');

    $this->assertDatabaseCount('reader_ideas', 0);
});

test('admins can review reader ideas and download attachments from the portal', function () {
    Storage::fake('local');
    $admin = User::factory()->admin()->create();
    $reader = User::factory()->create();
    $idea = $reader->readerIdeas()->create([
        'title' => 'Add reading reminders',
        'description' => 'Let readers schedule reminders.',
        'category' => 'app',
        'attachment_path' => 'reader-ideas/reminder.png',
        'attachment_name' => 'reminder.png',
        'attachment_mime' => 'image/png',
        'attachment_size' => 10,
    ]);
    Storage::disk('local')->put($idea->attachment_path, 'private image bytes');

    $this->actingAs($admin)->get(route('admin.reader-ideas'))->assertOk()->assertSee('Add reading reminders')->assertSee($reader->email);
    $this->actingAs($admin)->patch(route('admin.reader-ideas.update', $idea), [
        'status' => 'planned',
        'admin_notes' => 'Include in next planning session.',
    ])->assertRedirect(route('admin.reader-ideas'));
    expect($idea->fresh()->status)->toBe('planned')->and($idea->fresh()->admin_notes)->toBe('Include in next planning session.');
    $this->actingAs($admin)->get(route('admin.reader-ideas.attachment', $idea))->assertOk();
    $this->actingAs($reader)->get(route('admin.reader-ideas'))->assertForbidden();
});
