<p>Hi {{ $attendeeName }},</p>

<p>This is your {{ $reminderWindow }} reminder for <strong>{{ $eventTitle }}</strong>.</p>

<p>
    <strong>When:</strong> {{ $eventTime }}<br>
    <strong>Where:</strong> {{ $eventAddress }}
</p>

<p>
    Event details:
    <a href="{{ $eventUrl }}">{{ $eventUrl }}</a>
</p>
