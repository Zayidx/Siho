<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\Prakerin;
use App\Models\PembimbingSekolah;
use App\Models\KepalaProgram;
use App\Mail\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Livewire\Features\SupportEvents\Event;

class PengajuanApprovalController extends Controller
{
    public function approve($token)
    {
        $pengajuan = Pengajuan::where('token', $token)->firstOrFail();
        if (!in_array($pengajuan->status_pengajuan, ['diterima_admin', 'menunggu_konfirmasi_perusahaan'])) {
            abort(403, 'Pengajuan tidak valid atau sudah diproses.');
        }
        
        $pengajuan->status_pengajuan = 'diterima_perusahaan';
        $pengajuan->save();
        
        // Buat prakerin otomatis
        $this->createPrakerinFromPengajuan($pengajuan);
        
        // Generate PDF surat penerimaan
        $pdf = Pdf::loadView('pdf.surat-penerimaan', ['pengajuan' => $pengajuan]);
        // Kirim email ke siswa & admin
        Mail::to($pengajuan->siswa->user->email)->send(new SendEmail('Surat Penerimaan Magang', 'emails.surat-penerimaan', ['pengajuan' => $pengajuan], $pdf->output(), 'Surat-Penerimaan.pdf'));
        
        return view('emails.approval-success', ['status' => 'diterima', 'pengajuan' => $pengajuan]);
    }
    
    public function decline($token)
    {
        $pengajuan = Pengajuan::where('token', $token)->firstOrFail();
        if (!in_array($pengajuan->status_pengajuan, ['diterima_admin', 'menunggu_konfirmasi_perusahaan'])) {
            abort(403, 'Pengajuan tidak valid atau sudah diproses.');
        }
        $pengajuan->status_pengajuan = 'ditolak_perusahaan';
        $pengajuan->save();
        // Generate PDF surat penolakan
        $pdf = Pdf::loadView('pdf.surat-penolakan', ['pengajuan' => $pengajuan]);
        // Kirim email ke siswa & admin
        Mail::to($pengajuan->siswa->user->email)->send(new SendEmail('Surat Penolakan Magang', 'emails.surat-penolakan', ['pengajuan' => $pengajuan], $pdf->output(), 'Surat-Penolakan.pdf'));
        return view('emails.approval-success', ['status' => 'ditolak', 'pengajuan' => $pengajuan]);
    }
    
    /**
     * Method untuk membuat prakerin otomatis dari pengajuan yang diterima
     */
    private function createPrakerinFromPengajuan($pengajuan)
    {
        try {
            // Cek apakah sudah ada prakerin aktif untuk siswa ini
            $existingPrakerin = Prakerin::where('nis_siswa', $pengajuan->nis_siswa)
                ->where('status_prakerin', 'aktif')
                ->first();

            if (!$existingPrakerin) {
                // Ambil pembimbing perusahaan dari perusahaan
                $pembimbingPerusahaan = $pengajuan->perusahaan->pembimbingPerusahaan->first();
                
                // Ambil pembimbing sekolah dari perusahaan
                $pembimbingSekolah = $pengajuan->perusahaan->pembimbingSekolah;
                
                // Jika tidak ada pembimbing sekolah, ambil yang pertama
                if (!$pembimbingSekolah) {
                    $pembimbingSekolah = PembimbingSekolah::first();
                    if ($pembimbingSekolah) {
                        $pengajuan->perusahaan->update(['nip_pembimbing_sekolah' => $pembimbingSekolah->nip_pembimbing_sekolah]);
                    }
                }
                
                // Ambil kepala program dari jurusan siswa
                $kepalaProgram = KepalaProgram::where('id_jurusan', $pengajuan->siswa->id_jurusan)->first();
                
                // Pastikan semua data yang diperlukan ada
                if (!$pembimbingSekolah || !$kepalaProgram) {
                    \Log::error('Data pembimbing sekolah atau kepala program tidak ditemukan untuk prakerin', [
                        'nis_siswa' => $pengajuan->nis_siswa,
                        'id_perusahaan' => $pengajuan->id_perusahaan
                    ]);
                    return false;
                }
                
                // Buat prakerin baru
                $prakerin = Prakerin::create([
                    'nis_siswa' => $pengajuan->nis_siswa,
                    'id_perusahaan' => $pengajuan->id_perusahaan,
                    'id_pembimbing_perusahaan' => $pembimbingPerusahaan->id_pembimbing ?? null,
                    'nip_pembimbing_sekolah' => $pembimbingSekolah->nip_pembimbing_sekolah,
                    'nip_kepala_program' => $kepalaProgram->nip_kepala_program,
                    'tanggal_mulai' => $pengajuan->tanggal_mulai ?? now(),
                    'tanggal_selesai' => $pengajuan->tanggal_selesai ?? now()->addMonths(3),
                    'status_prakerin' => 'aktif',
                ]);
                
                \Log::info('Prakerin berhasil dibuat otomatis dari approval email', [
                    'nis_siswa' => $pengajuan->nis_siswa,
                    'id_perusahaan' => $pengajuan->id_perusahaan,
                    'perusahaan' => $pengajuan->perusahaan->nama_perusahaan,
                    'prakerin_id' => $prakerin->id_prakerin
                ]);
                
                // Dispatch event untuk real-time update
                $this->dispatchPrakerinCreatedEvent($prakerin);
                
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            \Log::error('Error saat membuat prakerin otomatis dari approval email', [
                'error' => $e->getMessage(),
                'nis_siswa' => $pengajuan->nis_siswa,
                'id_perusahaan' => $pengajuan->id_perusahaan
            ]);
            return false;
        }
    }
    
    /**
     * Method untuk dispatch event Livewire untuk real-time update
     */
    private function dispatchPrakerinCreatedEvent($prakerin)
    {
        try {
            // Log event untuk tracking
            \Log::info('Prakerin berhasil dibuat otomatis dari approval email', [
                'prakerin_id' => $prakerin->id_prakerin,
                'nis_siswa' => $prakerin->nis_siswa,
                'perusahaan' => $prakerin->perusahaan->nama_perusahaan,
                'status' => $prakerin->status_prakerin,
                'tanggal_mulai' => $prakerin->tanggal_mulai,
                'tanggal_selesai' => $prakerin->tanggal_selesai
            ]);
            
            // Note: Real-time update akan ditangani oleh polling di Livewire components
            // yang sudah diimplementasikan di PrakerinDashboard dan StaffHubin Dashboard
            
        } catch (\Exception $e) {
            \Log::error('Error logging prakerin creation', [
                'error' => $e->getMessage(),
                'prakerin_id' => $prakerin->id_prakerin
            ]);
        }
    }
} 