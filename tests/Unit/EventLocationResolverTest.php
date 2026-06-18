<?php

use App\Services\EventLocationResolver;

it('maps a known seeded coordinate to a readable location', function () {
    $location = (new EventLocationResolver())->resolve(40.7128, -74.0060);

    expect($location)->toMatchArray([
        'city' => 'New York',
        'country' => 'United States',
        'country_code' => 'US',
        'timezone' => 'America/New_York',
    ]);
});
