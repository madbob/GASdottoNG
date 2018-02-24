<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Log;
use Theme;

use App\Attachment;

class AttachmentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Attachment'
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $target_type = $request->input('target_type');
        $target = $target_type::findOrFail($request->input('target_id'));

        if ($target->attachmentPermissionGranted() == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $a = $target->attachByRequest($request);
        if ($a === false) {
            return $this->errorResponse(_i('File non caricato correttamente'));
        }

        return $this->successResponse([
            'id' => $a->id,
            'name' => $a->name,
            'header' => $a->printableHeader(),
            'url' => route('attachments.show', $a->id),
        ]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $a = Attachment::findOrFail($id);
        if ($a->attached->attachmentPermissionGranted() == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $a = $a->attached->attachByRequest($request, $a->id);
        if ($a === false) {
            return $this->errorResponse(_i('File non caricato correttamente'));
        }

        return $this->successResponse([
            'id' => $a->id,
            'name' => $a->name,
            'header' => $a->printableHeader(),
            'url' => route('attachments.show', $a->id)
        ]);
    }

    public function show($id)
    {
        $a = Attachment::findOrFail($id);

        if ($a->attached->attachmentPermissionGranted()) {
            return Theme::view('attachment.edit', ['attachment' => $a]);
        } else {
            return Theme::view('attachment.show', ['attachment' => $a]);
        }
    }

    public function download($id)
    {
        $a = Attachment::findOrFail($id);
        if (!empty($a->url)) {
            return redirect($a->url);
        } else {
            if (file_exists($a->path)) {
                return response()->download($a->path);
            }
            else {
                Log::error(_i('File non trovato: %s', $a->path));
                return '';
            }
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        $a = Attachment::findOrFail($id);

        /*
            I files autogenerati non possono essere eliminati. Mai
        */
        if ($a->internal || $a->attached->attachmentPermissionGranted() == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $a->delete();

        return $this->successResponse();
    }
}
