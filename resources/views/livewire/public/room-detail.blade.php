<div class="container py-5">
    @include('components.public.breadcrumbs', ['items' => [
        ['label' => 'Beranda', 'url' => url('/')],
        ['label' => 'Daftar Kamar', 'url' => route('rooms')],
        ['label' => 'Kamar No. '.$room->room_number]
    ]])
    <div class="row">
        <div class="col-lg-7">
            <div id="gallery" class="mb-3">
                @php($images = $room->roomType->images)
                @if($images && $images->count())
                    <div id="carouselRoom" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner rounded shadow-sm">
                            @foreach($images as $i => $img)
                                <div class="carousel-item {{ $i===0 ? 'active' : '' }}">
                                    <img src="{{ Storage::url($img->path) }}" class="d-block w-100" alt="room-{{ $i }}">
                                </div>
                            @endforeach
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselRoom" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselRoom" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                @else
                    <img class="img-fluid rounded shadow-sm" src="https://images.unsplash.com/photo-1551776235-dde6d4829808?auto=format&fit=crop&w=1200&q=60" alt="room">
                @endif
            </div>
            <h3 class="mb-1">{{ $room->roomType->name ?? 'Tipe Kamar' }} - No. {{ $room->room_number }}</h3>
            <div class="text-muted mb-3">Lantai {{ $room->floor }}</div>
            <p class="text-muted">{{ $room->description }}</p>
            <h6 class="mt-4">Fasilitas</h6>
            <div class="d-flex flex-wrap gap-2">
                @forelse(($room->roomType->facilities ?? []) as $fac)
                    <span class="badge bg-light text-dark border">{{ $fac->name }}</span>
                @empty
                    <span class="text-muted">Tidak ada data fasilitas.</span>
                @endforelse
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Harga per malam</div>
                            <div class="h4 m-0">Rp {{ number_format($room->price_per_night,0,',','.') }}</div>
                        </div>
                        <a class="btn btn-primary" href="{{ route('booking.wizard', ['room' => $room->id]) }}">Pesan</a>
                    </div>
                    <hr>
                    <div class="text-muted small">Kebijakan Pembatalan</div>
                    <p class="small">Pembatalan gratis hingga 48 jam sebelum check-in. Setelah itu, biaya setara 1 malam akan dikenakan.</p>
                    <hr>
                    <div class="text-muted small">Tanggal Terbooked (mendatang)</div>
                    @if(!empty($bookedRanges))
                        <ul class="list-unstyled small mb-0">
                            @foreach($bookedRanges as $r)
                                <li><span class="badge bg-light text-dark">{{ $r['from'] }} → {{ $r['to'] }}</span></li>
                            @endforeach
                        </ul>
                    @else
                        <div class="small text-success">Semua tanggal tersedia.</div>
                    @endif
                    <hr>
                    <div class="text-muted small mb-1">Kalender Ketersediaan</div>
                    @php
                        $start = \Carbon\Carbon::now()->startOfMonth();
                        $months = [$start->copy(), $start->copy()->addMonth()];
                        $bookedDates = [];
                        foreach(($bookedRanges ?? []) as $rg){
                            $from = \Carbon\Carbon::parse($rg['from']);
                            $to = \Carbon\Carbon::parse($rg['to']);
                            for($d = $from->copy(); $d->lt($to); $d->addDay()){
                                $bookedDates[$d->format('Y-m-d')] = true;
                            }
                        }
                    @endphp
                    <div class="row g-2">
                        @foreach($months as $m)
                            @php
                                $firstDay = $m->copy()->startOfMonth();
                                $lastDay = $m->copy()->endOfMonth();
                                $startDow = (int)$firstDay->format('N'); // 1..7 (Mon..Sun)
                                $daysInMonth = $lastDay->day;
                            @endphp
                            <div class="col-6">
                                <div class="border rounded p-2 small">
                                    <div class="text-center fw-semibold mb-1">{{ $m->translatedFormat('F Y') }}</div>
                                    <div class="d-grid" style="grid-template-columns: repeat(7, 1fr); gap: 4px;">
                                        @foreach(['S','S','R','K','J','S','M'] as $w)
                                            <div class="text-center text-muted">{{ $w }}</div>
                                        @endforeach
                                        @for($i=1;$i<$startDow;$i++)
                                            <div></div>
                                        @endfor
                                        @for($d=1;$d<=$daysInMonth;$d++)
                                            @php($dateStr = $m->copy()->day($d)->format('Y-m-d'))
                                            @php($isBooked = isset($bookedDates[$dateStr]))
                                            <div class="text-center {{ $isBooked ? 'bg-danger text-white' : 'bg-light' }} rounded" title="{{ $dateStr }}">{{ $d }}</div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container mt-3">
    <h5 class="mb-2">Kamar Serupa</h5>
    <div class="row g-3">
        @forelse($similarRooms as $s)
            <div class="col-md-4">
                <div class="card h-100">
                    @php($first = optional($s->roomType->images()->first())->path)
                    <img class="card-img-top" src="{{ $first ? Storage::url($first) : 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=800&q=60' }}" alt="room">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">{{ $s->roomType->name ?? 'Tipe' }}</h6>
                        <h5>No. {{ $s->room_number }}</h5>
                        <div class="mb-2">Rp {{ number_format($s->price_per_night,0,',','.') }} <small class="text-muted">/ malam</small></div>
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('rooms.detail', ['room' => $s->id]) }}">Lihat</a>
                        <a class="btn btn-primary btn-sm" href="{{ route('booking.wizard', ['room' => $s->id]) }}">Pesan</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="text-muted">Belum ada rekomendasi.</div></div>
        @endforelse
    </div>
</div>
