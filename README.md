# Package to extend filament functionality

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alareqi/filament-extend.svg?style=flat-square)](https://packagist.org/packages/alareqi/filament-extend)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/alareqi/filament-extend/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/alareqi/filament-extend/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/alareqi/filament-extend/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/alareqi/filament-extend/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/alareqi/filament-extend.svg?style=flat-square)](https://packagist.org/packages/alareqi/filament-extend)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require alareqi/filament-extend
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-extend-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-extend-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-extend-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filamentExtend = new Alareqi\FilamentExtend();
echo $filamentExtend->echoPhrase('Hello, Alareqi!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ayman Alareqi](https://github.com/aymanalareqi)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
