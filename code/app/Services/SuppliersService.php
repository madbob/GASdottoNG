<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

use Auth;
use Log;
use DB;
use PDF;

use App\User;
use App\Supplier;
use App\Role;

class SuppliersService extends BaseService
{
    public function list($term = '', $all = false)
    {
        $this->ensureAuth();
        $query = Supplier::orderBy('name', 'asc');

        if (!empty($term)) {
            $query->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%$term%");
            });
        }

        if ($all)
            $query->filterEnabled();

        $suppliers = $query->get();
        return $suppliers;
    }

    public function show($id)
    {
        return Supplier::withTrashed()->findOrFail($id);
    }

    private function setCommonAttributes($supplier, $request)
    {
        $this->setIfSet($supplier, $request, 'name');
        $this->setIfSet($supplier, $request, 'business_name');
        $this->setIfSet($supplier, $request, 'taxcode');
        $this->setIfSet($supplier, $request, 'vat');
        $this->setIfSet($supplier, $request, 'description');
        $this->setIfSet($supplier, $request, 'payment_method');
        $this->setIfSet($supplier, $request, 'order_method');

        if (isset($request['status'])) {
            $supplier->setStatus($request['status'], $request['deleted_at']);
        }
    }

    public function store(array $request)
    {
        $creator = $this->ensureAuth(['supplier.add' => 'gas']);

        if (!isset($request['payment_method']) || is_null($request['payment_method']))
            $request['payment_method'] = '';
        if (!isset($request['order_method']) || is_null($request['order_method']))
            $request['order_method'] = '';

        $supplier = new Supplier();
        $this->setCommonAttributes($supplier, $request);

        DB::transaction(function () use ($supplier, $creator) {
            $supplier->save();

            $roles = Role::havingAction('supplier.modify');
            foreach($roles as $r) {
                $creator->addRole($r, $supplier);
            }
        });

        return $supplier;
    }

    public function update($id, array $request)
    {
        $supplier = $this->show($id);
        $this->ensureAuth(['supplier.modify' => $supplier]);

        try {
            $supplier = DB::transaction(function () use ($supplier, $request) {
                $this->setCommonAttributes($supplier, $request);
                $supplier->save();
                $supplier->updateContacts($request);
                return $supplier;
            });
        }
        catch(\Exception $e) {
            Log::error('Errore aggiornamento fornitore: ' . $e->getMessage() . ' - ' . print_r($request, true));
            $supplier = null;
        }

        return $supplier;
    }

    public function catalogue($id, $format, array $request)
    {
        $this->ensureAuth();
        $supplier = $this->show($id);
        $filename = sprintf('Listino %s.%s', $supplier->name, $format);

        if (isset($request['printable']))
            $products = $supplier->products()->whereIn('id', $request['printable'])->get();
        else
            $products = $supplier->products()->where('active', true)->get();

        if ($format == 'pdf') {
            $html = view('documents.cataloguepdf', ['supplier' => $supplier, 'products' => $products])->render();
            PDF::SetTitle(_i('Listino %s del %s', $supplier->name, date('d/m/Y')));
            PDF::AddPage();
            PDF::writeHTML($html, true, false, true, false, '');
            PDF::Output($filename, 'D');
        }
        elseif ($format == 'csv') {
            $currency = currentAbsoluteGas()->currency;
            $headers = [_i('Nome'), _i('Unità di Misura'), _i('Prezzo Unitario (%s)', $currency), _i('Trasporto (%s)', $currency)];
            return output_csv($filename, $headers, $products, function($product) {
                $row = [];
                $row[] = $product->name;
                $row[] = $product->measure->printableName();
                $row[] = printablePrice($product->price, ',');
                $row[] = printablePrice($product->transport, ',');
                return $row;
            });
        }
    }

    public function plainBalance($id)
    {
        $this->ensureAuth(['movements.view' => 'gas', 'movements.admin' => 'gas']);
        $supplier = $this->show($id);
        return $supplier->current_balance_amount;
    }

    public function destroy($id)
    {
        $supplier = DB::transaction(function () use ($id) {
            $supplier = $this->show($id);

            if ($supplier->trashed()) {
                $this->ensureAuth(['supplier.add' => 'gas']);

                foreach($supplier->products as $product)
                    $product->forceDelete();

                $supplier->forceDelete();
            }
            else {
                $this->ensureAuth(['supplier.modify' => $supplier]);
                $supplier->delete();
            }

            return $supplier;
        });

        return $supplier;
    }
}
