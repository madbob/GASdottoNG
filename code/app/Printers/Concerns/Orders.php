<?php

namespace App\Printers\Concerns;

use Illuminate\Support\Facades\Mail;

use App\Notifications\GenericOrderShipping;

trait Orders
{
    use Shipping, Summary, Table;

    protected function bookingsRules($status)
    {
        if ($status == 'saved' || $status == 'shipped') {
            $get_total = 'delivered';
            $get_function = 'getDeliveredQuantity';
        }
        else {
            $get_total = 'booked';
            $get_function = 'getBookedQuantity';
        }

        return [$get_total, $get_function];
    }

    /*
        Questo serve a determinare quali valori prendere da prodotti e
        prenotazioni a seconda che siano state chieste delle quantità prenotato
        o consegnate
    */
    protected static function offsetsByStatus($status)
    {
        if ($status == 'shipped') {
            return (object) [
                'alternate' => true,
                'by_variant' => 'delivered',
                'by_product' => 'delivered_pieces',
                'by_booking' => 'delivered',
            ];
        }
        else {
            return (object) [
                'alternate' => false,
                'by_variant' => 'quantity',
                'by_product' => 'quantity_pieces',
                'by_booking' => 'booked',
            ];
        }
    }

    protected function filterExtraModifiers($modifiers, $extras)
    {
        if ($extras === false) {
            $modifiers = $modifiers->filter(function ($mod) {
                return is_null($mod->modifier->movementType);
            });
        }

        return $modifiers;
    }

    private function formatProduct($fields, $formattable, $product_redux, $product, $internal_offsets): array
    {
        $ret = [];

        if ($product_redux != null) {
            if (! empty($product_redux->variants)) {
                $offset = $internal_offsets->by_variant;

                foreach ($product_redux->variants as $variant) {
                    if ($variant->$offset == 0) {
                        continue;
                    }

                    $row = [];
                    foreach ($fields as $f) {
                        if (isset($formattable[$f])) {
                            if (isset($formattable[$f]->format_variant)) {
                                $row[] = call_user_func($formattable[$f]->format_variant, $product, $variant, $internal_offsets->alternate);
                            }
                            else {
                                $row[] = call_user_func($formattable[$f]->format_product, $product, $variant, $internal_offsets->alternate);
                            }
                        }
                    }

                    $ret[] = $row;
                }

                usort($ret, function ($a, $b) {
                    return $a[0] <=> $b[0];
                });
            }
            else {
                $offset = $internal_offsets->by_product;
                if ($product_redux->$offset != 0) {
                    $row = [];

                    foreach ($fields as $f) {
                        if (isset($formattable[$f])) {
                            $row[] = call_user_func($formattable[$f]->format_product, $product, $product_redux, $internal_offsets->alternate);
                        }
                    }

                    $ret[] = $row;
                }
            }
        }

        return $ret;
    }

    protected function sendDocumentMail($request, $temp_file_path)
    {
        $recipient_mails = $request['recipient_mail_value'] ?? [];

        $real_recipient_mails = array_map(function ($item) {
            return (object) ['email' => $item];
        }, array_filter($recipient_mails));

        if (empty($real_recipient_mails)) {
            return;
        }

        try {
            Mail::to($real_recipient_mails)->send(new GenericOrderShipping($temp_file_path, $request['subject_mail'], $request['body_mail']));
        }
        catch (\Exception $e) {
            \Log::error('Impossibile inoltrare documento ordine: ' . $e->getMessage());
        }

        @unlink($temp_file_path);
    }

    /*
        Reminder: questa viene usata anche per le istanze di
        App\Printers\Components\Document (che opportunamente hanno la stessa API
        di PDF)
    */
    protected function outputPdf($params, $filename, $pdf)
    {
        if ($params->action == 'download') {
            return $pdf->download($filename);
        }
        else {
            $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);
            $pdf->save($temp_file_path);

            if ($params->action == 'email') {
                $this->sendDocumentMail($params->request, $temp_file_path);
            }
            elseif ($params->action == 'save') {
                return $temp_file_path;
            }
        }
    }

    protected function outputCsv($params, $filename, $headers, $data)
    {
        if ($params->action == 'email') {
            $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);
            output_csv($filename, $headers, $data, null, $temp_file_path);
            $this->sendDocumentMail($params->request, $temp_file_path);
        }
        elseif ($params->action == 'download') {
            return output_csv($filename, $headers, $data, null);
        }
        elseif ($params->action == 'save') {
            $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);
            output_csv($filename, $headers, $data, null, $temp_file_path);
            return $temp_file_path;
        }
    }

    /*
        TODO Sarebbe opportuno astrarre il tipo di azione desiderata:
        - save per il salvataggio del file e la restituzione del path
        - mail per inviare la mail (al posto del flag send_mail)
        - output per mandare direttamente in output e far scaricare il file
    */
    public function document($obj, $type, $request)
    {
        switch ($type) {
            /*
                Dettaglio Consegne
            */
            case 'shipping':
                return $this->handleShipping($obj, $request);

                /*
                    Riassunto Prodotti
                */
            case 'summary':
                return $this->handleSummary($obj, $request);

                /*
                    Tabella Complessiva
                */
            case 'table':
                return $this->handleTable($obj, $request);

            default:
                \Log::error('Unrecognized type for Aggregate/Order document: ' . $type);

                return null;
        }
    }

    abstract protected function handleShipping($obj, $request);

    abstract protected function handleSummary($obj, $request);

    abstract protected function handleTable($obj, $request);
}
