# Event Visuals Decisions

## Local data and images

Events keep `created_time` as the canonical start timestamp so the existing seeded dataset stays usable. New nullable location metadata is added beside the original coordinates, and existing rows can be backfilled with `php artisan events:enrich-metadata`.

Images are served from `public/images/events`. The app uses a deterministic resolver by event type/id instead of storing image rows for every seeded event, which keeps the large dataset light while still providing two local images per event.

## Locations and timezones

The seeded dataset already clusters coordinates around known city anchors, so the app uses those anchors as a local geocoder. Fresh seeds store the city, country, country code, and timezone at insert time. Existing rows fall back to nearest-anchor resolution when rendered and can be enriched with the command above.

Date filters are interpreted in the browser timezone sent by the frontend, then converted to UTC epoch boundaries before querying `created_time`. Each event displays both event-local time and the viewer-local helper time.

## Attendance and reminders

Attendance uses a separate `event_attendees` table with a unique event/email pair. First registration queues a confirmation email; duplicate registration returns a friendly already-registered response without sending another email.

Reminder delivery is handled by `php artisan events:send-reminders`, scheduled hourly. It queues one 3-day reminder for events 24-72 hours away and one 24-hour reminder for events in the next 24 hours, marking each sent timestamp after queueing.
