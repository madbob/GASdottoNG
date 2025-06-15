<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Log;
use App;

use ezcArchive;
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

        $importer = CSVImporter::getImporter($type);

        if ($importer->testAccess($request) === false) {
            return $this->errorResponse(__('texts.generic.unauthorized'));
        }

        try {
            switch ($step) {
                case 'guess':
                    $parameters = $importer->guess($request);

                    return view('import.csvsortcolumns', $parameters);

                case 'select':
                    try {
                        $parameters = $importer->select($request);

                        return $importer->formatSelect($parameters);
                    }
                    catch (MissingFieldException $e) {
                        return view('import.csvimportfinal', [
                            'title' => __('texts.imports.help.failure_notice'),
                            'objects' => [],
                            'errors' => [$e->getMessage()],
                        ]);
                    }

                case 'run':
                    $parameters = $importer->run($request);

                    return view($importer->finalTemplate(), $parameters);

                default:
                    throw new \InvalidArgumentException('Passaggio non previsto in fase di importazione: ' . $step);
                    break;
            }
        }
        catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->errorResponse(__('texts.imports.help.invalid_command', [
            'type' => $type,
            'step' => $step,
        ]));
    }

    public function getGdxp(Request $request)
    {
        $classname = $request->input('classname');
        $id = $request->input('id');
        $obj = $classname::findOrFail($id);

        $working_dir = sys_get_temp_dir();

        switch ($request->input('format', 'json')) {
            case 'xml':
                $xml = $obj->exportXML();

                chdir($working_dir);
                $filename = md5($xml);
                file_put_contents($filename, $xml);

                $downloadable = sprintf('%s/%s.gdxp', $working_dir, str_replace('/', '_', $obj->printableName()));
                $archive = ezcArchive::open('compress.zlib://' . $downloadable, ezcArchive::TAR_USTAR);
                $archive->append([$filename], '');
                unlink($filename);
                break;

            case 'json':
            default:
                $json = $obj->exportJSON();
                $downloadable = sprintf('%s/%s.json', $working_dir, str_replace('/', '_', $obj->printableName()));
                file_put_contents($downloadable, $json);
                break;
        }

        return response()->download($downloadable)->deleteFileAfterSend(true);
    }

    private function readGdxpFile($path, $execute, $supplier_replace)
    {
        $working_dir = sys_get_temp_dir();

        $data = [];
        $type = mime_content_type($path);

        if (in_array($type, ['text/plain', 'application/json'])) {
            $info = json_decode(file_get_contents($path));
            foreach ($info->blocks as $c) {
                if ($execute) {
                    $data[] = Suppliers::importJSON($info, $c->supplier, $supplier_replace);
                }
                else {
                    $data[] = Suppliers::readJSON($c->supplier);
                }
            }
        }
        else {
            $archive = ezcArchive::open('compress.zlib://' . $path);
            while ($archive->valid()) {
                $entry = $archive->current();
                $archive->extractCurrent($working_dir);
                $filepath = sprintf('%s/%s', $working_dir, $entry->getPath());
                $contents = file_get_contents($filepath);
                $contents = simplexml_load_string($contents);

                foreach ($contents->children() as $c) {
                    if ($execute) {
                        $data[] = Suppliers::importXML($c, $supplier_replace);
                    }
                    else {
                        $data[] = Suppliers::readXML($c);
                    }
                }

                unlink($filepath);
                $archive->next();
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
