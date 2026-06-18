<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CalendarDays, Loader2, Map as MapIcon, MapPin } from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import EventAttendanceForm from '@/components/events/EventAttendanceForm.vue';
import EventDiscoveryFilters from '@/components/events/EventDiscoveryFilters.vue';
import EventImageStrip from '@/components/events/EventImageStrip.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useEventDiscovery } from '@/composables/useEventDiscovery';
import {
    formatCompactDate,
    formatDateGroup,
    formatEventDateTime,
    formatViewerDateTime,
} from '@/lib/eventFormatting';
import type { DiscoveryEvent } from '@/types/events';

const discovery = useEventDiscovery({ perPage: 30 });
const selectedId = ref<string | null>(null);

const groupedEvents = computed(() => {
    const groups = new Map<string, DiscoveryEvent[]>();

    discovery.events.value.forEach((event) => {
        const key = formatDateGroup(event);

        groups.set(key, [...(groups.get(key) ?? []), event]);
    });

    return Array.from(groups, ([label, events]) => ({ label, events }));
});

const selectedEvent = computed(
    () =>
        discovery.events.value.find((event) => event.id === selectedId.value) ??
        discovery.events.value[0] ??
        null,
);

const mapPoints = computed(() =>
    discovery.events.value
        .filter(
            (event) => event.latitude !== null && event.longitude !== null,
        )
        .map((event) => ({
            event,
            x: Math.min(
                96,
                Math.max(4, (((event.longitude ?? 0) + 180) / 360) * 100),
            ),
            y: Math.min(
                92,
                Math.max(8, ((90 - (event.latitude ?? 0)) / 180) * 100),
            ),
        })),
);

onMounted(() => {
    discovery.loadMore(true);
});

watch(
    () => discovery.events.value,
    (events) => {
        if (!selectedId.value && events[0]) {
            selectedId.value = events[0].id;
        }
    },
);
</script>

