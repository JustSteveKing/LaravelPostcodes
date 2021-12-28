<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes;

use GuzzleHttp\Client;
use Illuminate\Validation\Rule;
use Illuminate\Support\ServiceProvider;
use JustSteveKing\LaravelPostcodes\Rules\Postcode;
use JustSteveKing\LaravelPostcodes\Service\PostcodeService;

class PostcodesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/services.php',
            'services'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../config/postcodes.php',
            'postcodes'
        );

        $this->app->bind(PostcodeService::class, function () {
            return new PostcodeService(
                new Client()
            );
        });

        Rule::macro('postcode', function () {
            return new Postcode(resolve(PostcodeService::class));
        });
    }
}
