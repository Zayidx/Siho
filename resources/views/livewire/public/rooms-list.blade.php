<div class="container py-5">
    @include('components.public.breadcrumbs', ['items' => [
        ['label' => 'Beranda', 'url' => url('/')],
        ['label' => 'Daftar Kamar']
    ]])
    <div class="row mb-3">
        <div class="col-lg-3">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Filter</h5>
                    <div class="mb-2">
                        <label class="form-label">Cari</label>
                        <input type="search" class="form-control" placeholder="Nomor/Deskripsi" wire:model.live.debounce.300ms="search">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tipe Kamar</label>
                        <select class="form-select" wire:model.live="roomType">
                            <option value="">Semua</option>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Fasilitas</label>
                        <div class="border rounded p-2" style="max-height: 180px; overflow:auto;">
                            @foreach($facilities as $f)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $f->id }}" wire:model.live="facilityIds">
                                    <label class="form-check-label">{{ $f->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col">
                            <label class="form-label">Harga Min</label>
                            <input type="number" class="form-control" wire:model.live="minPrice">
                        </div>
                        <div class="col">
                            <label class="form-label">Harga Max</label>
                            <input type="number" class="form-control" wire:model.live="maxPrice">
                        </div>
                    </div>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Kapasitas (min)</label>
                        <input type="number" class="form-control" wire:model.live="minCapacity" min="1">
                    </div>
                    <hr>
                    <h6 class="mt-2">Tanggal & Tamu</h6>
                    <div class="mb-2">
                        <label class="form-label">Check-in</label>
                        <input id="roomsCheckin" type="text" class="form-control" placeholder="Pilih tanggal" wire:model.live="checkin">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Check-out</label>
                        <input id="roomsCheckout" type="text" class="form-control" placeholder="Pilih tanggal" wire:model.live="checkout">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tamu</label>
                        <input type="number" class="form-control" wire:model.live="guests" min="1" max="6">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            @if($recommended && $recommended->count())
                <div class="mb-4">
                    <h5 class="mb-2">Rekomendasi</h5>
                    <div id="recCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach($recommended->chunk(3) as $idx => $chunk)
                                <div class="carousel-item {{ $idx===0 ? 'active' : '' }}">
                                    <div class="row g-3">
                                        @foreach($chunk as $r)
                                            <div class="col-md-4">
                                                <div class="card h-100">
                                                    @php($img = $r->images()->first())
                                                    <img class="card-img-top" src="{{ $img ? Storage::url($img->path) : 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=800&q=60' }}" alt="rec">
                                                    <div class="card-body">
                                                        <h6 class="text-muted mb-1">{{ $r->roomType->name ?? 'Tipe' }}</h6>
                                                        <h5>No. {{ $r->room_number }}</h5>
                                                        <div class="mb-2">Rp {{ number_format($r->price_per_night,0,',','.') }} <small class="text-muted">/ malam</small></div>
                                                        <a class="btn btn-outline-primary btn-sm" href="{{ route('rooms.detail', ['room' => $r->id]) }}">Lihat</a>
            <a class="btn btn-primary btn-sm" href="{{ route('booking.wizard', ['room' => $r->id, 'checkin' => $checkin, 'checkout' => $checkout]) }}">Pesan</a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#recCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#recCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            @endif
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="m-0">Daftar Kamar</h4>
                <div class="d-flex gap-2">
                    <select class="form-select" style="width:auto" wire:model.live="sort">
                        <option value="price_asc">Harga Termurah</option>
                        <option value="price_desc">Harga Termahal</option>
                        <option value="newest">Terbaru</option>
                        <option value="popular">Terpopuler</option>
                    </select>
                    <select class="form-select" style="width:auto" wire:model.live="perPage">
                        <option value="12">12</option>
                        <option value="24">24</option>
                        <option value="48">48</option>
                    </select>
                </div>
            </div>
            <div class="row g-3" wire:loading.class="opacity-50">
                @forelse($rooms as $room)
                    <div class="col-md-4">
                            <div class="card h-100 shadow-sm">
                                @php($firstImg = $room->images()->first())
                            <img class="card-img-top" src="{{ $firstImg ? Storage::url($firstImg->path) : 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=800&q=60' }}" alt="room">
                            <div class="card-body d-flex flex-column">
                                <h6 class="text-muted mb-1">{{ $room->roomType->name ?? 'Tipe Kamar' }}</h6>
                                <h5 class="card-title">No. {{ $room->room_number }}</h5>
                                <div class="mb-1">
                                    @if($room->status === 'Available')
                                        <span class="badge bg-success">Tersedia</span>
                                    @elseif($room->status === 'Occupied')
                                        <span class="badge bg-danger">Terisi</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Cleaning</span>
                                    @endif
                                </div>
                                <div class="mb-2">Rp {{ number_format($room->price_per_night,0,',','.') }} <small class="text-muted">/ malam</small></div>
                                <p class="text-muted small flex-grow-1">{{ Str::limit($room->description, 80) }}</p>
                                <div class="d-flex gap-2 mt-2">
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('rooms.detail', ['room' => $room->id]) }}">Detail</a>
                                    <a class="btn btn-primary btn-sm {{ !$checkin || !$checkout ? 'disabled' : '' }}" href="{{ route('booking.wizard', ['room' => $room->id, 'checkin' => $checkin, 'checkout' => $checkout]) }}">Pesan</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12"><div class="alert alert-warning">Tidak ada kamar ditemukan.</div></div>
                @endforelse
            </div>
            <div wire:loading class="mt-3">
                <div class="placeholder-wave">
                    <span class="placeholder col-12" style="height: 12px"></span>
                </div>
            </div>
            <div class="mt-3">{{ $rooms->links() }}</div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', initRoomsPicker);
document.addEventListener('livewire:navigated', initRoomsPicker);
function initRoomsPicker(){
  if (typeof flatpickr === 'undefined') return;
  const disabled = @json($disabledDates ?? []);
  const optsIn = { dateFormat:'Y-m-d', minDate: 'today', disable: disabled };
  const optsOut = { dateFormat:'Y-m-d', minDate: 'today', disable: disabled };
  const ci = document.getElementById('roomsCheckin');
  const co = document.getElementById('roomsCheckout');
  if (!ci || !co) return;
  const fpIn = flatpickr(ci, optsIn);
  const fpOut = flatpickr(co, optsOut);
  ci.addEventListener('change', () => {
    if (ci.value){ fpOut.set('minDate', ci.value); }
  });
}
</script>
