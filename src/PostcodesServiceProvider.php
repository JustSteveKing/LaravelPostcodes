<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use JustSteveKing\LaravelPostcodes\Service\PostcodeService;

class PostcodesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/services.php',
            'services'
        );

        $this->app->bind(PostcodeService::class, function ($app) {
            return new PostcodeService(
                new Client()
            );
        });

        \Illuminate\Validation\Rule::macro('postcode', function () {
            return new \JustSteveKing\LaravelPostcodes\Rules\Postcode();
        });
    }

    public function boot()
    {
        $this->loadTranslationsFrom(
            __DIR__ . '/../resources/lang',
            'postcodes'
        );

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/postcodes'),
        ]);
    }
}
