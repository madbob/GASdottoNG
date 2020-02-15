<?php

namespace App;

use DB;

trait ExportableTrait
{
    public function exportableURL()
    {
        return url('import/gdxp?classname=' . get_class($this) . '&id=' . $this->id);
    }

    abstract public static function exportXML();
    abstract public static function readXML($xml);
    abstract public static function exportJSON();
    abstract public static function readJSON($json);
}
