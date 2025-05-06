<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

use Carbon\Carbon;

class InnerLog extends Model
{
    use Prunable;

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

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', Carbon::today()->subMonths(3));
    }
}
