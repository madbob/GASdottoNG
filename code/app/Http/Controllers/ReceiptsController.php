<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DB;
use PDF;

use App\Receipt;

class ReceiptsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Receipt'
        ]);
    }

    public function show($id)
    {
        $receipt = Receipt::findOrFail($id);

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas)) {
            return view('receipt.edit', ['receipt' => $receipt]);
        }
        else if ($user->can('movements.view', $user->gas)) {
            return view('receipt.show', ['receipt' => $receipt]);
        }
        else {
            abort(503);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $receipt = Receipt::findOrFail($id);
        $receipt->date = decodeDate($request->input('date'));
        $receipt->save();

        return $this->successResponse([
            'id' => $receipt->id,
            'header' => $receipt->printableHeader(),
            'url' => url('receipts/' . $receipt->id),
        ]);
    }

    public function download($id)
    {
        $receipt = Receipt::findOrFail($id);
        $user = Auth::user();

        if ($user->can('movements.admin', $user->gas) || $user->can('movements.view', $user->gas) || $receipt->user_id == $user->id) {
            $html = view('documents.receipt', ['receipt' => $receipt])->render();
            $title = _i('Fattura %s', [$receipt->number]);
            $filename = $title . '.pdf';
            PDF::SetTitle($title);
            PDF::AddPage();
            PDF::writeHTML($html, true, false, true, false, '');
            PDF::Output($filename, 'D');
        }
        else {
            abort(503);
        }
    }
}
