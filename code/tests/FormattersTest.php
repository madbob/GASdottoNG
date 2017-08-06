<?php

class FormattersTest extends TestCase
{
    public function testPrintablePrice()
    {
        $this->assertEquals('10.00', printablePrice(10));
        $this->assertEquals('10.00', printablePrice('10'));
        $this->assertEquals('10.10', printablePrice(10.1));
        $this->assertEquals('10.12', printablePrice(10.123));
    }

    public function testDecodeDate()
    {
        $this->assertEquals('', decodeDate(''));
        $this->assertEquals('2016-12-01', decodeDate('Thursday 01 December 2016'));
        $this->assertEquals('2016-12-29', decodeDate('Giovedì 29 Dicembre 2016'));
    }

    public function testIbanSplit()
    {
        $iban = 'IT02L1234512345123456789012';
        $this->assertEquals('IT', iban_split($iban, 'country'));
        $this->assertEquals('02', iban_split($iban, 'check'));
        $this->assertEquals('L', iban_split($iban, 'cin'));
        $this->assertEquals('12345', iban_split($iban, 'abi'));
        $this->assertEquals('12345', iban_split($iban, 'cab'));
        $this->assertEquals('123456789012', iban_split($iban, 'account'));

        $iban = 'IT 02 L 1234512345 123456789012';
        $this->assertEquals('IT', iban_split($iban, 'country'));
        $this->assertEquals('02', iban_split($iban, 'check'));
        $this->assertEquals('L', iban_split($iban, 'cin'));
        $this->assertEquals('12345', iban_split($iban, 'abi'));
        $this->assertEquals('12345', iban_split($iban, 'cab'));
        $this->assertEquals('123456789012', iban_split($iban, 'account'));
    }
}
