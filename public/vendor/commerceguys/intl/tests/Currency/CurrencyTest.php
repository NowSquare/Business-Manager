<?php

namespace CommerceGuys\Intl\Tests\Currency;

use CommerceGuys\Intl\Currency\Currency;

/**
 * @coversDefaultClass \CommerceGuys\Intl\Currency\Currency
 */
class CurrencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function testMissingProperty()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Missing required property "currency_code".');
        $currency = new Currency([]);
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::getCurrencyCode
     * @covers ::getName
     * @covers ::getNumericCode
     * @covers ::getSymbol
     * @covers ::getFractionDigits
     * @covers ::getLocale
     */
    public function testValid()
    {
        $definition = [
            'currency_code' => 'USD',
            'name' => 'dollar des États-Unis',
            'numeric_code' => '840',
            'symbol' => '$US',
            // Dummy value, intentionally different from the default.
            'fraction_digits' => 3,
            'locale' => 'fr',
        ];
        $currency = new Currency($definition);

        $this->assertEquals($definition['currency_code'], $currency->__toString());
        $this->assertEquals($definition['currency_code'], $currency->getCurrencyCode());
        $this->assertEquals($definition['name'], $currency->getName());
        $this->assertEquals($definition['numeric_code'], $currency->getNumericCode());
        $this->assertEquals($definition['symbol'], $currency->getSymbol());
        $this->assertEquals($definition['fraction_digits'], $currency->getFractionDigits());
        $this->assertEquals($definition['locale'], $currency->getLocale());
    }
}
