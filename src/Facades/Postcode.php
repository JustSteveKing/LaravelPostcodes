<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes\Facades;

use Illuminate\Support\Facades\Facade;
use JustSteveKing\LaravelPostcodes\Service\PostcodeService;

/** @see \JustSteveKing\LaravelPostcodes\Service\PostcodeService */
class Postcode extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return PostcodeService::class;
    }
}
