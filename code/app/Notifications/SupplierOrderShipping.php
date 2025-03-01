<?php

namespace App\Notifications;

use Illuminate\Support\Collection;

use App\Notifications\Concerns\ManyMailNotification;
use App\Notifications\Concerns\MailFormatter;
use App\Notifications\Concerns\MailReplyTo;
use App\Notifications\Concerns\TemporaryFiles;

class SupplierOrderShipping extends ManyMailNotification
{
    use MailFormatter, MailReplyTo, TemporaryFiles;

    private $gas;

    private $order;

    public function __construct($gas, $order, $files)
    {
        $this->gas = $gas;
        $this->order = $order;
        $this->setFiles($files);
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
        foreach ($users as $referent) {
            if (! empty($referent->email)) {
                $message = $message->cc($referent->email);
                $referent->messageAll($message);
            }
        }

        foreach ($this->getFiles() as $file) {
            $message->attach($file);
        }

        $message = $this->guessReplyTo($message, $this->order);

        return $message;
    }
}
