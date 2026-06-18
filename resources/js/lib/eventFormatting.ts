import type { DiscoveryEvent } from '@/types/events';

const defaultDateTimeOptions: Intl.DateTimeFormatOptions = {
    dateStyle: 'medium',
    timeStyle: 'short',
};

function safeTimezone(timezone: string | null | undefined): string {
    if (!timezone) {
        return 'UTC';
    }

    try {
        new Intl.DateTimeFormat(undefined, { timeZone: timezone }).format();

        return timezone;
    } catch {
        return 'UTC';
    }
}

export function localDateInput(date = new Date()): string {
    const offset = date.getTimezoneOffset() * 60_000;

    return new Date(date.getTime() - offset).toISOString().slice(0, 10);
}

export function viewerTimezone(): string {
    return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
}

export function formatEventDateTime(
    iso: string | null,
    timezone: string | null | undefined,
    options: Intl.DateTimeFormatOptions = defaultDateTimeOptions,
): string {
    if (!iso) {
        return 'Time to be announced';
    }

    return new Intl.DateTimeFormat(undefined, {
        ...options,
        timeZone: safeTimezone(timezone),
    }).format(new Date(iso));
}

export function formatViewerDateTime(iso: string | null): string {
    return formatEventDateTime(iso, viewerTimezone(), {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

export function formatDateGroup(event: DiscoveryEvent): string {
    if (!event.starts_at_iso) {
        return 'Date to be announced';
    }

    return new Intl.DateTimeFormat(undefined, {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        timeZone: safeTimezone(event.timezone),
    }).format(new Date(event.starts_at_iso));
}

export function formatCompactDate(event: DiscoveryEvent): string {
    if (!event.starts_at_iso) {
        return 'TBA';
    }

    return new Intl.DateTimeFormat(undefined, {
        month: 'short',
        day: 'numeric',
        timeZone: safeTimezone(event.timezone),
    }).format(new Date(event.starts_at_iso));
}
