<?php

use App\Models\Event;
use App\Services\EventLocationResolver;
use App\Services\EventReminderSender;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('events:enrich-metadata {--chunk=1000}', function () {
    $resolver = app(EventLocationResolver::class);
    $chunkSize = max(1, (int) $this->option('chunk'));
    $updated = 0;

    Event::query()
        ->where(function ($query): void {
            $query
                ->whereNull('city')
                ->orWhereNull('country')
                ->orWhereNull('country_code')
                ->orWhereNull('timezone');
        })
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->chunkById($chunkSize, function ($events) use ($resolver, &$updated): void {
            foreach ($events as $event) {
                $location = $resolver->resolve($event->latitude, $event->longitude);

                $event->forceFill($location)->saveQuietly();
                $updated++;
            }

            $this->info("Updated {$updated} events...");
        });

    $this->info("Done. Updated {$updated} events.");
})->purpose('Backfill readable event location and timezone metadata');

Artisan::command('events:send-reminders', function () {
    $sent = app(EventReminderSender::class)->sendDueReminders();

    $this->info("Queued {$sent} event reminders.");
})->purpose('Queue 3-day and 24-hour event reminder emails');

Schedule::command('events:send-reminders')->hourly();
