<script setup lang="ts">
import { CalendarDays, MapPin, Ticket, Users } from '@lucide/vue';
import EventAttendanceForm from '@/components/events/EventAttendanceForm.vue';
import EventImageStrip from '@/components/events/EventImageStrip.vue';
import { Badge } from '@/components/ui/badge';
import {
    formatEventDateTime,
    formatViewerDateTime,
} from '@/lib/eventFormatting';
import type { DiscoveryEvent } from '@/types/events';

defineProps<{
    event: DiscoveryEvent;
}>();

function badgeVariant(status: string): 'default' | 'secondary' | 'outline' {
    return status === 'sold_out' ? 'secondary' : 'default';
}
</script>

<template>
    <article
        class="group flex h-full flex-col overflow-hidden rounded-lg border bg-background shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md"
    >
        <EventImageStrip :title="event.title" :images="event.images" />

        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <Badge class="capitalize">{{ event.type }}</Badge>
                    <Badge :variant="badgeVariant(event.status)" class="capitalize">
                        {{ event.status.replace('_', ' ') }}
                    </Badge>
                </div>

                <div>
                    <h2 class="line-clamp-2 text-lg font-semibold">
                        {{ event.title }}
                    </h2>
                    <p class="mt-2 line-clamp-3 text-sm text-muted-foreground">
                        {{ event.description }}
                    </p>
                </div>
            </div>

            <dl class="grid gap-3 text-sm">
                <div class="flex gap-3">
                    <CalendarDays class="mt-0.5 size-4 shrink-0 text-emerald-600" />
                    <div>
                        <dt class="sr-only">Event local time</dt>
                        <dd class="font-medium">
                            {{
                                formatEventDateTime(
                                    event.starts_at_iso,
                                    event.timezone,
                                )
                            }}
                        </dd>
                        <dd class="text-muted-foreground">
                            Your time: {{ formatViewerDateTime(event.starts_at_iso) }}
                        </dd>
                    </div>
                </div>

                <div class="flex gap-3">
                    <MapPin class="mt-0.5 size-4 shrink-0 text-rose-600" />
                    <div>
                        <dt class="sr-only">Location</dt>
                        <dd class="font-medium">{{ event.venue }}</dd>
                        <dd class="text-muted-foreground">{{ event.address }}</dd>
                    </div>
                </div>

                <div class="flex gap-3">
                    <Users class="mt-0.5 size-4 shrink-0 text-sky-600" />
                    <div>
                        <dt class="sr-only">Attendance</dt>
                        <dd>
                            {{ event.attendee_count.toLocaleString() }} already
                            interested
                        </dd>
                    </div>
                </div>
            </dl>

            <div class="mt-auto">
                <div
                    class="mb-4 flex items-center gap-2 rounded-md bg-muted px-3 py-2 text-xs text-muted-foreground"
                >
                    <Ticket class="size-4" />
                    Confirmation and reminders are sent by email.
                </div>
                <EventAttendanceForm
                    :event-id="event.id"
                    :initial-attendee-count="event.attendee_count"
                />
            </div>
        </div>
    </article>
</template>
