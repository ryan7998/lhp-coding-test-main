<?php

use App\Mail\EventAttendanceConfirmed;
use App\Mail\EventReminder;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function eventPayload(string $name = 'Midnight Jazz Festival'): array
{
    $startsAt = CarbonImmutable::parse('2026-07-10 20:00:00 UTC')->timestamp;

    return [
        'name' => $name,
        'category' => 'concert',
        'description' => 'A global evening of live music with food, lights, and a full house.',
        'venue' => ['name' => 'Harbor Hall', 'capacity' => 1200],
        'location' => ['lat' => 40.7128, 'lng' => -74.0060],
        'schedule' => ['starts_at' => $startsAt, 'ends_at' => $startsAt + 7200],
        'pricing' => ['currency' => 'USD', 'min_price' => 30],
    ];
}

it('returns normalized discovery events with images, address, timezone, and attendee count', function () {
    $user = User::factory()->create();
    $startsAt = CarbonImmutable::parse('2026-07-10 20:00:00 UTC')->timestamp;
    $event = Event::factory()->for($user)->create([
        'type' => 'concert',
        'status' => 'published',
        'created_time' => $startsAt,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'city' => 'New York',
        'country' => 'United States',
        'country_code' => 'US',
        'timezone' => 'America/New_York',
        'payload' => eventPayload(),
    ]);

    EventAttendee::factory()->count(2)->for($event)->create();

    $response = $this->getJson(route('events.discovery-data', [
        'from' => '2026-07-10',
        'to' => '2026-07-10',
        'tz' => 'America/Toronto',
    ]));

    $response
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.id', $event->id)
        ->assertJsonPath('data.0.title', 'Midnight Jazz Festival')
        ->assertJsonPath('data.0.address', 'Harbor Hall, New York, United States')
        ->assertJsonPath('data.0.timezone', 'America/New_York')
        ->assertJsonPath('data.0.attendee_count', 2)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'type',
                    'status',
                    'venue',
                    'address',
                    'city',
                    'country',
                    'country_code',
                    'latitude',
                    'longitude',
                    'starts_at_iso',
                    'ends_at_iso',
                    'timezone',
                    'images',
                    'attendee_count',
                ],
            ],
            'facets' => ['types', 'locations'],
        ]);

    expect($response->json('data.0.images.0.src'))->toStartWith('/images/events/concert-');
});

it('filters discovery events by date and location', function () {
    $user = User::factory()->create();
    $torontoStart = CarbonImmutable::parse('2026-08-02 18:00:00 UTC')->timestamp;
    $parisStart = CarbonImmutable::parse('2026-08-04 18:00:00 UTC')->timestamp;

    Event::factory()->for($user)->create([
        'type' => 'conference',
        'status' => 'published',
        'created_time' => $torontoStart,
        'latitude' => 43.6532,
        'longitude' => -79.3832,
        'city' => 'Toronto',
        'country' => 'Canada',
        'country_code' => 'CA',
        'timezone' => 'America/Toronto',
        'payload' => eventPayload('Toronto Tech Summit'),
    ]);
    Event::factory()->for($user)->create([
        'type' => 'conference',
        'status' => 'published',
        'created_time' => $parisStart,
        'latitude' => 48.8566,
        'longitude' => 2.3522,
        'city' => 'Paris',
        'country' => 'France',
        'country_code' => 'FR',
        'timezone' => 'Europe/Paris',
        'payload' => eventPayload('Paris Design Forum'),
    ]);
    Event::factory()->for($user)->create([
        'status' => 'draft',
        'created_time' => $torontoStart,
        'city' => 'Toronto',
        'country_code' => 'CA',
    ]);

    $this->getJson(route('events.discovery-data', [
        'from' => '2026-08-02',
        'to' => '2026-08-02',
        'city' => 'Toronto',
        'country_code' => 'CA',
        'tz' => 'America/Toronto',
    ]))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.title', 'Toronto Tech Summit');
});

it('registers attendance once and queues a confirmation email', function () {
    Mail::fake();

    $event = Event::factory()->create([
        'status' => 'published',
        'city' => 'New York',
        'country' => 'United States',
        'country_code' => 'US',
        'timezone' => 'America/New_York',
        'payload' => eventPayload(),
    ]);

    $this->postJson(route('events.attendees.store', $event), [
        'name' => 'Ada Lovelace',
        'email' => 'ADA@example.com',
    ])
        ->assertCreated()
        ->assertJsonPath('registered', true)
        ->assertJsonPath('attendee_count', 1);

    $this->postJson(route('events.attendees.store', $event), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ])
        ->assertOk()
        ->assertJsonPath('registered', false)
        ->assertJsonPath('attendee_count', 1);

    $this->assertDatabaseCount('event_attendees', 1);
    $this->assertDatabaseHas('event_attendees', [
        'event_id' => $event->id,
        'email' => 'ada@example.com',
    ]);
    Mail::assertQueued(EventAttendanceConfirmed::class, 1);
});

it('sends each due reminder once and marks reminder timestamps', function () {
    Mail::fake();

    $threeDayEvent = Event::factory()->create([
        'status' => 'published',
        'created_time' => now()->addDays(2)->timestamp,
        'payload' => eventPayload('Three Day Event'),
    ]);
    $dayEvent = Event::factory()->create([
        'status' => 'published',
        'created_time' => now()->addHours(12)->timestamp,
        'payload' => eventPayload('Tomorrow Event'),
    ]);
    $laterEvent = Event::factory()->create([
        'status' => 'published',
        'created_time' => now()->addDays(5)->timestamp,
        'payload' => eventPayload('Later Event'),
    ]);

    $threeDayAttendee = EventAttendee::factory()->for($threeDayEvent)->create();
    $dayAttendee = EventAttendee::factory()->for($dayEvent)->create();
    $laterAttendee = EventAttendee::factory()->for($laterEvent)->create();

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertQueued(EventReminder::class, 2);
    Mail::assertQueued(
        EventReminder::class,
        fn (EventReminder $mail): bool => $mail->reminderWindow === '3 days',
    );
    Mail::assertQueued(
        EventReminder::class,
        fn (EventReminder $mail): bool => $mail->reminderWindow === '24 hours',
    );

    expect($threeDayAttendee->refresh()->reminder_3_days_sent_at)->not->toBeNull()
        ->and($threeDayAttendee->reminder_24_hours_sent_at)->toBeNull()
        ->and($dayAttendee->refresh()->reminder_24_hours_sent_at)->not->toBeNull()
        ->and($dayAttendee->reminder_3_days_sent_at)->toBeNull()
        ->and($laterAttendee->refresh()->reminder_3_days_sent_at)->toBeNull()
        ->and($laterAttendee->reminder_24_hours_sent_at)->toBeNull();

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertQueued(EventReminder::class, 2);
});
