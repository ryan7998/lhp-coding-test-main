<?php

use App\Models\Event;
use App\Services\EventImageResolver;

it('returns at least two local images for an event', function () {
    $event = new Event;
    $event->forceFill([
        'id' => '00000000-0000-4000-8000-000000000001',
        'type' => 'concert',
    ]);

    $images = (new EventImageResolver)->imagesForEvent($event, 'Test Concert');

    expect($images)->toHaveCount(2);

    foreach ($images as $image) {
        $path = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'public'.str_replace('/', DIRECTORY_SEPARATOR, $image['src']);

        expect(is_file($path))->toBeTrue();
    }
});
