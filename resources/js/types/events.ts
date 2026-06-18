export type EventImage = {
    src: string;
    alt: string;
};

export type DiscoveryEvent = {
    id: string;
    title: string;
    description: string;
    type: string;
    status: string;
    venue: string;
    address: string;
    city: string | null;
    country: string | null;
    country_code: string | null;
    latitude: number | null;
    longitude: number | null;
    starts_at_iso: string | null;
    ends_at_iso: string | null;
    timezone: string;
    images: EventImage[];
    attendee_count: number;
};

export type EventFilters = {
    from: string;
    to: string;
    city: string;
    country_code: string;
    type: string;
};

export type LocationFacet = {
    city: string;
    country: string;
    country_code: string;
    label: string;
};

export type DiscoveryFacets = {
    types: string[];
    locations: LocationFacet[];
};

export type DiscoveryResponse = {
    data: DiscoveryEvent[];
    current_page: number;
    last_page: number;
    total: number;
    facets: DiscoveryFacets;
};
