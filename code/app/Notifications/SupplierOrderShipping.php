<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Log;
use Auth;

use App\Role;

class SupplierOrderShipping extends ManyMailNotification
{
    use Queueable, SerializesModels, MailFormatter, TemporaryFiles;

    private $order;
    private $pdf_file;
    private $csv_file;

    public function __construct($order, $pdf_file, $csv_file)
    {
        $this->order = $order;
        $this->pdf_file = $pdf_file;
        $this->csv_file = $csv_file;
        $this->setFiles([$pdf_file, $csv_file]);
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        $message = $this->formatMail($message, 'supplier_summary', [
            'supplier_name' => $this->order->supplier->name,
            'order_number' => $this->order->number,
        ]);

        $users = Role::everybodyCan('supplier.orders', $this->order->supplier);
        foreach($users as $referent) {
            if (!empty($referent->email)) {
                $message = $message->cc($referent->email);
                // Segnalazione PHPStan invalida: $referent è sempre uno User,
                // che usa ContactableTrait
                // @phpstan-ignore-next-line
                $referent->messageAll($message);
            }
        }

        return $message->attach($this->pdf_file)->attach($this->csv_file);
    }
}
