<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

use App\Receipt;

class ReceiptsService extends BaseService
{
    public function list($start, $end, $supplier_id, $user_id = 0)
    {
        if ($user_id == '0') {
            $this->ensureAuth(['movements.admin' => 'gas']);
        }
        else {
            $user = Auth::user();

            if ($user->id != $user_id) {
                $this->ensureAuth(['movements.admin' => 'gas']);
            }
        }

        $query = Receipt::where('date', '>=', $start)->where('date', '<=', $end)->with(['bookings', 'bookings.user'])->orderBy('date', 'desc');

        if ($supplier_id != '0' || $user_id != 0) {
            $query->whereHas('bookings', function($query) use ($supplier_id, $user_id) {
                if ($user_id != 0) {
                    $query->where('user_id', $user_id);
                }

                if ($supplier_id != '0') {
                    $query->whereHas('order', function($query) use ($supplier_id) {
                        $query->where('supplier_id', $supplier_id);
                    });
                }
            });
        }

        return $query->get();
    }

    public function show($id)
    {
        return Receipt::findOrFail($id);
    }

    public function update($id, array $request)
    {
        $this->ensureAuth(['movements.admin' => 'gas']);
		$receipt = $this->show($id);
		$this->transformAndSetIfSet($receipt, $request, 'date', "decodeDate");
        $receipt->save();
		return $receipt;
    }

    public function destroy($id)
    {
        $receipt = $this->show($id);
        $receipt->delete();
        return $receipt;
    }
}
