# LaravelPostcodes

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

A service wrapper around postcodes.io with validation rule and macro

## Install

Via Composer

``` bash
$ composer require juststeveking/laravel-postcodes
```

## Usage

You can use the validation rule:

``` php
<?php

$this->validate($request, [
    'postcode' => [
        'required',
        'string',
        new Postcode()
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

If you want to interact with the service itself

```php
<?php 

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

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/juststeveking/laravel-postcodes.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/JustSteveKing/LaravelPostcodes/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/JustSteveKing/LaravelPostcodes.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/JustSteveKing/LaravelPostcodes.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/juststeveking/laravel-postcodes.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/juststeveking/laravel-postcodes
[link-travis]: https://travis-ci.org/JustSteveKing/LaravelPostcodes
[link-scrutinizer]: https://scrutinizer-ci.com/g/JustSteveKing/LaravelPostcodes/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/JustSteveKing/LaravelPostcodes
[link-downloads]: https://packagist.org/packages/juststeveking/laravel-postcodes
[link-author]: https://github.com/JustSteveKing
[link-contributors]: ../../contributors
