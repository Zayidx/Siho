<!-- File: resources/views/livewire/auth/register.blade.php -->
<div id="auth-left">
    <a href="#" class="mb-4 logo">
        <i class="fas fa-graduation-cap"></i> InfoPKL
    </a>
    
    @if (!$showOtpForm)
        {{-- TAMPILAN FORM REGISTRASI USERS --}}
        <h1 class="mt-5 auth-title">Buat Akun Pengguna</h1>
        <p class="mb-5 auth-subtitle pe-5">Isi data berikut untuk mendaftar sebagai pengguna.</p>
        
        <form wire:submit='submitRegistrationDetails' novalidate>
            @if ($errors->has('credentials'))
                <div class="mb-3 alert alert-danger">{{ $errors->first('credentials') }}</div>
            @endif

            <div class="mb-4 form-group position-relative has-icon-left">
                <input required type="text" wire:model.blur='username' class="form-control form-control-xl @error('username') is-invalid @enderror" placeholder="Nama Lengkap">
                <div class="form-control-icon"><i class="bi bi-person"></i></div>
                @error('username')<div class="invalid-feedback"><i class="bx bx-radio-circle"></i> {{ $message }}</div>@enderror
            </div>

            <div class="mb-4 form-group position-relative has-icon-left">
                <input required type="email" wire:model.blur='email' class="form-control form-control-xl @error('email') is-invalid @enderror" placeholder="Email">
                <div class="form-control-icon"><i class="bi bi-envelope"></i></div>
                @error('email')<div class="invalid-feedback"><i class="bx bx-radio-circle"></i> {{ $message }}</div>@enderror
            </div>


            <div class="mb-4 form-group position-relative has-icon-left">
                <input required type="password" wire:model.blur='password' class="form-control form-control-xl @error('password') is-invalid @enderror" placeholder="Password">
                <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
                @error('password')<div class="invalid-feedback"><i class="bx bx-radio-circle"></i> {{ $message }}</div>@enderror
            </div>

            <div class="mb-4 form-group position-relative has-icon-left">
                <input required type="password" wire:model.blur='password_confirmation' class="form-control form-control-xl" placeholder="Konfirmasi Password">
                <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
            </div>

            <div class="mb-3 form-group">
                <label for="foto" class="form-label">Foto Profil (Wajib)</label>
                <input type="file" class="form-control @error('foto') is-invalid @enderror" id="foto" wire:model="foto">
                @error('foto') <div class="invalid-feedback"><i class="bx bx-radio-circle"></i> {{ $message }}</div> @enderror
                <div wire:loading wire:target="foto" class="text-muted mt-1 small">Mengunggah...</div>
                @if ($foto)
                    <img src="{{ $foto->temporaryUrl() }}" class="img-thumbnail mt-2" style="max-height: 150px;" alt="Preview">
                @endif
            </div>

            <button type="submit" class="mt-3 shadow-lg btn btn-primary btn-block btn-lg" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target='submitRegistrationDetails'>Daftar</span> 
                <span wire:loading wire:target='submitRegistrationDetails' class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </button>
            
            <p class="m-0 mt-5 text-xl text-center text-dark">Sudah punya akun? <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-underline" wire:navigate>Login di sini</a></p> 
        </form>

    @else
        {{-- TAMPILAN FORM VERIFIKASI OTP --}}
        <h1 class="mt-5 auth-title">Verifikasi Email Anda</h1>
        <p class="mb-5 auth-subtitle pe-5">Kami telah mengirimkan kode OTP ke email <strong>{{ $email }}</strong>. Satu langkah lagi!</p>

        @if (config('app.debug') && $devOtp)
            <div class="alert alert-info">
                Mode pengembangan aktif. Kode OTP: <strong>{{ $devOtp }}</strong>
            </div>
        @endif

        @if ($mailSent)
            <div class="alert alert-success">Email OTP berhasil dikirim ke <strong>{{ $email }}</strong>.</div>
        @endif
        @if ($errors->has('credentials'))
            <div class="alert alert-danger">{{ $errors->first('credentials') }}</div>
        @endif
        
        <!-- FIX: Logika Alpine.js diperbaiki agar tidak konflik dengan Livewire -->
        <div x-data="{
                countdown: 300,
                timer: null,
                startTimer() {
                    this.countdown = 300;
                    clearInterval(this.timer); // Hapus timer lama jika ada
                    this.timer = setInterval(() => {
                        if (this.countdown > 0) {
                            this.countdown--;
                        } else {
                            clearInterval(this.timer);
                        }
                    }, 1000);
                },
                get timerRunning() {
                    return this.countdown > 0;
                },
                get formattedTime() {
                    const minutes = Math.floor(this.countdown / 60);
                    const seconds = this.countdown % 60;
                    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }
            }"
             x-init="startTimer()"
             @otp-sent.window="startTimer()">
            
            <form wire:submit='verifyOtpAndCreateUser'>
                @if ($errors->has('otp'))
                    <div class="mb-3 alert alert-danger">{{ $errors->first('otp') }}</div>
                @endif
                @if ($errors->has('credentials'))
                    <div class="mb-3 alert alert-danger">{{ $errors->first('credentials') }}</div>
                @endif

                <div class="mb-4 form-group position-relative has-icon-left">
                    <input required type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" wire:model.blur='otp' class="form-control form-control-xl @error('otp') is-invalid @enderror" placeholder="Masukkan 6 digit OTP">
                    <div class="form-control-icon"><i class="bi bi-key"></i></div>
                    @error('otp')<div class="invalid-feedback"><i class="bx bx-radio-circle"></i> {{ $message }}</div>@enderror
                </div>

                <button type="submit" class="mt-3 shadow-lg btn btn-primary btn-block btn-lg" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target='verifyOtpAndCreateUser'>Verifikasi & Buat Akun</span> 
                    <span wire:loading wire:target='verifyOtpAndCreateUser' class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </button>
            </form>

            <div class="mt-4 text-center">
                <template x-if="timerRunning">
                    <p class="text-muted">Kirim ulang kode dalam <span x-text="formattedTime" class="fw-bold"></span></p>
                </template>
                <template x-if="!timerRunning">
                    <p class="text-muted">Tidak menerima kode? <a href="#" wire:click.prevent="resendOtp" class="font-bold">Kirim Ulang</a></p>
                </template>
                <a href="#" wire:click.prevent="cancelOtp" class="text-muted d-block mt-2">Salah data? Kembali</a>
            </div>
        </div>
    @endif
</div>
