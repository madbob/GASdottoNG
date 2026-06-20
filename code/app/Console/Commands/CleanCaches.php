<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;

class CleanCaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:caches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /*
            Qui vengono rimossi gli avatar generati più di un mese fa da
            DispatchPictures::generateAvatar().
            Questa operazione serve ad evitare di accumulare immagini ad
            oltranza, incluse quelle non più usate da nessuno
        */
        $threshold = Carbon::now()->subDays(30)->getTimestamp();
        $disk = Storage::disk('avatars');

        $avatars = $disk->files('/');
        foreach($avatars as $avatar) {
            $time = $disk->lastModified($avatar);
            if ($time < $threshold) {
                @unlink($avatar);
            }
        }
    }
}
