<?php

namespace Tests;

use Tests\TestCase;

class FormattersTest extends TestCase
{
    public function testPrintablePrice()
    {
        $this->assertEquals('10.00', printablePrice(10));
        $this->assertEquals('10.00', printablePrice('10'));
        $this->assertEquals('10.10', printablePrice(10.1));
        $this->assertEquals('10.12', printablePrice(10.123));
    }

    public function testApplyPercentage()
    {
        $this->assertEquals(32, applyPercentage(42, '10'));
        $this->assertEquals(37.8, applyPercentage(42, '10%'));
        $this->assertEquals(52, applyPercentage(42, '10', '+'));
        $this->assertEquals(46.2, applyPercentage(42, '10%', '+'));
    }

    public function testEnforceNumber()
    {
        $this->assertEquals(42, enforceNumber(42));
        $this->assertEquals(-10, enforceNumber(-10));
        $this->assertEquals(5.12, enforceNumber(5.12));
        $this->assertEquals(0, enforceNumber('broken'));
    }

    public function testNormalizeUrl()
    {
        $this->assertEquals('http://example.com', normalizeUrl('example.com'));
    }

    public function testDecodeDate()
    {
        $this->assertEquals('', decodeDate(''));
        $this->assertEquals('2016-12-29', decodeDate('Giovedì 29 Dicembre 2016'));
    }

    public function testOutputCsv()
    {
        $path = sys_get_temp_dir() . '/test.csv';
        $path = output_csv('test', ['head 1', 'head 2', 'head 3'], [
            [1, 'first row', 'prima riga'],
            [2, 'second row', 'seconda riga'],
        ], null, $path);

        $this->assertTrue(file_exists($path));
        $wrote_contents = file($path);
        $this->assertEquals(3, count($wrote_contents));
        $this->assertEquals('"head 1","head 2","head 3"' . "\n", $wrote_contents[0]);
        $this->assertEquals('1,"first row","prima riga"' . "\n", $wrote_contents[1]);
        $this->assertEquals('2,"second row","seconda riga"' . "\n", $wrote_contents[2]);
    }

    public function testIbanSplit()
    {
        $ibans = ['IT02L1234512345123456789012', 'IT 02 L 1234512345 123456789012'];
        foreach($ibans as $iban) {
            $this->assertEquals('IT', iban_split($iban, 'country'));
            $this->assertEquals('02', iban_split($iban, 'check'));
            $this->assertEquals('L', iban_split($iban, 'cin'));
            $this->assertEquals('12345', iban_split($iban, 'abi'));
            $this->assertEquals('12345', iban_split($iban, 'cab'));
            $this->assertEquals('123456789012', iban_split($iban, 'account'));
        }
    }

    public function testHumanSizeToBytes()
    {
        $this->assertEquals(1024, humanSizeToBytes('1k'));
        $this->assertEquals(2202009, humanSizeToBytes('2.1M'));
        $this->assertEquals(1073741824, humanSizeToBytes('1G'));
    }
}
