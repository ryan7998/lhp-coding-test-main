<script setup lang="ts">
import { RotateCcw, Search } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { EventFilters, LocationFacet } from '@/types/events';

const props = defineProps<{
    modelValue: EventFilters;
    types: string[];
    locations: LocationFacet[];
    loading?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: EventFilters];
    submit: [];
    clear: [];
}>();

const countryOptions = computed(() => {
    const countries = new Map<string, string>();

    props.locations.forEach((location) => {
        countries.set(location.country_code, location.country);
    });

    return Array.from(countries, ([code, country]) => ({ code, country })).sort(
        (a, b) => a.country.localeCompare(b.country),
    );
});

function updateFilter(key: keyof EventFilters, value: string): void {
    emit('update:modelValue', {
        ...props.modelValue,
        [key]: value,
    });
}

function selectValue(event: Event): string {
    return (event.target as HTMLSelectElement).value;
}

function openDatePicker(event: Event): void {
    const input = event.currentTarget as HTMLInputElement & {
        showPicker?: () => void;
    };

    input.showPicker?.();
}
</script>

<template>
    <form
        class="grid gap-3 rounded-lg border bg-background/95 p-3 shadow-sm md:grid-cols-[1fr_1fr_1fr_1fr_auto_auto] md:items-end"
        @submit.prevent="emit('submit')"
    >
        <div class="space-y-1.5">
            <Label for="event-from">From</Label>
            <Input
                id="event-from"
                :model-value="modelValue.from"
                type="date"
                class="[color-scheme:light] dark:[color-scheme:dark]"
                @click="openDatePicker"
                @update:model-value="updateFilter('from', String($event))"
            />
        </div>

        <div class="space-y-1.5">
            <Label for="event-to">To</Label>
            <Input
                id="event-to"
                :model-value="modelValue.to"
                type="date"
                class="[color-scheme:light] dark:[color-scheme:dark]"
                @click="openDatePicker"
                @update:model-value="updateFilter('to', String($event))"
            />
        </div>

        <div class="space-y-1.5">
            <Label for="event-city">City</Label>
            <Input
                id="event-city"
                :model-value="modelValue.city"
                list="event-location-options"
                placeholder="Toronto"
                @update:model-value="updateFilter('city', String($event))"
            />
            <datalist id="event-location-options">
                <option
                    v-for="location in locations"
                    :key="`${location.city}-${location.country_code}`"
                    :value="location.city"
                >
                    {{ location.label }}
                </option>
            </datalist>
        </div>

        <div class="grid grid-cols-2 gap-3 md:grid-cols-1 xl:grid-cols-2">
            <div class="space-y-1.5">
                <Label for="event-country">Country</Label>
                <select
                    id="event-country"
                    :value="modelValue.country_code"
                    class="border-input bg-background ring-offset-background focus-visible:ring-ring h-9 w-full rounded-md border px-3 text-sm shadow-xs transition outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                    @change="updateFilter('country_code', selectValue($event))"
                >
                    <option value="">All</option>
                    <option
                        v-for="country in countryOptions"
                        :key="country.code"
                        :value="country.code"
                    >
                        {{ country.country }}
                    </option>
                </select>
            </div>

            <div class="space-y-1.5">
                <Label for="event-type">Type</Label>
                <select
                    id="event-type"
                    :value="modelValue.type"
                    class="border-input bg-background ring-offset-background focus-visible:ring-ring h-9 w-full rounded-md border px-3 text-sm capitalize shadow-xs transition outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                    @change="updateFilter('type', selectValue($event))"
                >
                    <option value="">All</option>
                    <option v-for="type in types" :key="type" :value="type">
                        {{ type }}
                    </option>
                </select>
            </div>
        </div>

        <Button type="submit" :disabled="loading" class="gap-2">
            <Search class="size-4" />
            Filter
        </Button>

        <Button
            type="button"
            variant="outline"
            :disabled="loading"
            class="gap-2"
            @click="emit('clear')"
        >
            <RotateCcw class="size-4" />
            Reset
        </Button>
    </form>
</template>
