import { computed, reactive, ref } from 'vue';
import { localDateInput, viewerTimezone } from '@/lib/eventFormatting';
import type {
    DiscoveryEvent,
    DiscoveryFacets,
    DiscoveryResponse,
    EventFilters,
} from '@/types/events';

type DiscoveryOptions = {
    perPage?: number;
    defaults?: Partial<EventFilters>;
};

const emptyFacets = (): DiscoveryFacets => ({
    types: [],
    locations: [],
});

export function useEventDiscovery(options: DiscoveryOptions = {}) {
    const filters = reactive<EventFilters>({
        from: options.defaults?.from ?? localDateInput(),
        to: options.defaults?.to ?? '',
        city: options.defaults?.city ?? '',
        country_code: options.defaults?.country_code ?? '',
        type: options.defaults?.type ?? '',
    });

    const events = ref<DiscoveryEvent[]>([]);
    const facets = ref<DiscoveryFacets>(emptyFacets());
    const page = ref(0);
    const lastPage = ref<number | null>(null);
    const total = ref<number | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);
    const hasLoadedOnce = ref(false);
    const perPage = options.perPage ?? 18;

    const hasMore = computed(
        () => lastPage.value === null || page.value < lastPage.value,
    );

    async function loadMore(reset = false): Promise<void> {
        if (loading.value) {
            return;
        }

        if (!reset && !hasMore.value) {
            return;
        }

        loading.value = true;
        error.value = null;

        const nextPage = reset ? 1 : page.value + 1;
        const params = new URLSearchParams({
            page: String(nextPage),
            per_page: String(perPage),
            tz: viewerTimezone(),
        });

        Object.entries(filters).forEach(([key, value]) => {
            if (value) {
                params.set(key, value);
            }
        });

        try {
            const response = await fetch(
                `/events/discovery-data?${params.toString()}`,
                {
                    headers: { Accept: 'application/json' },
                },
            );

            if (!response.ok) {
                throw new Error('Unable to load events.');
            }

            const payload = (await response.json()) as DiscoveryResponse;

            events.value = reset
                ? payload.data
                : [...events.value, ...payload.data];
            facets.value = payload.facets ?? emptyFacets();
            page.value = payload.current_page;
            lastPage.value = payload.last_page;
            total.value = payload.total;
            hasLoadedOnce.value = true;
        } catch (caught) {
            error.value =
                caught instanceof Error
                    ? caught.message
                    : 'Unable to load events.';
        } finally {
            loading.value = false;
        }
    }

    function applyFilters(): Promise<void> {
        page.value = 0;
        lastPage.value = null;

        return loadMore(true);
    }

    function clearFilters(): Promise<void> {
        filters.from = localDateInput();
        filters.to = '';
        filters.city = '';
        filters.country_code = '';
        filters.type = '';

        return applyFilters();
    }

    return {
        filters,
        events,
        facets,
        total,
        loading,
        error,
        hasMore,
        hasLoadedOnce,
        loadMore,
        applyFilters,
        clearFilters,
    };
}
