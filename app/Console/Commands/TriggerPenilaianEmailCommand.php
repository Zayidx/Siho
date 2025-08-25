<?php

namespace App\Console\Commands;

use App\Models\Prakerin;
use App\Events\PrakerinSelesaiEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TriggerPenilaianEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prakerin:trigger-email {prakerin_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger email penilaian untuk prakerin tertentu atau semua prakerin selesai';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $prakerinId = $this->argument('prakerin_id');

        if ($prakerinId) {
            // Trigger untuk prakerin tertentu
            $prakerin = Prakerin::with(['siswa', 'perusahaan', 'pembimbingPerusahaan'])
                ->find($prakerinId);

            if (!$prakerin) {
                $this->error("❌ Prakerin dengan ID {$prakerinId} tidak ditemukan");
                return;
            }

            if ($prakerin->status_prakerin !== 'selesai') {
                $this->error("❌ Prakerin ID {$prakerinId} status bukan 'selesai'");
                return;
            }

            $this->info("📧 Trigger email untuk prakerin ID: {$prakerin->id_prakerin} - Siswa: {$prakerin->siswa->nama_siswa}");
            event(new PrakerinSelesaiEvent($prakerin));
            $this->info("✅ Event dispatched untuk prakerin ID: {$prakerin->id_prakerin}");

        } else {
            // Trigger untuk semua prakerin selesai yang belum dikirim email
            $prakerinSelesai = Prakerin::with(['siswa', 'perusahaan', 'pembimbingPerusahaan'])
                ->where('status_prakerin', 'selesai')
                ->whereDoesntHave('pembimbingPerusahaan.penilaian', function($query) {
                    $query->whereColumn('penilaian.nis_siswa', 'prakerin.nis_siswa');
                })
                ->get();

            $this->info("🔍 Ditemukan {$prakerinSelesai->count()} prakerin selesai yang belum dikirim email");

            $emailSent = 0;
            $errors = 0;

            foreach ($prakerinSelesai as $prakerin) {
                try {
                    $this->info("📧 Trigger email untuk prakerin ID: {$prakerin->id_prakerin} - Siswa: {$prakerin->siswa->nama_siswa}");
                    event(new PrakerinSelesaiEvent($prakerin));
                    $emailSent++;
                    $this->info("✅ Event dispatched untuk prakerin ID: {$prakerin->id_prakerin}");

                    // Delay kecil untuk menghindari spam
                    sleep(1);

                } catch (\Exception $e) {
                    $errors++;
                    $this->error("❌ Error memproses prakerin ID: {$prakerin->id_prakerin} - {$e->getMessage()}");
                    
                    Log::error('Error dalam TriggerPenilaianEmailCommand', [
                        'prakerin_id' => $prakerin->id_prakerin,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->info("🎉 Trigger selesai!");
            $this->info("📧 Event dispatched: {$emailSent}");
            $this->info("❌ Error: {$errors}");
        }
    }
} 