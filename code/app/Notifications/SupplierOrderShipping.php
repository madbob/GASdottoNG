<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

use Log;
use Auth;

use App\Role;

class SupplierOrderShipping extends ManyMailNotification
{
    use Queueable, SerializesModels, MailFormatter, MailReplyTo, TemporaryFiles;

    private $gas;
    private $order;
    private $pdf_file;
    private $csv_file;

    public function __construct($gas, $order, $pdf_file, $csv_file)
    {
        $this->gas = $gas;
        $this->order = $order;
        $this->pdf_file = $pdf_file;
        $this->csv_file = $csv_file;
        $this->setFiles([$pdf_file, $csv_file]);
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        /*
            Nella modalità Multi-GAS un ordine può potenzialmente essere
            assegnato a molteplici GAS, che possono avere configurazioni diverse
            per la formattazione delle mail
        */
        $notifiable->setRelation('gas', new Collection([$this->gas]));

        $message = $this->formatMail($message, $notifiable, 'supplier_summary', [
            'supplier_name' => $this->order->supplier->name,
            'order_number' => $this->order->number,
        ]);

        $users = everybodyCan('supplier.orders', $this->order->supplier);
        foreach($users as $referent) {
            if (!empty($referent->email)) {
                $message = $message->cc($referent->email);
                // Segnalazione PHPStan invalida: $referent è sempre uno User,
                // che usa ContactableTrait
                // @phpstan-ignore-next-line
                $referent->messageAll($message);
            }
        }

        $message->attach($this->pdf_file)->attach($this->csv_file);
        $message = $this->guessReplyTo($message, $this->order);

        return $message;
    }
}
