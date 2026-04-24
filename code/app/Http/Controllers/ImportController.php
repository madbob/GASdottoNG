<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

use App\Exceptions\MissingFieldException;
use App\Importers\CSV\CSVImporter;
use App\Importers\GDXP\Suppliers;

class ImportController extends Controller
{
    public function esModal()
    {
        $entries = App::make('RemoteRepository')->getList();

        return view('import.esmodal', ['entries' => $entries]);
    }

    public function postCsv(Request $request)
    {
        $type = $request->input('type');
        $step = $request->input('step', 'guess');
        $request = $request->all();

        $importer = CSVImporter::getImporter($type);

        if ($importer->testAccess($request) === false) {
            return $this->errorResponse(__('texts.generic.unauthorized'));
        }

        $return = null;

        try {
            switch ($step) {
                case 'guess':
                    $parameters = $importer->guess($request);
                    $return = view('import.csvsortcolumns', $parameters);
                    break;

                case 'select':
                    try {
                        $parameters = $importer->select($request);
                        $return = $importer->formatSelect($parameters);
                    }
                    catch (MissingFieldException $e) {
                        $return = view('import.csvimportfinal', [
                            'title' => __('texts.imports.help.failure_notice'),
                            'objects' => [],
                            'errors' => [$e->getMessage()],
                        ]);
                    }

                    break;

                case 'run':
                    $parameters = $importer->run($request);
                    $return = view($importer->finalTemplate(), $parameters);
                    break;

                default:
                    throw new \InvalidArgumentException('Passaggio non previsto in fase di importazione: ' . $step);
                    break;
            }
        }
        catch (\Exception $e) {
            $return = $this->errorResponse($e->getMessage());
        }

        return $return;
    }

    public function getGdxp(Request $request)
    {
        $classname = $request->input('classname');
        $id = $request->input('id');
        $obj = $classname::findOrFail($id);

        $working_dir = sys_get_temp_dir();
        $json = $obj->exportJSON();
        $downloadable = sprintf('%s/%s.json', $working_dir, str_replace('/', '_', $obj->printableName()));
        file_put_contents($downloadable, $json);

        return response()->download($downloadable)->deleteFileAfterSend(true);
    }

    private function readGdxpFile($path, $execute, $supplier_replace)
    {
        $data = [];

        $info = json_decode(file_get_contents($path));
        foreach ($info->blocks as $c) {
            if ($execute) {
                $data[] = Suppliers::importJSON($info, $c->supplier, $supplier_replace);
            }
            else {
                $data[] = Suppliers::readJSON($c->supplier);
            }
        }

        return $data;
    }

    public function postGdxp(Request $request)
    {
        $archivepath = '';
        $working_dir = sys_get_temp_dir();
        $step = $request->input('step', 'read');

        if ($step == 'read') {
            $file = $request->file('file');
            if (is_null($file) || $file->isValid() === false) {
                $url = $request->input('url');
                $file = file_get_contents($url);
                $archivepath = tempnam($working_dir, 'gdxp_remote_file');
                file_put_contents($archivepath, $file);
            }
            else {
                $filename = basename(tempnam($working_dir, 'import_gdxp_'));
                $file->move($working_dir, $filename);
                $archivepath = sprintf('%s/%s', $working_dir, $filename);
            }

            $data = $this->readGdxpFile($archivepath, false, null);

            return view('import.gdxpsummary', ['data' => $data, 'path' => $archivepath]);
        }
        elseif ($step == 'run') {
            DB::beginTransaction();

            $archivepath = $request->input('path');
            if ($request->input('supplier_source') == 'new') {
                $data = $this->readGdxpFile($archivepath, true, null);
            }
            else {
                $data = $this->readGdxpFile($archivepath, true, $request->input('supplier_update'));
            }

            unlink($archivepath);
            DB::commit();

            return view('import.gdxpfinal', ['data' => $data]);
        }
    }
}
