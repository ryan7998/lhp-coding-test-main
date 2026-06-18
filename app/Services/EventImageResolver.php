<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Str;

class EventImageResolver
{
    /** @var array<string, array<int, string>> */
    private const IMAGES = [
        'concert' => ['concert-1.svg', 'concert-2.svg'],
        'conference' => ['conference-1.svg', 'conference-2.svg'],
        'meetup' => ['meetup-1.svg', 'meetup-2.svg'],
        'workshop' => ['workshop-1.svg', 'workshop-2.svg'],
        'festival' => ['festival-1.svg', 'festival-2.svg'],
        'sports' => ['sports-1.svg', 'sports-2.svg'],
        'networking' => ['networking-1.svg', 'networking-2.svg'],
        'exhibition' => ['exhibition-1.svg', 'exhibition-2.svg'],
    ];

    /**
     * @return array<int, array{src: string, alt: string}>
     */
    public function imagesForEvent(Event $event, ?string $title = null): array
    {
        $eventType = (string) $event->type;
        $type = array_key_exists($eventType, self::IMAGES) ? $eventType : 'meetup';
        $images = self::IMAGES[$type];
        $offset = abs(crc32((string) $event->getKey())) % count($images);
        $ordered = array_merge(array_slice($images, $offset), array_slice($images, 0, $offset));
        $label = $title ?: Str::headline($type).' event';

        return collect($ordered)
            ->take(3)
            ->map(fn (string $file): array => [
                'src' => "/images/events/{$file}",
                'alt' => "{$label} image",
            ])
            ->values()
            ->all();
    }
}
