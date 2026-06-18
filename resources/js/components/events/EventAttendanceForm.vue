<script setup lang="ts">
import { CheckCircle2, Mail, UserPlus } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    eventId: string;
    initialAttendeeCount: number;
}>();

const name = ref('');
const email = ref('');
const attendeeCount = ref(props.initialAttendeeCount);
const loading = ref(false);
const message = ref<string | null>(null);
const error = ref<string | null>(null);

const attendeeLabel = computed(() =>
    attendeeCount.value === 1
        ? '1 attendee'
        : `${attendeeCount.value.toLocaleString()} attendees`,
);

function csrfToken(): string {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? ''
    );
}

async function submit(): Promise<void> {
    if (loading.value) {
        return;
    }

    loading.value = true;
    message.value = null;
    error.value = null;

    try {
        const response = await fetch(`/events/${props.eventId}/attendees`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({
                name: name.value,
                email: email.value,
            }),
        });
        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload.message ?? 'Unable to register.');
        }

        attendeeCount.value = payload.attendee_count ?? attendeeCount.value;
        message.value = payload.message;

        if (payload.registered) {
            name.value = '';
            email.value = '';
        }
    } catch (caught) {
        error.value =
            caught instanceof Error ? caught.message : 'Unable to register.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <form class="space-y-3 border-t pt-4" @submit.prevent="submit">
        <div class="flex items-center justify-between gap-3 text-sm">
            <span class="inline-flex items-center gap-2 font-medium">
                <UserPlus class="size-4" />
                Join the list
            </span>
            <span class="text-muted-foreground">{{ attendeeLabel }}</span>
        </div>

        <div class="grid gap-2 sm:grid-cols-2">
            <div class="space-y-1.5">
                <Label :for="`${eventId}-name`">Name</Label>
                <Input
                    :id="`${eventId}-name`"
                    v-model="name"
                    autocomplete="name"
                    required
                    placeholder="Ada Lovelace"
                />
            </div>
            <div class="space-y-1.5">
                <Label :for="`${eventId}-email`">Email</Label>
                <Input
                    :id="`${eventId}-email`"
                    v-model="email"
                    autocomplete="email"
                    required
                    type="email"
                    placeholder="ada@example.com"
                />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <Button type="submit" size="sm" :disabled="loading" class="gap-2">
                <Mail class="size-4" />
                {{ loading ? 'Adding...' : 'Register interest' }}
            </Button>

            <p
                v-if="message"
                class="inline-flex items-center gap-1 text-sm text-emerald-700 dark:text-emerald-300"
            >
                <CheckCircle2 class="size-4" />
                {{ message }}
            </p>
            <p v-else-if="error" class="text-sm text-destructive">
                {{ error }}
            </p>
        </div>
    </form>
</template>
