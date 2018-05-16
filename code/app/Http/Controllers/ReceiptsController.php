<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
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

        if ($user->can('movements.admin', $user->gas) || $user->can('movements.view', $user->gas) || $receipt->user_id == $user->id) {
            return view('receipt.show', ['receipt' => $receipt]);
        }
        else {
            abort(503);
        }
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
