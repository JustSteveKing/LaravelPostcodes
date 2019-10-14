<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        try {
            $env = parse_ini_file(__DIR__ . '/../.env');
            if (isset($env['POSTCODES_URL'])) {
                $this->app['config']->set('services.postcodes.url', $env['POSTCODES_URL']);
            }
        } catch (\Exception $e) {
            //
        }

        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            \JustSteveKing\LaravelPostcodes\PostcodesServiceProvider::class
        ];
    }
}
