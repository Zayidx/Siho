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

            <div class="mb-3 form-group position-relative has-icon-left">
                <input required type="text" wire:model.blur='full_name' class="form-control form-control-xl @error('full_name') is-invalid @enderror" placeholder="Nama Lengkap">
                <div class="form-control-icon"><i class="bi bi-person"></i></div>
                @error('full_name')<div class="invalid-feedback"><i class="bx bx-radio-circle"></i> {{ $message }}</div>@enderror
            </div>

            <div class="mb-3 form-group position-relative has-icon-left">
                <input required type="text" wire:model.blur='username' class="form-control form-control-xl @error('username') is-invalid @enderror" placeholder="Nama Pengguna">
                <div class="form-control-icon"><i class="bi bi-at"></i></div>
                @error('username')<div class="invalid-feedback"><i class="bx bx-radio-circle"></i> {{ $message }}</div>@enderror
            </div>

            <div class="mb-3 form-group position-relative has-icon-left">
                <input required type="email" wire:model.blur='email' class="form-control form-control-xl @error('email') is-invalid @enderror" placeholder="Email">
                <div class="form-control-icon"><i class="bi bi-envelope"></i></div>
                @error('email')<div class="invalid-feedback"><i class="bx bx-radio-circle"></i> {{ $message }}</div>@enderror
            </div>

            <div class="mb-3 form-group position-relative has-icon-left">
                <input required type="text" wire:model.blur='phone' class="form-control form-control-xl @error('phone') is-invalid @enderror" placeholder="Nomor Telepon">
                <div class="form-control-icon"><i class="bi bi-telephone"></i></div>
                @error('phone')<div class="invalid-feedback"><i class="bx bx-radio-circle"></i> {{ $message }}</div>@enderror
            </div>

            <!-- Alamat: Provinsi, Kota, Jalan, RT/RW -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Alamat</label>
                <div class="row g-3">
                    <div class="col-md-6" wire:ignore>
                        <div x-data="{
                                provinces: [],
                                cities: [],
                                hasError: false,
                                manualMode: false,
                                selectedProvinceId: @js($province_id ?? ''),
                                selectedCityId: @js($city_id ?? ''),
                                async fetchProvinces(){
                                    try {
                                        const res = await fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
                                        if(!res.ok) throw new Error('failed');
                                        this.provinces = await res.json();
                                    } catch (e) { console.error('Gagal memuat provinsi', e); this.hasError = true; }
                                },
                                async fetchCities(){
                                    this.cities = [];
                                    this.selectedCityId = '';
                                    if(!this.selectedProvinceId) return;
                                    try {
                                        const res = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${this.selectedProvinceId}.json`);
                                        if(!res.ok) throw new Error('failed');
                                        this.cities = await res.json();
                                    } catch (e) { console.error('Gagal memuat kota/kabupaten', e); this.hasError = true; }
                                },
                                init(){
                                    this.fetchProvinces().then(() => {
                                        if(this.selectedProvinceId){ this.fetchCities(); }
                                    });
                                }
                            }" x-init="init()">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <small class="text-muted" x-show="hasError">Mode offline aktif: isian manual</small>
                                <div class="form-check form-switch ms-auto">
                                    <input class="form-check-input" type="checkbox" id="manualSwitch" x-model="manualMode">
                                    <label class="form-check-label" for="manualSwitch">Isi manual</label>
                                </div>
                            </div>

                            <template x-if="!manualMode && !hasError">
                                <div>
                                    <div class="form-group position-relative has-icon-left">
                                        <select class="form-select form-select-xl @error('province') is-invalid @enderror" x-model="selectedProvinceId"
                                            @change="$wire.set('province_id', selectedProvinceId); const p = provinces.find(pr => pr.id == selectedProvinceId); $wire.set('province', p ? p.name : ''); fetchCities();">
                                            <option value="">Pilih Provinsi</option>
                                            <template x-for="p in provinces" :key="p.id">
                                                <option :value="p.id" x-text="p.name"></option>
                                            </template>
                                        </select>
                                        <div class="form-control-icon"><i class="bi bi-geo"></i></div>
                                        @error('province')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group position-relative has-icon-left mt-3">
                                        <select class="form-select form-select-xl @error('city') is-invalid @enderror" x-model="selectedCityId"
                                            @change="$wire.set('city_id', selectedCityId); const c = cities.find(ct => ct.id == selectedCityId); $wire.set('city', c ? c.name : '');" :disabled="!selectedProvinceId || cities.length===0">
                                            <option value="">Pilih Kota / Kabupaten</option>
                                            <template x-for="c in cities" :key="c.id">
                                                <option :value="c.id" x-text="c.name"></option>
                                            </template>
                                        </select>
                                        <div class="form-control-icon"><i class="bi bi-building"></i></div>
                                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </template>

                            <template x-if="manualMode || hasError">
                                <div>
                                    <div class="form-group position-relative has-icon-left mb-3">
                                        <input type="text" class="form-control form-control-xl @error('province') is-invalid @enderror" placeholder="Provinsi" @input="$wire.set('province', $event.target.value)">
                                        <div class="form-control-icon"><i class="bi bi-geo"></i></div>
                                        @error('province')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" class="form-control form-control-xl @error('city') is-invalid @enderror" placeholder="Kota / Kabupaten" @input="$wire.set('city', $event.target.value)">
                                        <div class="form-control-icon"><i class="bi bi-building"></i></div>
                                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group position-relative has-icon-left mb-3">
                            <input required type="text" wire:model.blur='street' class="form-control form-control-xl @error('street') is-invalid @enderror" placeholder="Nama Jalan / No. Rumah">
                            <div class="form-control-icon"><i class="bi bi-signpost-2"></i></div>
                            @error('street')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="form-group position-relative has-icon-left">
                                    <input type="text" wire:model.blur='rt' class="form-control form-control-xl @error('rt') is-invalid @enderror" placeholder="RT">
                                    <div class="form-control-icon"><i class="bi bi-123"></i></div>
                                    @error('rt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group position-relative has-icon-left">
                                    <input type="text" wire:model.blur='rw' class="form-control form-control-xl @error('rw') is-invalid @enderror" placeholder="RW">
                                    <div class="form-control-icon"><i class="bi bi-123"></i></div>
                                    @error('rw')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                <label for="foto" class="form-label">Foto Profil (Opsional)</label>
                <input type="file" class="form-control @error('foto') is-invalid @enderror" id="foto" wire:model="foto" accept="image/*">
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
