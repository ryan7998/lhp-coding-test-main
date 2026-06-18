<script setup lang="ts">
import type { EventImage } from '@/types/events';

defineProps<{
    title: string;
    images: EventImage[];
}>();
</script>

<template>
    <div class="grid aspect-[16/10] grid-cols-[1fr_5rem] gap-1 overflow-hidden rounded-t-lg bg-muted">
        <img
            v-if="images[0]"
            :src="images[0].src"
            :alt="images[0].alt"
            class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
            loading="lazy"
        />
        <div
            v-else
            class="flex h-full w-full items-center justify-center bg-muted text-sm text-muted-foreground"
        >
            {{ title }}
        </div>

        <div class="grid gap-1">
            <img
                v-for="image in images.slice(1, 3)"
                :key="image.src"
                :src="image.src"
                :alt="image.alt"
                class="h-full min-h-0 w-full object-cover"
                loading="lazy"
            />
        </div>
    </div>
</template>
