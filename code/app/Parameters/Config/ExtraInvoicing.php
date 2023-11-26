<?php

namespace App\Parameters\Config;

class ExtraInvoicing extends Config
{
    public function identifier()
    {
        return 'extra_invoicing';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'business_name' => '',
            'taxcode' => '',
            'vat' => '',
            'address' => '',
            'invoices_counter' => 0,
            'invoices_counter_year' => date('Y'),
        ];
    }

    public function handleSave($gas, $request)
    {
        if ($request->has('enable_extra_invoicing')) {
            $invoicing_info = $gas->extra_invoicing;
            $invoicing_info['business_name'] = $request->input('extra_invoicing->business_name');
            $invoicing_info['taxcode'] = $request->input('extra_invoicing->taxcode');
            $invoicing_info['vat'] = $request->input('extra_invoicing->vat');
            $invoicing_info['address'] = $request->input('extra_invoicing->address');
            $invoicing_info['invoices_counter_year'] = date('Y');

            $reset_counter = $request->input('extra_invoicing->invoices_counter');
            if (!empty($reset_counter)) {
                $invoicing_info['invoices_counter'] = $reset_counter;
            }
        }
        else {
            $invoicing_info = [
                'business_name' => '',
                'taxcode' => '',
                'vat' => '',
                'address' => '',
                'invoices_counter' => 0,
                'invoices_counter_year' => '',
            ];
        }

        $gas->setConfig('extra_invoicing', $invoicing_info);
    }
}
