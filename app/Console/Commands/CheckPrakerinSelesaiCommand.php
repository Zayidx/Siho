<?php

namespace App\Console\Commands;

use App\Models\Prakerin;
use App\Events\PrakerinSelesaiEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPrakerinSelesaiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prakerin:check-selesai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check prakerin dengan status selesai dan kirim email penilaian';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Memulai pengecekan prakerin selesai...');

        try {
            // Ambil prakerin dengan status selesai yang belum dikirim email
            $prakerinSelesai = Prakerin::with(['siswa', 'perusahaan', 'pembimbingPerusahaan'])
                ->where('status_prakerin', 'selesai')
                ->whereDoesntHave('pembimbingPerusahaan.penilaian', function($query) {
                    $query->whereColumn('penilaian.nis_siswa', 'prakerin.nis_siswa');
                })
                ->get();

            $this->info("📊 Ditemukan {$prakerinSelesai->count()} prakerin selesai yang belum dinilai");

            $emailSent = 0;
            $errors = 0;

            foreach ($prakerinSelesai as $prakerin) {
                try {
                    $this->info("📧 Memproses prakerin ID: {$prakerin->id_prakerin} - Siswa: {$prakerin->siswa->nama_siswa}");

                    // Dispatch event untuk mengirim email
                    event(new PrakerinSelesaiEvent($prakerin));

                    $emailSent++;
                    $this->info("✅ Email berhasil dikirim untuk prakerin ID: {$prakerin->id_prakerin}");

                    // Delay kecil untuk menghindari spam
                    sleep(1);

                } catch (\Exception $e) {
                    $errors++;
                    $this->error("❌ Error memproses prakerin ID: {$prakerin->id_prakerin} - {$e->getMessage()}");
                    
                    Log::error('Error dalam CheckPrakerinSelesaiCommand', [
                        'prakerin_id' => $prakerin->id_prakerin,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->info("🎉 Pengecekan selesai!");
            $this->info("📧 Email berhasil dikirim: {$emailSent}");
            $this->info("❌ Error: {$errors}");

            Log::info('CheckPrakerinSelesaiCommand completed', [
                'total_checked' => $prakerinSelesai->count(),
                'email_sent' => $emailSent,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            $this->error("❌ Error dalam pengecekan: {$e->getMessage()}");
            
            Log::error('Error dalam CheckPrakerinSelesaiCommand', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
} 