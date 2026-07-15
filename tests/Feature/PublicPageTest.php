<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public landing page loads', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('ReadArena');
});

test('reader dashboard redirects anonymous users', function () {
    $response = $this->get('/reader/dashboard');

    $response->assertRedirect('/login');
});
