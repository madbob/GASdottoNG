<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UtilsTest extends TestCase
{
    public function testDecodeDate()
    {
        $this->assertEquals('', \App\Utils\Utils::decodeDate(''));
        $this->assertEquals('2016-12-01', \App\Utils\Utils::decodeDate('Thursday 01 December 2016'));
        $this->assertEquals('2016-12-29', \App\Utils\Utils::decodeDate('Giovedì 29 Dicembre 2016'));
    }
}
