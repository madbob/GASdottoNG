<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Auth;

class SupplierOrderShipping extends Mailable
{
    use Queueable, SerializesModels, MailFormatter;

    private $order;
    private $pdf_file;
    private $csv_file;

    public function __construct($order, $pdf_file, $csv_file)
    {
        $this->order = $order;
        $this->pdf_file = $pdf_file;
        $this->csv_file = $csv_file;
    }

    public function build()
    {
        $message = $this;
        $message = $this->formatMail($message, 'supplier_summary', []);
        $message = $this->attach($this->pdf_file)->attach($this->csv_file);
        return $message;
    }
}
