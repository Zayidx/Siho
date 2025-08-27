<div>
    <style>
        .preview-img { max-height: 150px; width: auto; border-radius: 0.5rem; border: 1px solid #ddd; margin-top: 1rem; }
        .table-img { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
        .modal-backdrop { z-index: 1040 !important; }
        .modal { z-index: 1050 !important; }
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
                    <input type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari username atau email...">
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.users.export', ['search' => $search]) }}" class="btn btn-outline-secondary">Export CSV</a>
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
                            <th class="text-center text-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $index => $user)
                            <tr wire:key="{{ $user->id }}">
                                <td>{{ $users->firstItem() + $index }}</td>
                                <td class="text-center">
                                    <img src="{{ $user->foto ? Storage::url($user->foto) : 'https://placehold.co/100x100/6c757d/white?text=' . strtoupper(substr($user->username, 0, 1)) }}" alt="{{ $user->username }}" class="table-img">
                                </td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->role)
                                        <span class="badge {{ $user->role->name == 'superadmin' ? 'bg-light-info' : 'bg-light-success' }}">{{ $user->role->name }}</span>
                                    @else
                                        <span class="badge bg-light-secondary">Tanpa Peran</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-warning btn-sm" wire:click="edit({{ $user->id }})"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-danger btn-sm" wire:click="$dispatch('swal:confirm', { id: {{ $user->id }}, method: 'destroy' })"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4">Tidak ada data pengguna yang ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    @if ($isModalOpen)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $userId ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="store">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" wire:model="username">
                            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" wire:model="email">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" wire:model="password" placeholder="{{ $userId ? 'Kosongkan jika tidak diubah' : '' }}">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="password_confirmation" wire:model="password_confirmation">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Peran (Role)</label>
                            <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" wire:model="role_id">
                                <option value="" selected>Pilih Peran</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto Profil @if(!$userId)<span class="text-danger">*</span>@endif</label>
                            <input type="file" class="form-control @error('foto') is-invalid @enderror" id="foto" wire:model="foto">
                            @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                <span wire:loading wire:target="store" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span wire:loading wire:target="store">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" style="{{ !$isModalOpen ? 'display: none;' : '' }}"></div>
    @endif
</div>
