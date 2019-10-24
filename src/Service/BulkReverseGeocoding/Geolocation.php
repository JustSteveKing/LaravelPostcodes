<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes\Service\BulkReverseGeocoding;

class Geolocation
{
    /** @var float */
    private $lng;
    /** @var float */
    private $lat;
    /** @var int|null */
    private $radius;
    /** @var int|null */
    private $limit;

    public function __construct(
        float $lng,
        float $lat,
        int $radius = null,
        int $limit = null
    ) {
        $this->lng = $lng;
        $this->lat = $lat;
        $this->radius = $radius;
        $this->limit = $limit;
    }

    public function getLng(): float
    {
        return $this->lng;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getRadius(): ?int
    {
        return $this->radius;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function toArray(): array
    {
        return [
            'longitude' => $this->getLng(),
            'latitude' => $this->getLat(),
        ] + array_filter([
            'radius' => $this->getRadius(),
            'limit' => $this->getLimit(),
        ]);
    }
}
