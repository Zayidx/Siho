<div class="container py-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Kelola Galeri Hotel</h5>
            <div class="mb-3 d-flex gap-2 align-items-center">
                <input type="file" multiple class="form-control @error('photos.*') is-invalid @enderror" wire:model.live="photos" accept="image/*">
                <button class="btn btn-primary" wire:click="savePhotos" wire:loading.attr="disabled" wire:target="photos,savePhotos">Unggah</button>
            </div>
            @error('photos') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
            @error('photos.*') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
            <div wire:loading wire:target="photos" class="text-muted small mb-3">Mengunggah file...</div>

            <div class="row g-3">
                @forelse($rows as $img)
                    <div class="col-md-3">
                        <div class="card h-100">
                            <img class="card-img-top" src="{{ Storage::disk('public')->exists($img->path) ? Storage::url($img->path) : 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=800&q=60' }}" alt="gallery">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small text-muted">#{{ $img->id }}</span>
                                        @if($img->is_cover)
                                            <span class="badge bg-primary">Cover</span>
                                        @endif
                                    </div>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-secondary" title="Naik" wire:click="moveUp({{ $img->id }})">↑</button>
                                        <button class="btn btn-outline-secondary" title="Turun" wire:click="moveDown({{ $img->id }})">↓</button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small mb-1">Kategori</label>
                                    <select class="form-select form-select-sm" wire:change="setCategory({{ $img->id }}, $event.target.value)">
                                        @foreach($this->categories as $key => $label)
                                            <option value="{{ $key }}" @selected($img->category === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="setCover({{ $img->id }})">Jadikan Cover</button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="delete({{ $img->id }})">Hapus</button>
                                </div>
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

