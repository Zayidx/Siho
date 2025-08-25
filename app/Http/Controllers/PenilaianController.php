<?php

namespace App\Http\Controllers;

use App\Models\Prakerin;
use App\Models\Siswa;
use App\Models\Perusahaan;
use App\Models\PembimbingPerusahaan;
use App\Models\Kompetensi;
use App\Models\Penilaian;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PenilaianController extends Controller
{
    /**
     * Tampilkan form penilaian berdasarkan token
     */
    public function showForm($token)
    {
        // Validasi token
        $tokenData = Cache::get("penilaian_token_{$token}");
        
        if (!$tokenData) {
            return view('penilaian.token-invalid');
        }
        
        // Cek apakah token sudah expired
        if (now()->isAfter($tokenData['expires_at'])) {
            Cache::forget("penilaian_token_{$token}");
            return view('penilaian.token-expired');
        }
        
        // Ambil data yang diperlukan
        $prakerin = Prakerin::with(['siswa.kelas', 'siswa.jurusan', 'perusahaan', 'pembimbingPerusahaan'])
            ->where('id_prakerin', $tokenData['prakerin_id'])
            ->first();
            
        if (!$prakerin) {
            return view('penilaian.data-not-found');
        }
        
        // Cek apakah sudah ada penilaian
        $existingPenilaian = Penilaian::where('nis_siswa', $tokenData['nis_siswa'])
            ->where('id_pemb_perusahaan', $tokenData['pembimbing_id'])
            ->first();
            
        if ($existingPenilaian) {
            return view('penilaian.already-rated', [
                'prakerin' => $prakerin,
                'penilaian' => $existingPenilaian
            ]);
        }
        
        // Cek apakah form sudah disubmit sebelumnya (session flag)
        if (session('penilaian_submitted_' . $token)) {
            return view('penilaian.already-rated', [
                'prakerin' => $prakerin,
                'penilaian' => null,
                'message' => 'Form penilaian sudah disubmit sebelumnya. Silakan refresh halaman jika ingin mengirim ulang.'
            ]);
        }
        
        // Ambil kompetensi berdasarkan jurusan siswa
        $kompetensi = Kompetensi::where('id_jurusan', $prakerin->siswa->id_jurusan)->get();
        
        return view('penilaian.form', [
            'prakerin' => $prakerin,
            'kompetensi' => $kompetensi,
            'token' => $token
        ]);
    }
    
    /**
     * Proses submit form penilaian
     */
    public function submitPenilaian(Request $request, $token)
    {
        // Validasi token
        $tokenData = Cache::get("penilaian_token_{$token}");
        
        if (!$tokenData) {
            return redirect()->back()->with('error', 'Token tidak valid atau sudah expired.');
        }
        
        // Cek apakah token sudah expired
        if (now()->isAfter($tokenData['expires_at'])) {
            Cache::forget("penilaian_token_{$token}");
            return redirect()->back()->with('error', 'Token sudah expired.');
        }
        
        // Cek apakah sudah ada penilaian
        $existingPenilaian = Penilaian::where('nis_siswa', $tokenData['nis_siswa'])
            ->where('id_pemb_perusahaan', $tokenData['pembimbing_id'])
            ->first();
            
        if ($existingPenilaian) {
            return redirect()->back()->with('error', 'Penilaian sudah dilakukan sebelumnya.');
        }
        
        // Validasi input
        $request->validate([
            'nilai.*' => 'required|integer|min:0|max:100',
            'komentar' => 'nullable|string|max:1000',
            'submitted' => 'required|in:0,1'
        ]);
        
        // Cek apakah form sudah disubmit sebelumnya
        if ($request->submitted == '1') {
            return redirect()->back()->with('error', 'Form sudah disubmit sebelumnya. Silakan refresh halaman jika ingin mengirim ulang.');
        }
        
        try {
            // Buat record penilaian
            $penilaian = Penilaian::create([
                'nis_siswa' => $tokenData['nis_siswa'],
                'id_pemb_perusahaan' => $tokenData['pembimbing_id'],
            ]);
            
            // Simpan nilai kompetensi
            foreach ($request->nilai as $kompetensiId => $nilai) {
                Nilai::create([
                    'id_penilaian' => $penilaian->id_penilaian,
                    'id_kompetensi' => $kompetensiId,
                    'nilai' => $nilai
                ]);
            }
            
            // Hapus token setelah berhasil
            Cache::forget("penilaian_token_{$token}");
            
            // Set session flag untuk mencegah akses ulang ke form
            session(['penilaian_submitted_' . $token => true]);
            
            Log::info('Penilaian berhasil disimpan', [
                'penilaian_id' => $penilaian->id_penilaian,
                'nis_siswa' => $tokenData['nis_siswa'],
                'pembimbing_id' => $tokenData['pembimbing_id'],
                'token' => $token
            ]);
            
            // Redirect dengan pesan sukses untuk SweetAlert
            return redirect()->route('penilaian.success', ['token' => $token])->with('success', 'Penilaian berhasil disimpan!');
            
        } catch (\Exception $e) {
            Log::error('Error menyimpan penilaian', [
                'error' => $e->getMessage(),
                'token' => $token,
                'data' => $request->all()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan penilaian. Silakan coba lagi.');
        }
    }

    /**
     * Tampilkan halaman sukses penilaian
     */
    public function showSuccess($token)
    {
        // Cari penilaian terbaru yang mungkin baru saja dibuat
        $penilaian = Penilaian::with(['siswa.kelas.jurusan', 'pembimbingPerusahaan.perusahaan'])
            ->orderBy('id_penilaian', 'desc')
            ->first();
            
        if (!$penilaian) {
            return view('penilaian.success', [
                'penilaian' => null,
                'prakerin' => null,
                'showSuccessAlert' => true
            ]);
        }
        
        // Ambil data prakerin berdasarkan siswa
        $prakerin = Prakerin::with(['siswa', 'perusahaan'])
            ->where('nis_siswa', $penilaian->nis_siswa)
            ->orderBy('id_prakerin', 'desc')
            ->first();
        
        return view('penilaian.success', [
            'penilaian' => $penilaian,
            'prakerin' => $prakerin,
            'showSuccessAlert' => true
        ]);
    }
} 