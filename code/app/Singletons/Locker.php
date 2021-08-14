<?php

namespace App\Singletons;

use Cache;

class Locker
{
    public function execute($identifier, $callback)
    {
        $tries = 0;
        $identifier = sprintf('%s_%s', env('DB_DATABASE'), $identifier);

        while (Cache::get($identifier) && $tries < 10) {
            sleep(50000);
            $tries++;
        }

        Cache::put($identifier, '1');

        try {
            $ret = $callback();
            Cache::forget($identifier);
            return $ret;
        }
        catch(\Exception $e) {
            Cache::forget($identifier);
            throw $e;
        }
    }
}
