<?php

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->package = Package::factory()->create();
    $this->user = User::factory()->create(['usertype' => 'admin']);
    Sanctum::actingAs($this->user);
});

it('can store trek highlights for a package', function () {
    $response = $this->postJson("/api/trek-highlights/{$this->package->id}", [
        'highlights' => ['Beautiful scenery', 'Challenging trails', 'Wildlife spotting'],
    ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Trek Highlight saved successfully.']);

    $this->assertDatabaseHas('trek_highlights', [
        'package_id' => $this->package->id,
        'highlight' => 'Beautiful scenery',
    ]);

    expect($this->package->trekHighlights()->count())->toBe(3);
});

it('validates highlight is an array when storing', function () {
    $response = $this->postJson("/api/trek-highlights/{$this->package->id}", [
        'highlights' => 'not-an-array',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['highlights']);
});

it('can update trek highlights for a package', function () {
    $this->package->trekHighlights()->create(['highlight' => 'Old highlight']);

    $response = $this->putJson("/api/trek-highlights/{$this->package->id}", [
        'highlights' => ['New highlight 1', 'New highlight 2'],
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Trek Highlight updated successfully.']);

    $this->assertDatabaseMissing('trek_highlights', [
        'package_id' => $this->package->id,
        'highlight' => 'Old highlight',
    ]);

    expect($this->package->trekHighlights()->count())->toBe(2);
});

it('can delete all trek highlights for a package', function () {
    $this->package->trekHighlights()->create(['highlight' => 'Highlight 1']);
    $this->package->trekHighlights()->create(['highlight' => 'Highlight 2']);

    $response = $this->deleteJson("/api/trek-highlights/{$this->package->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Trek Highlight deleted successfully.']);

    expect($this->package->trekHighlights()->count())->toBe(0);
});
