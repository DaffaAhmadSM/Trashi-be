<?php

use App\Enums\TimeSlot;
use App\Models\Address;
use App\Models\Office;
use App\Models\User;
use App\Models\WasteCategory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('checkout processes payment for pending booking', function () {
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

    $date = Carbon::tomorrow()->addDay()->toDateString(); // skip tomorrow, pick a weekday

    $booking = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [['category_id' => $category->category_id]],
        'scheduled_date' => $date,
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    $transId = $booking->json('transaction.trans_id');

    $response = $this->actingAs($user)->postJson("/api/checkout/{$transId}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Payment successful.')
        ->assertJsonPath('transaction.payment_status', 'paid');
});

test('checkout fails when payment already processed', function () {
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

    $this->actingAs($user)->postJson("/api/checkout/{$transId}");
    $response = $this->actingAs($user)->postJson("/api/checkout/{$transId}");

    $response->assertUnprocessable()
        ->assertJsonFragment(['message' => 'Payment already processed.']);
});

test('checkout fails for other user transaction', function () {
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

    $response = $this->actingAs($otherUser)->postJson("/api/checkout/{$transId}");

    $response->assertNotFound();
});
