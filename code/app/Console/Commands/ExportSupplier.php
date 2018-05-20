<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use ezcArchive;

use App\Supplier;

class ExportSupplier extends Command
{
    protected $signature = 'gdxp:write:supplier {supplier_id}';
    protected $description = 'Genera il file GDXP per un dato fornitore';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $id = $this->argument('supplier_id');
        $obj = Supplier::findOrFail($id);
        $xml = $obj->exportXML();

        $working_dir = sys_get_temp_dir();
        chdir($working_dir);
        $filename = md5($xml);
        file_put_contents($filename, $xml);

        $archivepath = sprintf('%s/%s.gdxp', $working_dir, str_replace('/', '_', $obj->printableName()));
        $archive = ezcArchive::open('compress.zlib://' . $archivepath, ezcArchive::TAR_USTAR);
        $archive->append([$filename], '');
        unlink($filename);

        echo "File creato in " . $archivepath . "\n";
    }
}
