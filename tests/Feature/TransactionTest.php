<?php

use App\Enums\TimeSlot;
use App\Models\Address;
use App\Models\Office;
use App\Models\User;
use App\Models\WasteCategory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('user can view own transaction history', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = WasteCategory::create(['name_category' => 'Plastik']);

    $address = Address::factory()->for($user)->create([
        'latitude' => -6.2088,
        'longitude' => 106.8456,
    ]);

    Office::create([
        'office_name' => 'Office Jakarta',
        'office_address' => 'Jl. Sudirman',
        'office_phone' => '021-123456',
        'address_latitude' => -6.2000,
        'address_longitude' => 106.8400,
    ]);

    $date = Carbon::tomorrow()->addDay()->toDateString();

    $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [['category_id' => $category->category_id]],
        'scheduled_date' => $date,
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    // Other user's booking — should not appear
    $otherAddress = Address::factory()->for($otherUser)->create([
        'latitude' => -6.2088,
        'longitude' => 106.8456,
    ]);

    $this->actingAs($otherUser)->postJson('/api/booking', [
        'address_id' => $otherAddress->address_id,
        'details' => [['category_id' => $category->category_id]],
        'scheduled_date' => $date,
        'time_slot' => TimeSlot::Slot_10_12->value,
    ]);

    $response = $this->actingAs($user)->getJson('/api/transactions');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('transaction history has selected columns and waste category', function () {
    $user = User::factory()->create();
    $category = WasteCategory::create(['name_category' => 'Plastik']);

    $address = Address::factory()->for($user)->create([
        'latitude' => -6.2088,
        'longitude' => 106.8456,
    ]);

    Office::create([
        'office_name' => 'Office Jakarta',
        'office_address' => 'Jl. Sudirman',
        'office_phone' => '021-123456',
        'address_latitude' => -6.2000,
        'address_longitude' => 106.8400,
    ]);

    $date = Carbon::tomorrow()->addDay()->toDateString();

    $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [['category_id' => $category->category_id]],
        'scheduled_date' => $date,
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    $response = $this->actingAs($user)->getJson('/api/transactions');

    $response->assertSuccessful();

    $item = $response->json('data.0');
    expect($item)->toHaveKeys([
        'trans_id', 'status', 'payment_status', 'total_paid', 'time_slot', 'scheduled_date',
        'transaction_details',
    ]);
    expect($item['transaction_details'][0])->toHaveKeys(['waste_category']);
    // ponytail: index excludes payment_fees, address — show endpoint has them
    expect($item)->not->toHaveKeys(['payment_fees', 'address']);
});

test('empty transaction history returns empty array', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/transactions');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('user can view transaction detail', function () {
    $user = User::factory()->create();
    $category = WasteCategory::create(['name_category' => 'Plastik']);

    $address = Address::factory()->for($user)->create([
        'latitude' => -6.2088,
        'longitude' => 106.8456,
    ]);

    Office::create([
        'office_name' => 'Office Jakarta',
        'office_address' => 'Jl. Sudirman',
        'office_phone' => '021-123456',
        'address_latitude' => -6.2000,
        'address_longitude' => 106.8400,
    ]);

    $date = Carbon::tomorrow()->addDay()->toDateString();

    $booking = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [['category_id' => $category->category_id]],
        'scheduled_date' => $date,
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    $transId = $booking->json('transaction.trans_id');

    $response = $this->actingAs($user)->getJson("/api/transactions/{$transId}");

    $response->assertSuccessful()
        ->assertJsonPath('trans_id', $transId)
        ->assertJsonPath('transaction_details.0.waste_category.name_category', 'Plastik');

    expect($response->json())->toHaveKeys([
        'transaction_details', 'payment_fees', 'address',
    ]);
});

test('cannot view other user transaction detail', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = WasteCategory::create(['name_category' => 'Plastik']);

    $address = Address::factory()->for($user)->create([
        'latitude' => -6.2088,
        'longitude' => 106.8456,
    ]);

    Office::create([
        'office_name' => 'Office Jakarta',
        'office_address' => 'Jl. Sudirman',
        'office_phone' => '021-123456',
        'address_latitude' => -6.2000,
        'address_longitude' => 106.8400,
    ]);

    $date = Carbon::tomorrow()->addDay()->toDateString();

    $booking = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [['category_id' => $category->category_id]],
        'scheduled_date' => $date,
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    $transId = $booking->json('transaction.trans_id');

    $response = $this->actingAs($otherUser)->getJson("/api/transactions/{$transId}");

    $response->assertNotFound();
});
