<?php

namespace Gibbo\Bryn\Calculator\Yahoo\Test;

use Gibbo\Bryn\Calculator\Yahoo\ExchangeRateCalculator;
use PHPUnit\Framework\TestCase;

/**
 * Currency tests.
 */
class ExchangeRateCalculatorTest extends TestCase
{

    /**
     * Can the currency be initialised.
     *
     * @return void
     */
    public function testCanBeInitialised()
    {
        $this->assertInstanceOf(ExchangeRateCalculator::class, new ExchangeRateCalculator());
    }
}