<?php

namespace App\Parameters\Config;

class CSVSeparator extends Config
{
    public function identifier()
    {
        return 'csv_separator';
    }

    public function type()
    {
        return 'string';
    }

    public function default()
    {
        return ',';
    }
}
