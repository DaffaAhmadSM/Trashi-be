<?php

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

$addressData = fn (array $overrides = []) => array_merge([
    'label' => 'Rumah',
    'city' => 'Jakarta',
    'latitude' => -6.2088,
    'longitude' => 106.8456,
], $overrides);

test('user can list own addresses', function () {
    $user = User::factory()->create();
    Address::factory()->for($user)->count(3)->create();
    Address::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/addresses');

    $response->assertSuccessful()
        ->assertJsonCount(3);
});

test('user can create address with latitude and longitude', function () use ($addressData) {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/addresses', $addressData());

    $response->assertCreated()
        ->assertJsonFragment([
            'label' => 'Rumah',
            'city' => 'Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);
});

test('user can show an address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create();

    $response = $this->actingAs($user)->getJson("/api/addresses/{$address->address_id}");

    $response->assertSuccessful()
        ->assertJsonFragment(['address_id' => $address->address_id]);
});

test('user can update address latitude and longitude', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create();

    $response = $this->actingAs($user)->postJson(
        "/api/addresses/{$address->address_id}",
        ['label' => 'Updated', 'city' => 'Surabaya', 'latitude' => -7.2504, 'longitude' => 112.7688]
    );

    $response->assertSuccessful()
        ->assertJsonFragment([
            'latitude' => -7.2504,
            'longitude' => 112.7688,
        ]);
});

test('user can delete an address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create();

    $response = $this->actingAs($user)->deleteJson("/api/addresses/{$address->address_id}");

    $response->assertSuccessful()
        ->assertJsonFragment(['message' => 'Address deleted.']);

    expect(Address::query()->find($address->address_id))->toBeNull();
});

test('latitude must be between -90 and 90', function ($value) {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/addresses', [
        'label' => 'Test',
        'city' => 'Test',
        'latitude' => $value,
        'longitude' => 106.8456,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('latitude');
})->with([91, -91]);

test('longitude must be between -180 and 180', function ($value) {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/addresses', [
        'label' => 'Test',
        'city' => 'Test',
        'latitude' => -6.2088,
        'longitude' => $value,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('longitude');
})->with([181, -181]);

test('latitude is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/addresses', [
        'label' => 'Test',
        'city' => 'Test',
        'longitude' => 106.8456,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('latitude');
});

test('longitude is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/addresses', [
        'label' => 'Test',
        'city' => 'Test',
        'latitude' => -6.2088,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('longitude');
});

test('latitude must be numeric', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/addresses', [
        'label' => 'Test',
        'city' => 'Test',
        'latitude' => 'not-a-number',
        'longitude' => 106.8456,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('latitude');
});
