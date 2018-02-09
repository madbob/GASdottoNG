<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class SupplierDeleting
{
    use SerializesModels;

    public $supplier;

    public function __construct(Model $supplier)
    {
        $this->supplier = $supplier;
    }
}
