<div class="container py-4">
    @include('components.public.breadcrumbs', ['items' => [
        ['label' => 'Dashboard', 'url' => route('user.dashboard')],
        ['label' => 'Profil']
    ]])
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Profil Saya</h5>
                    <form wire:submit.prevent="save">
                        <div class="row">
                            <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" wire:model.defer="full_name">
                            @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model.defer="email">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telepon</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" wire:model.defer="phone">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alamat</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" wire:model.defer="address">
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        </div>
                            <div class="col-md-4">
                                <label class="form-label">Foto Profil</label>
                                <input type="file" class="form-control @error('photo') is-invalid @enderror" wire:model="photo">
                                @error('photo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                <div class="mt-2">
                                    @if($photo)
                                        <img class="img-thumbnail" style="max-height:140px" src="{{ $photo->temporaryUrl() }}" alt="Preview">
                                    @else
                                        <img class="img-thumbnail" style="max-height:140px" src="{{ Auth::user()->foto ? Storage::url(Auth::user()->foto) : 'https://placehold.co/200x200?text=Photo' }}" alt="Current">
                                    @endif
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model.defer="password">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" wire:model.defer="password_confirmation">
                            </div>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">
                                <span wire:loading.remove>Simpan Perubahan</span>
                                <span wire:loading class="spinner-border spinner-border-sm"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
