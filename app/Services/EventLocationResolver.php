<?php

namespace App\Services;

class EventLocationResolver
{
    /**
     * @var array<int, array{city: string, country: string, country_code: string, timezone: string, latitude: float, longitude: float}>
     */
    public const ANCHORS = [
        ['city' => 'New York', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/New_York', 'latitude' => 40.7128, 'longitude' => -74.0060],
        ['city' => 'Los Angeles', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Los_Angeles', 'latitude' => 34.0522, 'longitude' => -118.2437],
        ['city' => 'Chicago', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Chicago', 'latitude' => 41.8781, 'longitude' => -87.6298],
        ['city' => 'Houston', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Chicago', 'latitude' => 29.7604, 'longitude' => -95.3698],
        ['city' => 'Phoenix', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Phoenix', 'latitude' => 33.4484, 'longitude' => -112.0740],
        ['city' => 'Philadelphia', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/New_York', 'latitude' => 39.9526, 'longitude' => -75.1652],
        ['city' => 'San Antonio', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Chicago', 'latitude' => 29.4241, 'longitude' => -98.4936],
        ['city' => 'San Diego', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Los_Angeles', 'latitude' => 32.7157, 'longitude' => -117.1611],
        ['city' => 'Dallas', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Chicago', 'latitude' => 32.7767, 'longitude' => -96.7970],
        ['city' => 'San Jose', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Los_Angeles', 'latitude' => 37.3382, 'longitude' => -121.8863],
        ['city' => 'Austin', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Chicago', 'latitude' => 30.2672, 'longitude' => -97.7431],
        ['city' => 'San Francisco', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Los_Angeles', 'latitude' => 37.7749, 'longitude' => -122.4194],
        ['city' => 'Seattle', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Los_Angeles', 'latitude' => 47.6062, 'longitude' => -122.3321],
        ['city' => 'Denver', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Denver', 'latitude' => 39.7392, 'longitude' => -104.9903],
        ['city' => 'Boston', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/New_York', 'latitude' => 42.3601, 'longitude' => -71.0589],
        ['city' => 'Las Vegas', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Los_Angeles', 'latitude' => 36.1699, 'longitude' => -115.1398],
        ['city' => 'Miami', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/New_York', 'latitude' => 25.7617, 'longitude' => -80.1918],
        ['city' => 'Atlanta', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/New_York', 'latitude' => 33.7490, 'longitude' => -84.3880],
        ['city' => 'Washington', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/New_York', 'latitude' => 38.9072, 'longitude' => -77.0369],
        ['city' => 'Nashville', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Chicago', 'latitude' => 36.1627, 'longitude' => -86.7816],
        ['city' => 'Portland', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Los_Angeles', 'latitude' => 45.5152, 'longitude' => -122.6784],
        ['city' => 'New Orleans', 'country' => 'United States', 'country_code' => 'US', 'timezone' => 'America/Chicago', 'latitude' => 29.9511, 'longitude' => -90.0715],
        ['city' => 'Toronto', 'country' => 'Canada', 'country_code' => 'CA', 'timezone' => 'America/Toronto', 'latitude' => 43.6532, 'longitude' => -79.3832],
        ['city' => 'Montreal', 'country' => 'Canada', 'country_code' => 'CA', 'timezone' => 'America/Toronto', 'latitude' => 45.5019, 'longitude' => -73.5674],
        ['city' => 'Vancouver', 'country' => 'Canada', 'country_code' => 'CA', 'timezone' => 'America/Vancouver', 'latitude' => 49.2827, 'longitude' => -123.1207],
        ['city' => 'Calgary', 'country' => 'Canada', 'country_code' => 'CA', 'timezone' => 'America/Edmonton', 'latitude' => 51.0447, 'longitude' => -114.0719],
        ['city' => 'Ottawa', 'country' => 'Canada', 'country_code' => 'CA', 'timezone' => 'America/Toronto', 'latitude' => 45.4215, 'longitude' => -75.6972],
        ['city' => 'Edmonton', 'country' => 'Canada', 'country_code' => 'CA', 'timezone' => 'America/Edmonton', 'latitude' => 53.5461, 'longitude' => -113.4938],
        ['city' => 'Quebec City', 'country' => 'Canada', 'country_code' => 'CA', 'timezone' => 'America/Toronto', 'latitude' => 46.8139, 'longitude' => -71.2080],
        ['city' => 'Winnipeg', 'country' => 'Canada', 'country_code' => 'CA', 'timezone' => 'America/Winnipeg', 'latitude' => 49.8951, 'longitude' => -97.1384],
        ['city' => 'Mexico City', 'country' => 'Mexico', 'country_code' => 'MX', 'timezone' => 'America/Mexico_City', 'latitude' => 19.4326, 'longitude' => -99.1332],
        ['city' => 'Guadalajara', 'country' => 'Mexico', 'country_code' => 'MX', 'timezone' => 'America/Mexico_City', 'latitude' => 20.6597, 'longitude' => -103.3496],
        ['city' => 'Monterrey', 'country' => 'Mexico', 'country_code' => 'MX', 'timezone' => 'America/Monterrey', 'latitude' => 25.6866, 'longitude' => -100.3161],
        ['city' => 'Puebla', 'country' => 'Mexico', 'country_code' => 'MX', 'timezone' => 'America/Mexico_City', 'latitude' => 19.0414, 'longitude' => -98.2063],
        ['city' => 'Tijuana', 'country' => 'Mexico', 'country_code' => 'MX', 'timezone' => 'America/Tijuana', 'latitude' => 32.5149, 'longitude' => -117.0382],
        ['city' => 'Cancun', 'country' => 'Mexico', 'country_code' => 'MX', 'timezone' => 'America/Cancun', 'latitude' => 21.1619, 'longitude' => -86.8515],
        ['city' => 'Merida', 'country' => 'Mexico', 'country_code' => 'MX', 'timezone' => 'America/Merida', 'latitude' => 20.9674, 'longitude' => -89.5926],
        ['city' => 'London', 'country' => 'United Kingdom', 'country_code' => 'GB', 'timezone' => 'Europe/London', 'latitude' => 51.5074, 'longitude' => -0.1278],
        ['city' => 'Paris', 'country' => 'France', 'country_code' => 'FR', 'timezone' => 'Europe/Paris', 'latitude' => 48.8566, 'longitude' => 2.3522],
        ['city' => 'Berlin', 'country' => 'Germany', 'country_code' => 'DE', 'timezone' => 'Europe/Berlin', 'latitude' => 52.5200, 'longitude' => 13.4050],
        ['city' => 'Madrid', 'country' => 'Spain', 'country_code' => 'ES', 'timezone' => 'Europe/Madrid', 'latitude' => 40.4168, 'longitude' => -3.7038],
        ['city' => 'Rome', 'country' => 'Italy', 'country_code' => 'IT', 'timezone' => 'Europe/Rome', 'latitude' => 41.9028, 'longitude' => 12.4964],
        ['city' => 'Amsterdam', 'country' => 'Netherlands', 'country_code' => 'NL', 'timezone' => 'Europe/Amsterdam', 'latitude' => 52.3676, 'longitude' => 4.9041],
        ['city' => 'Barcelona', 'country' => 'Spain', 'country_code' => 'ES', 'timezone' => 'Europe/Madrid', 'latitude' => 41.3851, 'longitude' => 2.1734],
        ['city' => 'Munich', 'country' => 'Germany', 'country_code' => 'DE', 'timezone' => 'Europe/Berlin', 'latitude' => 48.1351, 'longitude' => 11.5820],
        ['city' => 'Milan', 'country' => 'Italy', 'country_code' => 'IT', 'timezone' => 'Europe/Rome', 'latitude' => 45.4642, 'longitude' => 9.1900],
        ['city' => 'Vienna', 'country' => 'Austria', 'country_code' => 'AT', 'timezone' => 'Europe/Vienna', 'latitude' => 48.2082, 'longitude' => 16.3738],
        ['city' => 'Prague', 'country' => 'Czechia', 'country_code' => 'CZ', 'timezone' => 'Europe/Prague', 'latitude' => 50.0755, 'longitude' => 14.4378],
        ['city' => 'Lisbon', 'country' => 'Portugal', 'country_code' => 'PT', 'timezone' => 'Europe/Lisbon', 'latitude' => 38.7223, 'longitude' => -9.1393],
        ['city' => 'Dublin', 'country' => 'Ireland', 'country_code' => 'IE', 'timezone' => 'Europe/Dublin', 'latitude' => 53.3498, 'longitude' => -6.2603],
        ['city' => 'Copenhagen', 'country' => 'Denmark', 'country_code' => 'DK', 'timezone' => 'Europe/Copenhagen', 'latitude' => 55.6761, 'longitude' => 12.5683],
        ['city' => 'Stockholm', 'country' => 'Sweden', 'country_code' => 'SE', 'timezone' => 'Europe/Stockholm', 'latitude' => 59.3293, 'longitude' => 18.0686],
        ['city' => 'Oslo', 'country' => 'Norway', 'country_code' => 'NO', 'timezone' => 'Europe/Oslo', 'latitude' => 59.9139, 'longitude' => 10.7522],
        ['city' => 'Helsinki', 'country' => 'Finland', 'country_code' => 'FI', 'timezone' => 'Europe/Helsinki', 'latitude' => 60.1699, 'longitude' => 24.9384],
        ['city' => 'Brussels', 'country' => 'Belgium', 'country_code' => 'BE', 'timezone' => 'Europe/Brussels', 'latitude' => 50.8503, 'longitude' => 4.3517],
        ['city' => 'Zurich', 'country' => 'Switzerland', 'country_code' => 'CH', 'timezone' => 'Europe/Zurich', 'latitude' => 47.3769, 'longitude' => 8.5417],
        ['city' => 'Warsaw', 'country' => 'Poland', 'country_code' => 'PL', 'timezone' => 'Europe/Warsaw', 'latitude' => 52.2297, 'longitude' => 21.0122],
        ['city' => 'Budapest', 'country' => 'Hungary', 'country_code' => 'HU', 'timezone' => 'Europe/Budapest', 'latitude' => 47.4979, 'longitude' => 19.0402],
        ['city' => 'Athens', 'country' => 'Greece', 'country_code' => 'GR', 'timezone' => 'Europe/Athens', 'latitude' => 37.9838, 'longitude' => 23.7275],
        ['city' => 'Lyon', 'country' => 'France', 'country_code' => 'FR', 'timezone' => 'Europe/Paris', 'latitude' => 45.7640, 'longitude' => 4.8357],
        ['city' => 'Hamburg', 'country' => 'Germany', 'country_code' => 'DE', 'timezone' => 'Europe/Berlin', 'latitude' => 53.5511, 'longitude' => 9.9937],
        ['city' => 'Manchester', 'country' => 'United Kingdom', 'country_code' => 'GB', 'timezone' => 'Europe/London', 'latitude' => 53.4808, 'longitude' => -2.2426],
        ['city' => 'Edinburgh', 'country' => 'United Kingdom', 'country_code' => 'GB', 'timezone' => 'Europe/London', 'latitude' => 55.9533, 'longitude' => -3.1883],
        ['city' => 'Frankfurt', 'country' => 'Germany', 'country_code' => 'DE', 'timezone' => 'Europe/Berlin', 'latitude' => 50.1109, 'longitude' => 8.6821],
        ['city' => 'Krakow', 'country' => 'Poland', 'country_code' => 'PL', 'timezone' => 'Europe/Warsaw', 'latitude' => 50.0647, 'longitude' => 19.9450],
        ['city' => 'Porto', 'country' => 'Portugal', 'country_code' => 'PT', 'timezone' => 'Europe/Lisbon', 'latitude' => 41.1579, 'longitude' => -8.6291],
        ['city' => 'Naples', 'country' => 'Italy', 'country_code' => 'IT', 'timezone' => 'Europe/Rome', 'latitude' => 40.8518, 'longitude' => 14.2681],
        ['city' => 'Tokyo', 'country' => 'Japan', 'country_code' => 'JP', 'timezone' => 'Asia/Tokyo', 'latitude' => 35.6762, 'longitude' => 139.6503],
        ['city' => 'Seoul', 'country' => 'South Korea', 'country_code' => 'KR', 'timezone' => 'Asia/Seoul', 'latitude' => 37.5665, 'longitude' => 126.9780],
        ['city' => 'Singapore', 'country' => 'Singapore', 'country_code' => 'SG', 'timezone' => 'Asia/Singapore', 'latitude' => 1.3521, 'longitude' => 103.8198],
        ['city' => 'Sydney', 'country' => 'Australia', 'country_code' => 'AU', 'timezone' => 'Australia/Sydney', 'latitude' => -33.8688, 'longitude' => 151.2093],
        ['city' => 'Melbourne', 'country' => 'Australia', 'country_code' => 'AU', 'timezone' => 'Australia/Melbourne', 'latitude' => -37.8136, 'longitude' => 144.9631],
        ['city' => 'Dubai', 'country' => 'United Arab Emirates', 'country_code' => 'AE', 'timezone' => 'Asia/Dubai', 'latitude' => 25.2048, 'longitude' => 55.2708],
        ['city' => 'Sao Paulo', 'country' => 'Brazil', 'country_code' => 'BR', 'timezone' => 'America/Sao_Paulo', 'latitude' => -23.5505, 'longitude' => -46.6333],
        ['city' => 'Buenos Aires', 'country' => 'Argentina', 'country_code' => 'AR', 'timezone' => 'America/Argentina/Buenos_Aires', 'latitude' => -34.6037, 'longitude' => -58.3816],
    ];

    /**
     * @return array<int, array{city: string, country: string, country_code: string, timezone: string, latitude: float, longitude: float}>
     */
    public static function anchors(): array
    {
        return self::ANCHORS;
    }

    /**
     * @return array{city: string|null, country: string|null, country_code: string|null, timezone: string}
     */
    public function resolve(?float $latitude, ?float $longitude): array
    {
        if ($latitude === null || $longitude === null) {
            return [
                'city' => null,
                'country' => null,
                'country_code' => null,
                'timezone' => 'UTC',
            ];
        }

        $nearest = self::ANCHORS[0];
        $nearestDistance = PHP_FLOAT_MAX;

        foreach (self::ANCHORS as $anchor) {
            $distance = $this->distance($latitude, $longitude, $anchor['latitude'], $anchor['longitude']);

            if ($distance < $nearestDistance) {
                $nearest = $anchor;
                $nearestDistance = $distance;
            }
        }

        return [
            'city' => $nearest['city'],
            'country' => $nearest['country'],
            'country_code' => $nearest['country_code'],
            'timezone' => $nearest['timezone'],
        ];
    }

    public function address(?string $venue, ?string $city, ?string $country): string
    {
        return collect([$venue, $city, $country])
            ->filter(fn (?string $part): bool => filled($part))
            ->join(', ');
    }

    private function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
