<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventImageResolver;
use App\Services\EventLocationResolver;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class EventController extends Controller
{
    private const DISCOVERY_STATUSES = ['published', 'sold_out'];

    public function index(Request $request): Response
    {
        return Inertia::render('Events/Index', [
            'filters' => [
                'status' => $request->status,
                'from' => $request->input('from', '2023-01-01'),
            ],
            'statuses' => ['draft', 'published', 'cancelled', 'sold_out'],
        ]);
    }

    public function discoveryData(
        Request $request,
        EventImageResolver $images,
        EventLocationResolver $locations,
    ): JsonResponse {
        $timezone = $this->browserTimezone((string) $request->string('tz'));
        $perPage = max(1, min((int) $request->integer('per_page', 18), 60));

        $query = Event::query()
            ->withCount('attendees')
            ->whereIn('status', self::DISCOVERY_STATUSES)
            ->when(
                $request->filled('type'),
                fn ($query) => $query->where('type', (string) $request->string('type')),
            )
            ->when(
                $request->filled('country_code'),
                fn ($query) => $query->where(
                    'country_code',
                    strtoupper((string) $request->string('country_code')),
                ),
            )
            ->when(
                $request->filled('city'),
                fn ($query) => $query->where('city', 'like', '%'.(string) $request->string('city').'%'),
            );

        if ($request->filled('from')) {
            $query->where(
                'created_time',
                '>=',
                $this->dateBoundary((string) $request->string('from'), $timezone, false),
            );
        }

        if ($request->filled('to')) {
            $query->where(
                'created_time',
                '<=',
                $this->dateBoundary((string) $request->string('to'), $timezone, true),
            );
        }

        $events = $query
            ->orderBy('created_time')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => collect($events->items())
                ->map(fn (Event $event): array => $this->eventCard($event, $images, $locations))
                ->values(),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
            'total' => $events->total(),
            'facets' => $this->discoveryFacets(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        [$events, $stats] = $this->loadListing($request);

        return response()->json([
            'data' => $events->items(),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
            'total' => $events->total(),
            'stats' => $stats,
        ]);
    }

    public function show(Event $event): Response
    {
        $event->load('user');

        return Inertia::render('Events/Show', [
            'event' => $event,
        ]);
    }

    /**
     * @return array{0: LengthAwarePaginator, 1: array{ms: int, bytes: int}}
     */
    private function loadListing(Request $request): array
    {
        $start = microtime(true);

        $events = Event::with('user')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when(
                $request->from,
                fn ($q, $from) => $q->where('created_time', '>=', strtotime((string) $from)),
            )
            ->orderByDesc('created_time')
            ->paginate(50)
            ->withQueryString();

        $stats = [
            'ms' => (int) round((microtime(true) - $start) * 1000),
            'bytes' => strlen((string) json_encode($events->items())),
        ];

        return [$events, $stats];
    }

    private function browserTimezone(?string $timezone): DateTimeZone
    {
        try {
            return new DateTimeZone($timezone ?: 'UTC');
        } catch (Throwable) {
            return new DateTimeZone('UTC');
        }
    }

    private function dateBoundary(string $date, DateTimeZone $timezone, bool $endOfDay): int
    {
        $parsed = CarbonImmutable::parse($date, $timezone);

        return ($endOfDay ? $parsed->endOfDay() : $parsed->startOfDay())
            ->setTimezone('UTC')
            ->timestamp;
    }

    /**
     * @return array<string, mixed>
     */
    private function eventCard(
        Event $event,
        EventImageResolver $images,
        EventLocationResolver $locations,
    ): array {
        $payload = is_array($event->payload) ? $event->payload : [];
        $resolved = $locations->resolve($event->latitude, $event->longitude);
        $city = $event->city ?: $resolved['city'];
        $country = $event->country ?: $resolved['country'];
        $countryCode = $event->country_code ?: $resolved['country_code'];
        $timezone = $event->timezone ?: $resolved['timezone'];
        $title = (string) Arr::get($payload, 'name', Str::headline($event->type).' Event');
        $venue = (string) Arr::get($payload, 'venue.name', 'Venue TBD');
        $startsAt = $event->created_time ?: Arr::get($payload, 'schedule.starts_at');
        $endsAt = Arr::get($payload, 'schedule.ends_at');

        return [
            'id' => $event->id,
            'title' => $title,
            'description' => (string) Arr::get(
                $payload,
                'description',
                "Join us for {$title}, a {$event->type} event worth adding to your calendar.",
            ),
            'type' => $event->type,
            'status' => $event->status,
            'venue' => $venue,
            'address' => $locations->address($venue, $city, $country),
            'city' => $city,
            'country' => $country,
            'country_code' => $countryCode,
            'latitude' => $event->latitude,
            'longitude' => $event->longitude,
            'starts_at_iso' => $startsAt
                ? CarbonImmutable::createFromTimestampUTC((int) $startsAt)->toIso8601String()
                : null,
            'ends_at_iso' => $endsAt
                ? CarbonImmutable::createFromTimestampUTC((int) $endsAt)->toIso8601String()
                : null,
            'timezone' => $timezone,
            'images' => $images->imagesForEvent($event, $title),
            'attendee_count' => (int) ($event->attendees_count ?? 0),
        ];
    }

    /**
     * @return array{types: mixed, locations: mixed}
     */
    private function discoveryFacets(): array
    {
        return [
            'types' => Event::query()
                ->whereIn('status', self::DISCOVERY_STATUSES)
                ->select('type')
                ->distinct()
                ->orderBy('type')
                ->pluck('type')
                ->values(),
            'locations' => Event::query()
                ->whereIn('status', self::DISCOVERY_STATUSES)
                ->whereNotNull('city')
                ->whereNotNull('country')
                ->whereNotNull('country_code')
                ->select('city', 'country', 'country_code')
                ->distinct()
                ->orderBy('country')
                ->orderBy('city')
                ->limit(200)
                ->get()
                ->map(fn (Event $event): array => [
                    'city' => (string) $event->city,
                    'country' => (string) $event->country,
                    'country_code' => (string) $event->country_code,
                    'label' => "{$event->city}, {$event->country}",
                ])
                ->values(),
        ];
    }
}
