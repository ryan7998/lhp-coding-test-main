<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EventAttendee;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EventReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public EventAttendee $attendee,
        public string $reminderWindow,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->reminderWindow} reminder: ".$this->eventTitle(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.event-reminder',
            with: $this->mailData(),
        );
    }

    /**
     * @return array<string, string>
     */
    private function mailData(): array
    {
        return [
            'attendeeName' => $this->attendee->name,
            'eventTitle' => $this->eventTitle(),
            'eventAddress' => $this->eventAddress(),
            'eventTime' => $this->eventTime(),
            'eventUrl' => route('events.show', $this->event),
            'reminderWindow' => $this->reminderWindow,
        ];
    }

    private function eventTitle(): string
    {
        return (string) Arr::get(
            $this->event->payload,
            'name',
            Str::headline($this->event->type).' Event',
        );
    }

    private function eventAddress(): string
    {
        $venue = (string) Arr::get($this->event->payload, 'venue.name', 'Venue TBD');

        return collect([$venue, $this->event->city, $this->event->country])
            ->filter(fn (?string $part): bool => filled($part))
            ->join(', ');
    }

    private function eventTime(): string
    {
        if ($this->event->created_time === null) {
            return 'Time to be announced';
        }

        $timezone = $this->event->timezone ?: 'UTC';

        return CarbonImmutable::createFromTimestampUTC((int) $this->event->created_time)
            ->setTimezone($timezone)
            ->format('M j, Y g:i A T');
    }
}
