<p align="center">

![](laravel-postal-code-validation.png)

</p>

# LaravelPostcodes

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-github-action]][link-github-action]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

A service wrapper around [postcodes.io](https://postcodes.io/) with validation rule and macro

## Install

Via Composer

```bash
$ composer require juststeveking/laravel-postcodes
```

After installation, merge configuration for services using:

```bash
$ php artisan vendor:publish --provider="JustSteveKing\LaravelPostcodes\PostcodesServiceProvider"
```

If, for some reason, this doesn't work please use the following steps:

- Add the following into the `config/services.php` configuration file:

```php
<?php

'postcodes' => [
    'url' => env('POSTCODES_URL', 'https://api.postcodes.io/')
],
```

- Add `POSTCODES_URL` to your `.env` file and add `https://api.postcodes.io/` as the value.


## Basic Usage

You can use the validation rule:

``` php
<?php

$this->validate($request, [
    'postcode' => [
        'required',
        'string',
        new Postcode(resolve(PostcodeService::class))
    ]
]);
```

Or you can use the validation Macro:

```php
$this->validate($request, [
    'postcode' => [
        'required',
        'string',
        Rule::postcode()
    ]
]);
```

If you want to interact with the service itself:

```php
<?php 

use JustSteveKing\LaravelPostcodes\Service\PostcodeService;

class SomeController extends Controller
{
    protected $postcodes;

    public function __construct(PostcodeService $service)
    {
        $this->postcodes = $service;
    }

    public function store(Request $request)
    {
        // validation using example above
        $location = $this->postcodes->getPostcode($request->postcode);
    }
}
```

Or use the facade:

```php
<?php 

class SomeController extends Controller
{
    public function store(Request $request)
    {
        // validation using example above
        $location = Postcode::getPostcode($request->postcode);
    }
}
```

### Validate

```php
<?php

$service = resolve(PostcodeService::class);

$service->validate('AB10 1AB');

// You can also use the facade:
Postcode::validate('AB10 1AB');
```

### Validate Postcode

```php
<?php

$service = resolve(PostcodeService::class);

$service->validate('AB10 1AB');

// You can also use the facade:
Postcode::validate('AB10 1AB');
```

### Get Postcode information

```php
<?php

$service = resolve(PostcodeService::class);

$service->getPostcode('AB10 1AB');

// You can also use the facade:
Postcode::getPostcode('AB10 1AB');
```


### Bulk Lookup Postcodes

```php
<?php

$service = resolve(PostcodeService::class);

$service->getPostcodes([
    'AB10 1AB',
    'AB10 1AF',
    'AB10 1AG',
]);

// You can also use the facade:
Postcode::getPostcodes([
    'AB10 1AB',
    'AB10 1AF',
    'AB10 1AG',
]);
```

### Get nearest postcodes for a given longitude & latitude

```php
<?php

$service = resolve(PostcodeService::class);

$service->nearestPostcodesForGivenLngAndLat(
    0.629806,
    51.792326
);

// You can also use the facade:
Postcode::nearestPostcodesForGivenLngAndLat(
    0.629806,
    51.792326
);
```

### Nearest postcodes for postcode

```php
<?php

$service = resolve(PostcodeService::class);

$service->nearest('AB10 1AB');

// You can also use the facade:
Postcode::nearest('AB10 1AB');
```

### Autocomplete a postcode partial

```php
<?php

$service = resolve(PostcodeService::class);

$service->autocomplete('AB10');

// You can also use the facade:
Postcode::autocomplete('AB10');
```

### Query for postcode

```php
<?php

$service = resolve(PostcodeService::class);

$service->query('AB10 1AB');

// You can also use the facade:
Postcode::query('AB10 1AB');
```

### Lookup terminated postcode

```php
<?php

$service = resolve(PostcodeService::class);

$service->getTerminatedPostcode('AB1 0AA');

// You can also use the facade:
Postcode::getTerminatedPostcode('AB1 0AA');
```

### Lookup Outward Code

```php
<?php

$service = resolve(PostcodeService::class);

$service->getOutwardCode('N11');

// You can also use the facade:
Postcode::getOutwardCode('N11');
```

### Nearest outward code for outward code

```php
<?php

$service = resolve(PostcodeService::class);

$limit = 80; // Limit needs to be less than 100
$radius = 15000; // Radius needs to be less than 25000
$service->getNearestOutwardCode('N11', $limit, $radius);

// You can also use the facade:
Postcode::getNearestOutwardCode('N11', $limit, $radius);
```

### Get nearest outward codes for a given longitude & latitude

```php
<?php

$service = resolve(PostcodeService::class);

$service->nearestOutwardCodesForGivenLngAndLat(
    0.629806,
    51.792326
);

// You can also use the facade:
Postcode::nearestOutwardCodesForGivenLngAndLat(
    0.629806,
    51.792326
);
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email juststevemcd@gmail.com instead of using the issue tracker.

## Credits

- [Steve McDougall][link-author]
- [All Contributors][link-contributors]
- [Laravel News for the artwork](https://www.laravel-news.com)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/juststeveking/laravel-postcodes.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-github-action]: https://github.com/JustSteveKing/LaravelPostcodes/workflows/build-tests/badge.svg?branch=master
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/JustSteveKing/LaravelPostcodes.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/JustSteveKing/LaravelPostcodes.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/juststeveking/laravel-postcodes.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/juststeveking/laravel-postcodes
[link-github-action]: https://github.com/JustSteveKing/LaravelPostcodes/actions
[link-scrutinizer]: https://scrutinizer-ci.com/g/JustSteveKing/LaravelPostcodes/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/JustSteveKing/LaravelPostcodes
[link-downloads]: https://packagist.org/packages/juststeveking/laravel-postcodes
[link-author]: https://github.com/JustSteveKing
[link-contributors]: ../../contributors