<template>
    <Head title="Events Visual 2" />

    <main class="min-h-screen bg-slate-50 dark:bg-background">
        <section class="border-b bg-background">
            <div class="mx-auto flex max-w-7xl flex-col gap-5 p-4 sm:p-6">
                <div
                    class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between"
                >
                    <div>
                        <div
                            class="mb-2 inline-flex items-center gap-2 rounded-md border px-2.5 py-1 text-xs font-medium text-muted-foreground"
                        >
                            <MapIcon class="size-3.5 text-rose-600" />
                            Timeline map
                        </div>
                        <h1 class="text-2xl font-semibold tracking-normal">
                            Event Visuals 2
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{
                                discovery.total.value !== null
                                    ? `${discovery.total.value.toLocaleString()} matching events`
                                    : 'Loading events'
                            }}
                        </p>
                    </div>

                    <p class="max-w-md text-sm text-muted-foreground">
                        Scan events chronologically, then use the world map to
                        compare where the loaded events are happening.
                    </p>
                </div>

                <EventDiscoveryFilters
                    :model-value="discovery.filters"
                    :types="discovery.facets.value.types"
                    :locations="discovery.facets.value.locations"
                    :loading="discovery.loading.value"
                    @update:model-value="Object.assign(discovery.filters, $event)"
                    @submit="discovery.applyFilters"
                    @clear="discovery.clearFilters"
                />
            </div>
        </section>

        <section
            class="mx-auto grid max-w-7xl gap-6 p-4 lg:grid-cols-[minmax(0,1fr)_25rem] sm:p-6"
        >
            <div class="space-y-6">
                <div
                    v-if="discovery.error.value"
                    class="rounded-lg border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive"
                >
                    {{ discovery.error.value }}
                </div>

                <div
                    v-else-if="
                        discovery.hasLoadedOnce.value &&
                        discovery.events.value.length === 0
                    "
                    class="rounded-lg border bg-background p-10 text-center text-muted-foreground"
                >
                    No events matched those filters.
                </div>

                <div
                    v-for="group in groupedEvents"
                    :key="group.label"
                    class="relative pl-6"
                >
                    <div
                        class="absolute top-2 bottom-0 left-2 w-px bg-border"
                    ></div>
                    <h2
                        class="sticky top-0 z-10 mb-3 inline-flex items-center gap-2 rounded-md border bg-background px-3 py-1.5 text-sm font-semibold shadow-sm"
                    >
                        <CalendarDays class="size-4 text-emerald-600" />
                        {{ group.label }}
                    </h2>

                    <div class="grid gap-3">
                        <button
                            v-for="event in group.events"
                            :key="event.id"
                            type="button"
                            class="relative rounded-lg border bg-background p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md"
                            :class="{
                                'border-primary ring-2 ring-primary/15':
                                    selectedEvent?.id === event.id,
                            }"
                            @click="selectedId = event.id"
                        >
                            <span
                                class="absolute top-6 -left-[1.55rem] size-3 rounded-full border-2 border-background bg-rose-600"
                            ></span>

                            <div
                                class="grid gap-4 md:grid-cols-[9rem_minmax(0,1fr)]"
                            >
                                <EventImageStrip
                                    :title="event.title"
                                    :images="event.images"
                                />
                                <div class="min-w-0 space-y-3">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <Badge class="capitalize">
                                            {{ event.type }}
                                        </Badge>
                                        <Badge
                                            variant="secondary"
                                            class="capitalize"
                                        >
                                            {{ formatCompactDate(event) }}
                                        </Badge>
                                    </div>

                                    <div>
                                        <h3
                                            class="line-clamp-2 text-base font-semibold"
                                        >
                                            {{ event.title }}
                                        </h3>
                                        <p
                                            class="mt-1 line-clamp-2 text-sm text-muted-foreground"
                                        >
                                            {{ event.description }}
                                        </p>
                                    </div>

                                    <div
                                        class="grid gap-2 text-sm text-muted-foreground md:grid-cols-2"
                                    >
                                        <span class="inline-flex gap-2">
                                            <CalendarDays
                                                class="mt-0.5 size-4 text-emerald-600"
                                            />
                                            {{
                                                formatEventDateTime(
                                                    event.starts_at_iso,
                                                    event.timezone,
                                                )
                                            }}
                                        </span>
                                        <span class="inline-flex gap-2">
                                            <MapPin
                                                class="mt-0.5 size-4 text-rose-600"
                                            />
                                            {{ event.city }},
                                            {{ event.country }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="flex justify-center py-2">
                    <Button
                        v-if="discovery.hasMore.value"
                        type="button"
                        variant="outline"
                        :disabled="discovery.loading.value"
                        class="gap-2"
                        @click="discovery.loadMore()"
                    >
                        <Loader2
                            v-if="discovery.loading.value"
                            class="size-4 animate-spin"
                        />
                        {{ discovery.loading.value ? 'Loading...' : 'Load more' }}
                    </Button>
                    <p
                        v-else-if="discovery.hasLoadedOnce.value"
                        class="text-sm text-muted-foreground"
                    >
                        End of results
                    </p>
                </div>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-4 lg:self-start">
                <div class="overflow-hidden rounded-lg border bg-background shadow-sm">
                    <div class="relative aspect-[4/3] overflow-hidden bg-sky-50">
                        <img
                            src="/images/events/world-map.svg"
                            alt="Stylized world map"
                            class="absolute inset-0 h-full w-full object-cover"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-b from-white/0 to-white/20 dark:from-slate-950/10 dark:to-slate-950/30"
                        ></div>

                        <button
                            v-for="point in mapPoints"
                            :key="point.event.id"
                            type="button"
                            class="absolute -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-background bg-rose-600 p-1.5 text-white shadow transition hover:scale-110"
                            :class="{
                                'bg-primary ring-4 ring-primary/20':
                                    selectedEvent?.id === point.event.id,
                            }"
                            :style="{
                                left: `${point.x}%`,
                                top: `${point.y}%`,
                            }"
                            :title="point.event.title"
                            @click="selectedId = point.event.id"
                        >
                            <MapPin class="size-3.5" />
                        </button>
                    </div>

                    <div v-if="selectedEvent" class="space-y-4 p-4">
                        <div>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <Badge class="capitalize">
                                    {{ selectedEvent.type }}
                                </Badge>
                                <Badge variant="secondary" class="capitalize">
                                    {{ selectedEvent.status.replace('_', ' ') }}
                                </Badge>
                            </div>
                            <h2 class="text-lg font-semibold">
                                {{ selectedEvent.title }}
                            </h2>
                            <p class="mt-2 text-sm text-muted-foreground">
                                {{ selectedEvent.description }}
                            </p>
                        </div>

                        <dl class="grid gap-3 text-sm">
                            <div>
                                <dt class="font-medium">Event time</dt>
                                <dd class="text-muted-foreground">
                                    {{
                                        formatEventDateTime(
                                            selectedEvent.starts_at_iso,
                                            selectedEvent.timezone,
                                        )
                                    }}
                                </dd>
                                <dd class="text-muted-foreground">
                                    Your time:
                                    {{
                                        formatViewerDateTime(
                                            selectedEvent.starts_at_iso,
                                        )
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium">Location</dt>
                                <dd class="text-muted-foreground">
                                    {{ selectedEvent.address }}
                                </dd>
                            </div>
                        </dl>

                        <EventAttendanceForm
                            :event-id="selectedEvent.id"
                            :initial-attendee-count="
                                selectedEvent.attendee_count
                            "
                        />
                    </div>
                </div>
            </aside>
        </section>
    </main>
</template>
