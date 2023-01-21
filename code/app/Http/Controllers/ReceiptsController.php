<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DB;
use PDF;
use Log;
use Mail;

use App\Services\ReceiptsService;
use App\Notifications\ReceiptForward;
use App\Receipt;

class ReceiptsController extends BackedController
{
    public function __construct(ReceiptsService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Receipt',
			'service' => $service,
        ]);
    }

	public function index(Request $request)
	{
		$past = date('Y-m-d', strtotime('-1 months'));
		$future = date('Y-m-d', strtotime('+10 years'));
		$receipts = $this->service->list($past, $future, 0);
		return view('receipt.index', [
			'receipts' => $receipts,
		]);
	}

    public function show($id)
    {
        $receipt = $this->service->show($id);

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

    private function testAccess($user, $receipt)
    {
        if ($user->can('movements.admin', $user->gas) || $user->can('movements.view', $user->gas) || $receipt->user_id == $user->id) {
            return true;
        }
        else {
            abort(503);
        }
    }

    public function handle(Request $request, $id)
    {
        $receipt = Receipt::findOrFail($id);
        $user = $request->user();

        $this->testAccess($user, $receipt);
        return view('receipt.handle', ['receipt' => $receipt]);
    }

	private function initPdf($receipt)
	{
		$pdf = PDF::loadView('documents.receipt', ['receipt' => $receipt]);
        $title = _i('Fattura %s', [$receipt->number]);
        $filename = sanitizeFilename($title . '.pdf');
		return [$pdf, $filename];
	}

	private function sendByMail($receipt)
	{
		list($pdf, $filename) = $this->initPdf($receipt);
		$temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);
		$pdf->save($temp_file_path);

		$receipt->user->notify(new ReceiptForward($temp_file_path));
		$receipt->mailed = true;
		$receipt->save();
	}

    public function download(Request $request, $id)
    {
        $receipt = Receipt::findOrFail($id);
        $user = $request->user();
        $this->testAccess($user, $receipt);

        $send_mail = $request->has('send_mail');
        if ($send_mail) {
            $this->sendByMail($receipt);
        }
        else {
			list($pdf, $filename) = $this->initPdf($receipt);
            return $pdf->download($filename);
        }
    }

	private function outputCSV($elements)
    {
        $filename = _i('Esportazione ricevute GAS %s.csv', date('d/m/Y'));
        $headers = [_i('Utente'), _i('Data'), _i('Numero'), _i('Imponibile'), _i('IVA')];

        return output_csv($filename, $headers, $elements, function($receipt) {
            return [
				$receipt->user ? $receipt->user->printableName() : '',
				$receipt->date,
				$receipt->number,
				$receipt->total,
				$receipt->total_vat,
			];
        });
    }

	public function search(Request $request)
	{
		$start = decodeDate($request->input('startdate'));
		$end = decodeDate($request->input('enddate'));
		$supplier_id = $request->input('supplier_id');
		$elements = $this->service->list($start, $end, $supplier_id);

		$format = $request->input('format', 'none');

		switch($format) {
			case 'send':
				foreach($elements as $receipt) {
					if ($receipt->mailed == false) {
						try {
							$this->sendByMail($receipt);
						}
						catch(\Exception $e) {
							\Log::error('Errore in inoltro ricevuta: ' . $e->getMessage());
						}
					}
				}

				$elements = $this->service->list($start, $end, $supplier_id);

				/*
					Qui il break manca di proposito
				*/

			case 'none':
				$list_identifier = $request->input('list_identifier', 'receipts-list');
				return view('commons.loadablelist', [
					'identifier' => $list_identifier,
					'items' => $elements,
					'legend' => (object)[
						'class' => 'Receipt',
					],
				]);

			case 'csv':
				return $this->outputCSV($elements);
		}
	}
}
