<div class="container py-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Kelola Foto: Kamar No. {{ $room->room_number }} ({{ $room->roomType->name ?? 'Tipe' }})</h5>
            <div class="mb-3 d-flex gap-2 align-items-center">
                <input type="file" multiple class="form-control" wire:model="photos">
                <button class="btn btn-primary" wire:click="upload" wire:loading.attr="disabled">Unggah</button>
            </div>
            <div class="row g-3">
                @forelse($room->images as $img)
                    <div class="col-md-3">
                        <div class="card h-100">
                            <img class="card-img-top" src="{{ Storage::url($img->path) }}" alt="room">
                            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                <span class="small text-muted">#{{ $img->id }}</span>
                                <button class="btn btn-sm btn-outline-danger" wire:click="delete({{ $img->id }})">Hapus</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12"><div class="text-muted">Belum ada foto.</div></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

