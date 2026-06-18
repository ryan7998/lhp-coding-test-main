<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Grid3X3, Loader2 } from '@lucide/vue';
import { computed, onMounted } from 'vue';
import EventDiscoveryCard from '@/components/events/EventDiscoveryCard.vue';
import EventDiscoveryFilters from '@/components/events/EventDiscoveryFilters.vue';
import { Button } from '@/components/ui/button';
import { useEventDiscovery } from '@/composables/useEventDiscovery';

const discovery = useEventDiscovery({ perPage: 18 });

const resultSummary = computed(() => {
    if (discovery.total.value === null) {
        return 'Loading events';
    }

    return `${discovery.total.value.toLocaleString()} matching events`;
});

onMounted(() => {
    discovery.loadMore(true);
});
</script>

<template>
    <Head title="Events Visual 1" />

    <main class="min-h-screen bg-muted/30">
        <section class="border-b bg-background">
            <div class="mx-auto flex max-w-7xl flex-col gap-5 p-4 sm:p-6">
                <div
                    class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between"
                >
                    <div>
                        <div
                            class="mb-2 inline-flex items-center gap-2 rounded-md border px-2.5 py-1 text-xs font-medium text-muted-foreground"
                        >
                            <Grid3X3 class="size-3.5 text-emerald-600" />
                            Card grid
                        </div>
                        <h1 class="text-2xl font-semibold tracking-normal">
                            Event Visuals 1
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ resultSummary }}
                        </p>
                    </div>

                    <p class="max-w-md text-sm text-muted-foreground">
                        Browse local event imagery, event-local times, your local
                        helper time, and quick attendance registration.
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

        <section class="mx-auto max-w-7xl p-4 sm:p-6">
            <div
                v-if="discovery.error.value"
                class="rounded-lg border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive"
            >
                {{ discovery.error.value }}
            </div>

            <div
                v-else-if="
                    discovery.hasLoadedOnce.value && discovery.events.value.length === 0
                "
                class="rounded-lg border bg-background p-10 text-center text-muted-foreground"
            >
                No events matched those filters.
            </div>

            <div
                v-else
                class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
            >
                <EventDiscoveryCard
                    v-for="event in discovery.events.value"
                    :key="event.id"
                    :event="event"
                    class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2"
                />
            </div>

            <div class="flex justify-center py-8">
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
        </section>
    </main>
</template>
