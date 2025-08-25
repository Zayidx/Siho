<?php

namespace App\Console\Commands;

use App\Models\Prakerin;
use Illuminate\Console\Command;

class UpdateExpiredPrakerin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prakerin:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update prakerin yang sudah lewat waktu menjadi selesai';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredPrakerin = Prakerin::where('status_prakerin', 'aktif')
            ->where('tanggal_selesai', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredPrakerin as $prakerin) {
            $prakerin->update(['status_prakerin' => 'selesai']);
            $count++;
        }

        $this->info("Berhasil mengupdate {$count} prakerin yang sudah lewat waktu.");
    }
}
