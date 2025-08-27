<form wire:submit.prevent="submit">
    <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name" placeholder="Nama Lengkap">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model.defer="email" placeholder="email@contoh.com">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label class="form-label">Pesan</label>
        <textarea rows="4" class="form-control @error('message') is-invalid @enderror" wire:model.defer="message" placeholder="Tuliskan pesan Anda"></textarea>
        @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
        <span wire:loading.remove>Kirim</span>
        <span wire:loading class="spinner-border spinner-border-sm"></span>
    </button>
</form>

