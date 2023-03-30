<?php

namespace App\Observers;

use App\Invoice;

class InvoiceObserver
{
    public function deleting(Invoice $invoice)
    {
		$invoice->deleteMovements();
		return true;
    }
}
