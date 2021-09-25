<?php

namespace Tests;

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

    public function testUnrollPeriodicWeekly()
    {
        $test = (object) [
            'from' => '2021-08-01',
            'to' => '2021-10-31',
            'cycle' => 'all',
            'day' => 'tuesday',
        ];

        $dates = unrollPeriodic($test);

        $valid_dates = [
            '2021-08-03',
            '2021-08-10',
            '2021-08-17',
            '2021-08-24',
            '2021-08-31',
            '2021-09-07',
            '2021-09-14',
            '2021-09-21',
            '2021-09-28',
            '2021-10-05',
            '2021-10-12',
            '2021-10-19',
            '2021-10-26',
        ];

        $this->assertEquals(count($dates), count($valid_dates));

        foreach($dates as $index => $date) {
            $this->assertEquals($date, $valid_dates[$index]);
        }
    }

    public function testUnrollPeriodicMonthFirst()
    {
        $test = (object) [
            'from' => '2021-08-01',
            'to' => '2021-10-31',
            'cycle' => 'month_first',
            'day' => 'wednesday',
        ];

        $dates = unrollPeriodic($test);

        $valid_dates = [
            '2021-08-04',
            '2021-09-01',
            '2021-10-06',
        ];

        $this->assertEquals(count($dates), count($valid_dates));

        foreach($dates as $index => $date) {
            $this->assertEquals($date, $valid_dates[$index]);
        }
    }

    public function testUnrollPeriodicBiWeekly()
    {
        /*
            Qui eseguo due volte lo stesso test, ma prima partendo da una data
            antecedente alla prima effettiva data che deve risultare e poi
            partendo dalla prima data utile per l'intervallo di ricorrenza
        */

        $valid_dates = [
            '2021-08-02',
            '2021-08-16',
            '2021-08-30',
            '2021-09-13',
            '2021-09-27',
            '2021-10-11',
            '2021-10-25',
        ];

        $test = (object) [
            'from' => '2021-08-01',
            'to' => '2021-10-31',
            'cycle' => 'biweekly',
            'day' => 'monday',
        ];

        $dates = unrollPeriodic($test);
        $this->assertEquals(count($dates), count($valid_dates));

        foreach($dates as $index => $date) {
            $this->assertEquals($date, $valid_dates[$index]);
        }

        $test = (object) [
            'from' => '2021-08-02',
            'to' => '2021-10-31',
            'cycle' => 'biweekly',
            'day' => 'monday',
        ];

        $dates = unrollPeriodic($test);
        $this->assertEquals(count($dates), count($valid_dates));

        foreach($dates as $index => $date) {
            $this->assertEquals($date, $valid_dates[$index]);
        }
    }
}
