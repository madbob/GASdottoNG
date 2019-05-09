<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InnerLog extends Model
{
    private static function saveLog($level, $type, $message)
    {
        $i = new InnerLog();
        $i->level = $level;
        $i->type = $type;
        $i->message = $message;
        $i->save();
    }

    public static function error($type, $message)
    {
        self::saveLog('error', $type, $message);
    }
}
