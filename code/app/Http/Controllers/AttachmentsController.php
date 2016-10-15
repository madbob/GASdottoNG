<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Theme;

use App\Attachment;

class AttachmentsController extends Controller
{
        public function __construct()
	{
		$this->middleware('auth');
	}

        public function store(Request $request)
        {
                DB::beginTransaction();

                $target_type = $request->input('target_type');
		$target = $target_type::findOrFail($request->input('target_id'));

		if ($target->attachmentPermissionGranted() == false)
			return $this->errorResponse('Non autorizzato');

		$a = $target->attachByRequest($request);
                if ($a === false)
                        return $this->errorResponse('File non caricato correttamente');

		return $this->successResponse([
			'id' => $a->id,
			'name' => $a->name,
			'header' => $a->printableHeader(),
			'url' => url('attachments/' . $a->id)
		]);
        }

        public function show($id)
	{
		$a = Attachment::findOrFail($id);

		if ($a->attached->attachmentPermissionGranted())
			return Theme::view('attachment.edit', ['attachment' => $a]);
		else
			return Theme::view('attachment.show', ['attachment' => $a]);
	}

        public function download($id)
        {
                $a = Attachment::findOrFail($id);
                if (!empty($a->url))
                        return redirect($a->url);
                else
                        return response()->download($a->path);
        }

        public function destroy($id)
        {
                DB::beginTransaction();

		$a = Attachment::findOrFail($id);

		if ($a->attached->attachmentPermissionGranted() == false)
			return $this->errorResponse('Non autorizzato');

		$a->delete();
		return $this->successResponse();
        }
}
