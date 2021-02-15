<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Log;
use Auth;

use App\Role;

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

        $message = $this->formatMail($message, 'supplier_summary', [
            'supplier_name' => $this->order->supplier->name,
            'order_number' => $this->order->number,
        ]);

        $supplier_mail = $this->order->supplier->email;

        if (empty($supplier_mail)) {
            Log::error('Nessuna email per il fornitore cui mandare il riepilogo ordini');
            return null;
        }

        $message->to($supplier_mail);
        $this->order->supplier->messageAll($message);

        $users = Role::everybodyCan('supplier.orders', $this->order->supplier);
        foreach($users as $referent) {
            if (!empty($referent->email)) {
                $message = $message->cc($referent->email);
                $referent->messageAll($message);
            }
        }

        $message = $message->attach($this->pdf_file)->attach($this->csv_file);
        return $message;
    }
}
