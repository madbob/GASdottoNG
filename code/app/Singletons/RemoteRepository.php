<?php

namespace App\Singletons;

use Log;

class RemoteRepository
{
    private $list = null;

    public function getList()
    {
        if (is_null($this->list)) {
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', sprintf('%s/api/list', env('HUB_URL')));
                $response = json_decode($response->getBody());

                usort($response->results, function($a, $b) {
                    return $a->name <=> $b->name;
                });

                $this->list = $response->results;
            }
            catch(\Exception $e) {
                Log::error('Unable to read remote repository: ' . $e->getMessage());
                $this->list = [];
            }
        }

        return $this->list;
    }

    public function getSupplierLink($vat)
    {
        return sprintf('%s/api/get/%s', env('HUB_URL'), $vat);
    }
}
