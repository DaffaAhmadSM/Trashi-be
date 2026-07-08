<?php

use App\Enums\TimeSlot;
use App\Enums\TransactionStatus;
use App\Models\Address;
use App\Models\Office;
use App\Models\User;
use App\Models\WasteCategory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

function nextValidDate(): string
{
    $date = Carbon::tomorrow();
    while ($date->isSunday()) {
        $date->addDay();
    }

    return $date->toDateString();
}

test('booking creates transaction with schedule and nearest office', function () {
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

    $response = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [
            ['category_id' => $category->category_id],
        ],
        'scheduled_date' => nextValidDate(),
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    $response->assertCreated()
        ->assertJsonPath('nearest_office.office_name', 'Office Jakarta')
        ->assertJsonFragment(['name' => 'Distance Fee'])
        ->assertJsonPath('transaction.payment_status', 'pending')
        ->assertJsonPath('transaction.status', 'pending');

    $data = $response->json();
    expect($data['nearest_office']['distance_km'])->toBeLessThan(5);
    expect($data['transaction']['total_paid'])->toBeGreaterThan(0);
    expect($data['transaction']['transaction_details'][0]['actual_weight'])->toBeNull();
    expect($data['transaction']['scheduled_date'])->not->toBeNull();
    expect($data['transaction']['time_slot'])->toBe(TimeSlot::Slot_8_10->value);
});

test('booking returns no service when all offices over 30km', function () {
    $user = User::factory()->create();
    $category = WasteCategory::create(['name_category' => 'Plastik']);

    $address = Address::factory()->for($user)->create([
        'latitude' => -6.2088,
        'longitude' => 106.8456,
    ]);

    Office::create([
        'office_name' => 'Office Bali',
        'office_address' => 'Jl. Denpasar',
        'office_phone' => '0361-123456',
        'address_latitude' => -8.4095,
        'address_longitude' => 115.1889,
    ]);

    $response = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [
            ['category_id' => $category->category_id],
        ],
        'scheduled_date' => nextValidDate(),
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    $response->assertUnprocessable()
        ->assertJsonFragment(['message' => 'No service available in your location.']);
});

test('booking rejects scheduling on Sunday', function () {
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

    $sunday = Carbon::now()->next(Carbon::SUNDAY)->toDateString();

    $response = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [
            ['category_id' => $category->category_id],
        ],
        'scheduled_date' => $sunday,
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('scheduled_date');
});

test('booking rejects 3PM-5PM slot on Saturday', function () {
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

    $saturday = Carbon::now()->next(Carbon::SATURDAY)->toDateString();

    $response = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [
            ['category_id' => $category->category_id],
        ],
        'scheduled_date' => $saturday,
        'time_slot' => TimeSlot::Slot_15_17->value,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('time_slot');
});

test('booking accepts other slots on Saturday', function () {
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

    $saturday = Carbon::now()->next(Carbon::SATURDAY)->toDateString();

    $response = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [
            ['category_id' => $category->category_id],
        ],
        'scheduled_date' => $saturday,
        'time_slot' => TimeSlot::Slot_10_12->value,
    ]);

    $response->assertCreated();
});

test('confirm payment sets status to accepted', function () {
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

    // Create booking
    $booking = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [['category_id' => $category->category_id]],
        'scheduled_date' => nextValidDate(),
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    $transId = $booking->json('transaction.trans_id');

    // Confirm payment
    $response = $this->actingAs($user)->postJson("/api/booking/{$transId}/confirm");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Payment confirmed.')
        ->assertJsonPath('transaction.payment_status', 'confirmed')
        ->assertJsonPath('transaction.status', TransactionStatus::Accepted->value);
});

test('cannot confirm already confirmed payment', function () {
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

    $booking = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [['category_id' => $category->category_id]],
        'scheduled_date' => nextValidDate(),
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    $transId = $booking->json('transaction.trans_id');

    $this->actingAs($user)->postJson("/api/booking/{$transId}/confirm");
    $response = $this->actingAs($user)->postJson("/api/booking/{$transId}/confirm");

    $response->assertUnprocessable()
        ->assertJsonFragment(['message' => 'Payment already confirmed.']);
});

test('booking validates address belongs to user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = WasteCategory::create(['name_category' => 'Plastik']);

    $otherAddress = Address::factory()->for($otherUser)->create();

    $response = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $otherAddress->address_id,
        'details' => [['category_id' => $category->category_id]],
        'scheduled_date' => nextValidDate(),
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    $response->assertNotFound();
});

test('booking requires scheduled_date and time_slot', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create();
    $category = WasteCategory::create(['name_category' => 'Plastik']);

    $response = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [['category_id' => $category->category_id]],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['scheduled_date', 'time_slot']);
});

test('booking requires details array', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create();

    $response = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'scheduled_date' => nextValidDate(),
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('details');
});

test('booking validates category exists', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create();

    $response = $this->actingAs($user)->postJson('/api/booking', [
        'address_id' => $address->address_id,
        'details' => [['category_id' => 999]],
        'scheduled_date' => nextValidDate(),
        'time_slot' => TimeSlot::Slot_8_10->value,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('details.0.category_id');
});
