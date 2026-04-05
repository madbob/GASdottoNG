<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


use App\Supplier;

class ExportSupplier extends Command
{
    protected $signature = 'gdxp:write:supplier {supplier_id}';

    protected $description = 'Genera il file GDXP per un dato fornitore';

    public function handle()
    {
        $id = $this->argument('supplier_id');
        $obj = Supplier::findOrFail($id);

        $working_dir = sys_get_temp_dir();
        $json = $obj->exportJSON();
        $archivepath = sprintf('%s/%s.json', $working_dir, str_replace('/', '_', $obj->printableName()));
        file_put_contents($archivepath, $json);

        $this->info('File creato in ' . $archivepath);
    }
}
