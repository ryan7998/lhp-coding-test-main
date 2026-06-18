<p>Hi {{ $attendeeName }},</p>

<p>You are on the list for <strong>{{ $eventTitle }}</strong>.</p>

<p>
    <strong>When:</strong> {{ $eventTime }}<br>
    <strong>Where:</strong> {{ $eventAddress }}
</p>

<p>
    Event details:
    <a href="{{ $eventUrl }}">{{ $eventUrl }}</a>
</p>

<p>We will send reminders as the event gets closer.</p>
