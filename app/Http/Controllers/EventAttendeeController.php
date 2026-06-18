<?php

namespace App\Http\Controllers;

use App\Mail\EventAttendanceConfirmed;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EventAttendeeController extends Controller
{
    public function store(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $attendee = EventAttendee::firstOrCreate(
            [
                'event_id' => $event->id,
                'email' => Str::lower($validated['email']),
            ],
            [
                'name' => $validated['name'],
            ],
        );

        if ($attendee->wasRecentlyCreated) {
            Mail::to($attendee->email)->queue(new EventAttendanceConfirmed($event, $attendee));
        }

        return response()->json([
            'registered' => $attendee->wasRecentlyCreated,
            'message' => $attendee->wasRecentlyCreated
                ? 'You are on the list. Check your email for confirmation.'
                : 'You are already on the list for this event.',
            'attendee_count' => $event->attendees()->count(),
        ], $attendee->wasRecentlyCreated ? 201 : 200);
    }
}
