<?php

class FormattersTest extends TestCase
{
    public function testDecodeDate()
    {
        $this->assertEquals('', decodeDate(''));
        $this->assertEquals('2016-12-01', decodeDate('Thursday 01 December 2016'));
        $this->assertEquals('2016-12-29', decodeDate('Giovedì 29 Dicembre 2016'));
    }
}
