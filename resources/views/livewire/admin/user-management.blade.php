<div>
    <style>
        .preview-img {
            max-height: 150px;
            width: auto;
            border-radius: 0.5rem;
            border: 1px solid #ddd;
            margin-top: 1rem;
        }

        .table-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }

        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal {
            z-index: 1050 !important;
        }
    </style>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex mb-4 justify-content-between align-items-center flex-wrap">
                <div class="d-flex gap-2 mb-2 mb-md-0">
                    <select wire:model.live="perPage" class="form-select" style="width: auto;">
                        <option value="5">5 per halaman</option>
                        <option value="10">10 per halaman</option>
                        <option value="20">20 per halaman</option>
                        <option value="50">50 per halaman</option>
                    </select>
                    <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                        placeholder="Cari username atau email...">
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.users.export', ['search' => $search]) }}"
                        class="btn btn-outline-secondary">Export CSV</a>
                    <button class="btn btn-primary" wire:click="create">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Pengguna
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-nowrap">No</th>
                            <th>Foto</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th class="text-nowrap">Peran</th>
                            <th class="text-nowrap">Kota/Provinsi</th>
                            <th class="text-nowrap">Gender</th>
                            <th class="text-nowrap">Umur</th>
                            <th class="text-center text-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $index => $user)
                            <tr wire:key="{{ $user->id }}">
                                <td>{{ $users->firstItem() + $index }}</td>
                                <td class="text-center">
                                    <img src="{{ $user->foto_url ?: 'https://placehold.co/100x100/6c757d/white?text=' . strtoupper(substr($user->username, 0, 1)) }}"
                                        alt="{{ $user->username }}" class="table-img">
                                </td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->role)
                                        <span
                                            class="badge {{ $user->role->name == 'superadmin' ? 'bg-light-info' : 'bg-light-success' }}">{{ $user->role->name }}</span>
                                    @else
                                        <span class="badge bg-light-secondary">Tanpa Peran</span>
                                    @endif
                                </td>
                                <td>{{ $user->city }}{{ $user->province ? ', ' . $user->province : '' }}</td>
                                <td>{{ $user->gender ?: '-' }}</td>
                                <td>{{ $user->age !== null ? $user->age . ' th' : '-' }}</td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-warning btn-sm"
                                            wire:click="edit({{ $user->id }})"><i
                                                class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-danger btn-sm"
                                            wire:click="$dispatch('swal:confirm', { id: {{ $user->id }}, method: 'destroy' })"><i
                                                class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Tidak ada data pengguna yang ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $userId ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="store">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                id="username" wire:model="username">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" wire:model="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                    id="full_name" wire:model="full_name">
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">No. Telepon</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" wire:model="phone">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" rows="2"
                                wire:model="address" placeholder="Jalan/No rumah, RT/RW, dst"></textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row" wire:ignore>
                            <div class="col-md-12 mb-2" x-data="{ manualMode: false }">
                                <div class="d-flex align-items-center justify-content-between">
                                    <small class="text-muted">Gunakan dropdown provinsi/kota atau isi manual</small>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="adminManualSwitch2"
                                            x-model="manualMode">
                                        <label class="form-check-label" for="adminManualSwitch2">Isi manual</label>
                                    </div>
                                </div>
                                <div class="row mt-2" x-data="{
                                    provinces: [],
                                    cities: [],
                                    hasError: false,
                                    selectedProvinceId: '',
                                    selectedCityId: '',
                                    async fetchProvinces() {
                                        try {
                                            const r = await fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
                                            if (!r.ok) throw new Error();
                                            this.provinces = await r.json();
                                        } catch (e) { this.hasError = true; }
                                    },
                                    async fetchCities() {
                                        this.cities = [];
                                        this.selectedCityId = '';
                                        if (!this.selectedProvinceId) return;
                                        try {
                                            const r = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${this.selectedProvinceId}.json`);
                                            if (!r.ok) throw new Error();
                                            this.cities = await r.json();
                                        } catch (e) { this.hasError = true; }
                                    },
                                    init() { this.fetchProvinces(); }
                                }">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Provinsi</label>
                                        <template x-if="!manualMode && !hasError">
                                            <select class="form-select @error('province') is-invalid @enderror"
                                                x-model="selectedProvinceId"
                                                @change="$wire.set('province', (provinces.find(p=>p.id==selectedProvinceId)||{}).name || ''); fetchCities();">
                                                <option value="">Pilih Provinsi</option>
                                                <template x-for="p in provinces" :key="p.id">
                                                    <option :value="p.id" x-text="p.name"></option>
                                                </template>
                                            </select>
                                        </template>
                                        <template x-if="manualMode || hasError">
                                            <input type="text"
                                                class="form-control @error('province') is-invalid @enderror"
                                                placeholder="Provinsi"
                                                @input="$wire.set('province', $event.target.value)"
                                                value="{{ $province }}">
                                        </template>
                                        @error('province')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kota / Kabupaten</label>
                                        <template x-if="!manualMode && !hasError">
                                            <select class="form-select @error('city') is-invalid @enderror"
                                                x-model="selectedCityId"
                                                @change="$wire.set('city', (cities.find(c=>c.id==selectedCityId)||{}).name || '')"
                                                :disabled="!selectedProvinceId || cities.length === 0">
                                                <option value="">Pilih Kota / Kabupaten</option>
                                                <template x-for="c in cities" :key="c.id">
                                                    <option :value="c.id" x-text="c.name"></option>
                                                </template>
                                            </select>
                                        </template>
                                        <template x-if="manualMode || hasError">
                                            <input type="text"
                                                class="form-control @error('city') is-invalid @enderror"
                                                placeholder="Kota / Kabupaten"
                                                @input="$wire.set('city', $event.target.value)"
                                                value="{{ $city }}">
                                        </template>
                                        @error('city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="date_of_birth" class="form-label">Tanggal Lahir</label>
                                <input type="date"
                                    class="form-control @error('date_of_birth') is-invalid @enderror"
                                    id="date_of_birth" wire:model="date_of_birth">
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="gender" class="form-label">Jenis Kelamin</label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender"
                                    wire:model="gender">
                                    <option value="">- pilih -</option>
                                    <option value="male">Laki-laki</option>
                                    <option value="female">Perempuan</option>
                                    <option value="other">Lainnya</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="stay_purpose" class="form-label">Tujuan Menginap</label>
                                <input type="text"
                                    class="form-control @error('stay_purpose') is-invalid @enderror"
                                    id="stay_purpose" wire:model="stay_purpose" placeholder="Bisnis / Liburan / dll">
                                @error('stay_purpose')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" wire:model="password"
                                    placeholder="{{ $userId ? 'Kosongkan jika tidak diubah' : '' }}">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    wire:model="password_confirmation">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Peran (Role)</label>
                            <select class="form-select @error('role_id') is-invalid @enderror" id="role_id"
                                wire:model="role_id">
                                <option value="" selected>Pilih Peran</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto Profil @if (!$userId)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <input type="file" accept="image/jpeg,image/png,image/webp"
                                class="form-control @error('foto') is-invalid @enderror" id="foto"
                                wire:model="foto" wire:key="foto-{{ (int) $isModalOpen }}">
                            @error('foto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div wire:loading wire:target="foto" class="text-muted mt-1 small">Mengunggah...</div>
                            @if ($foto)
                                <img src="{{ $foto->temporaryUrl() }}" class="preview-img" alt="Preview">
                            @elseif ($existingFoto)
                                <img src="{{ $existingFoto }}" class="preview-img" alt="Current Photo">
                            @endif
                        </div>
                        <div class="modal-footer pb-0">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="store">Simpan</span>
                                <span wire:loading wire:target="store" class="spinner-border spinner-border-sm"
                                    role="status" aria-hidden="true"></span>
                                <span wire:loading wire:target="store">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
