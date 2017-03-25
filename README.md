# Bryn - Yahoo Calculator

[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.org/ben-gibson/bryn-yahoo-calculator.svg?branch=master)](https://travis-ci.org/ben-gibson/bryn-yahoo-calculator)

An exchange rate calculator for [Bryn](https://github.com/ben-gibson/bryn) that pulls data from Yahoo's YQL Finance API. [HTTPlug](http://httplug.io/) 
is used to support multiple `HTTP` clients including `Guzzle`, `Buzz`, and `Curl`.

## Install

Use composer to install this library and your preferred `HTTP` client.

``` bash
$ composer require php-http/guzzle6-adapter
$ composer require ben-gibson/bryn-yahoo-calculator
```

## Usage

``` php
<?php
    
require 'vendor/autoload.php';
    
$calculator = \Gibbo\Bryn\Calculator\Yahoo\ExchangeRateCalculator::default();
    
$exchangeRate = $calculator->getRate(
    new \Gibbo\Bryn\Exchange(
        \Gibbo\Bryn\Currency::GBP(),
        \Gibbo\Bryn\Currency::USD()
    )
);
    
echo $exchangeRate;
echo $exchangeRate->convert(550);
echo $exchangeRate->flip()->convert(550);
    
/**
 * OUTPUTS:
 *
 * 1 GBP(£) = 1.25 USD($)
 * 686.2295
 * 440.814
 */
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email ben.gibson.2011@gmail.com instead of using the issue tracker.

## Credits

- [Ben Gibson][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-license]: https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square
[link-author]: https://github.com/ben-gibson
[link-contributors]: ../../contributors