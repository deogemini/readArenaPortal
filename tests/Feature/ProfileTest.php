<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
    $this->assertNull($user->username);
});

test('readers can set and clear an optional username from the portal and Android API', function () {
    $reader = User::factory()->create();

    $this->actingAs($reader)->patch('/profile', [
        'name' => $reader->name,
        'email' => $reader->email,
        'username' => '  Book_Lover24  ',
    ])->assertSessionHasNoErrors()->assertRedirect('/profile');
    expect($reader->fresh()->username)->toBe('book_lover24');

    $this->actingAs($reader, 'sanctum')->patchJson('/api/profile', ['username' => 'Mobile_Reader'])
        ->assertOk()->assertJsonPath('data.username', 'mobile_reader');

    $this->actingAs($reader, 'sanctum')->patchJson('/api/profile', ['username' => null])
        ->assertOk()->assertJsonPath('data.username', null);
    expect($reader->fresh()->username)->toBeNull();
});

test('reader usernames must be unique and match the supported format', function () {
    $firstReader = User::factory()->create(['username' => 'booklover']);
    $secondReader = User::factory()->create();

    $this->actingAs($secondReader)->patch('/profile', [
        'name' => $secondReader->name,
        'email' => $secondReader->email,
        'username' => 'BOOKLOVER',
    ])->assertSessionHasErrors('username');

    $this->actingAs($secondReader, 'sanctum')->patchJson('/api/profile', ['username' => 'two words'])
        ->assertUnprocessable()->assertJsonValidationErrors('username');

    expect($firstReader->fresh()->username)->toBe('booklover')
        ->and($secondReader->fresh()->username)->toBeNull();
});

test('only reader accounts can set a username', function () {
    $author = User::factory()->create(['role' => 'author']);

    $this->actingAs($author, 'sanctum')->patchJson('/api/profile', ['username' => 'authorname'])
        ->assertUnprocessable()->assertJsonValidationErrors('username');
    expect($author->fresh()->username)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('profile photo can be uploaded', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $photo = UploadedFile::fake()->image('avatar.jpg');

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'profile_photo' => $photo,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertNotNull($user->profile_photo_path);
    Storage::disk('public')->assertExists($user->profile_photo_path);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
