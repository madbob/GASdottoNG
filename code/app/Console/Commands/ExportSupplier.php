<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use ezcArchive;

use App\Supplier;

class ExportSupplier extends Command
{
    protected $signature = 'gdxp:write:supplier {supplier_id} {format}';
    protected $description = 'Genera il file GDXP per un dato fornitore';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $id = $this->argument('supplier_id');
        $format = $this->argument('format');

        $obj = Supplier::findOrFail($id);
        $working_dir = sys_get_temp_dir();

        if ($format == 'xml') {
            $xml = $obj->exportXML();
            chdir($working_dir);
            $filename = md5($xml);
            file_put_contents($filename, $xml);

            $archivepath = sprintf('%s/%s.gdxp', $working_dir, str_replace('/', '_', $obj->printableName()));
            zipAll($archivepath, [$filename]);
        }
        else {
            $json = $obj->exportJSON();
            $archivepath = sprintf('%s/%s.json', $working_dir, str_replace('/', '_', $obj->printableName()));
            file_put_contents($archivepath, $json);
        }

        $this->info("File creato in " . $archivepath);
    }
}
