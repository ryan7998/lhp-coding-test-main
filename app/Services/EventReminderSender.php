<?php

namespace App\Services;

use App\Mail\EventReminder;
use App\Models\EventAttendee;
use Illuminate\Support\Facades\Mail;

class EventReminderSender
{
    public function sendDueReminders(): int
    {
        $now = now()->timestamp;

        return $this->sendWindow(
            'reminder_3_days_sent_at',
            '3 days',
            $now + 24 * 60 * 60,
            $now + 72 * 60 * 60,
        ) + $this->sendWindow(
            'reminder_24_hours_sent_at',
            '24 hours',
            $now,
            $now + 24 * 60 * 60,
        );
    }

    private function sendWindow(string $sentColumn, string $window, int $from, int $to): int
    {
        $sent = 0;

        EventAttendee::query()
            ->with('event')
            ->whereNull($sentColumn)
            ->whereHas('event', fn ($query) => $query->whereBetween('created_time', [$from, $to]))
            ->chunkById(100, function ($attendees) use ($sentColumn, $window, &$sent): void {
                foreach ($attendees as $attendee) {
                    if ($attendee->event === null) {
                        continue;
                    }

                    Mail::to($attendee->email)->queue(new EventReminder($attendee->event, $attendee, $window));

                    $attendee->forceFill([
                        $sentColumn => now(),
                    ])->save();

                    $sent++;
                }
            });

        return $sent;
    }
}
