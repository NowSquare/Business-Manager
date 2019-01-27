<?php

namespace CommerceGuys\Intl\Tests\NumberFormat;

use CommerceGuys\Intl\NumberFormat\NumberFormatRepository;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass \CommerceGuys\Intl\NumberFormat\NumberFormatRepository
 */
class NumberFormatRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::get
     * @covers ::createNumberFormatFromDefinition
     *
     * @uses \CommerceGuys\Intl\NumberFormat\NumberFormat
     * @uses \CommerceGuys\Intl\Locale
     */
    public function testGet()
    {
        $numberFormatRepository = new NumberFormatRepository('de');

        $numberFormat = $numberFormatRepository->get('en');
        $this->assertInstanceOf('CommerceGuys\\Intl\\NumberFormat\\NumberFormat', $numberFormat);
        $this->assertEquals('en', $numberFormat->getLocale());
        $this->assertEquals('latn', $numberFormat->getNumberingSystem());
        $this->assertEquals('#,##0.###', $numberFormat->getDecimalPattern());
        $this->assertEquals('#,##0%', $numberFormat->getPercentPattern());
        $this->assertEquals('¤#,##0.00', $numberFormat->getCurrencyPattern());
        $this->assertEquals('¤#,##0.00;(¤#,##0.00)', $numberFormat->getAccountingCurrencyPattern());
        $this->assertEquals('.', $numberFormat->getDecimalSeparator());
        $this->assertEquals(',', $numberFormat->getGroupingSeparator());
        $this->assertEquals('+', $numberFormat->getPlusSign());
        $this->assertEquals('-', $numberFormat->getMinusSign());
        $this->assertEquals('%', $numberFormat->getPercentSign());

        $numberFormat = $numberFormatRepository->get('es');
        $this->assertEquals('es', $numberFormat->getLocale());

        $numberFormat = $numberFormatRepository->get('UNKNOWN');
        $this->assertEquals('de', $numberFormat->getLocale());
    }
}
