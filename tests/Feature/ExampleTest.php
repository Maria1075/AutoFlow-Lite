<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('la aplicación responde correctamente', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
