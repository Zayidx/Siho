<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <h5 class="m-0">Ketersediaan Kamar</h5>
            <div class="d-flex align-items-center gap-3 small">
                <span class="d-inline-flex align-items-center gap-1"><span class="rounded-circle d-inline-block" style="width:12px;height:12px;background:#0d6efd"></span> Booked</span>
                <span class="d-inline-flex align-items-center gap-1"><span class="rounded-circle d-inline-block" style="width:12px;height:12px;background:#198754"></span> Checked-in</span>
                <span class="d-inline-flex align-items-center gap-1"><span class="rounded-circle d-inline-block" style="width:12px;height:12px;background:#6c757d"></span> Checked-out</span>
                <span class="d-inline-flex align-items-center gap-1"><span class="rounded-circle d-inline-block" style="width:12px;height:12px;background:#dc3545"></span> Cancelled</span>
            </div>
        </div>
        <div class="mt-3 row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small">Status</label>
                <select class="form-select form-select-sm" wire:model.live="status" wire:change="filtersUpdated">
                    <option value="">Semua</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="Checked-in">Checked-in</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small">Tipe Kamar</label>
                <select class="form-select form-select-sm" wire:model.live="roomType" wire:change="filtersUpdated">
                    <option value="">Semua</option>
                    @foreach($roomTypes as $rt)
                        <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small">No. Kamar</label>
                <input type="text" class="form-control form-control-sm" placeholder="cth: 101" wire:model.debounce.400ms="roomNumber" wire:change="filtersUpdated">
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" wire:click="filtersUpdated">Terapkan</button>
                <button class="btn btn-sm btn-light" onclick="window.calendarFilters={status:'',room_type_id:'',room:''}; if(window.calendar){ window.calendar.refetchEvents(); }">Reset</button>
            </div>
        </div>
    </div>
    <div class="card-body" wire:ignore>
        <div id="calendar" style="min-height: 680px;"></div>
    </div>
</div>

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script>
    document.addEventListener('livewire:init', function() {
        var calendarEl = document.getElementById('calendar');
        window.calendarFilters = {status:'', room_type_id:'', room:''};
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            dayMaxEventRows: 3,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: function(info, success, failure) {
                const params = new URLSearchParams({
                    start: info.startStr,
                    end: info.endStr,
                    status: window.calendarFilters.status || '',
                    room_type_id: window.calendarFilters.room_type_id || '',
                    room: window.calendarFilters.room || '',
                });
                fetch('{{ route("admin.calendar.events") }}?' + params.toString())
                    .then(r => r.json())
                    .then(success)
                    .catch(failure);
            },
            eventContent: function(arg) {
                const guest = arg.event.extendedProps.guest || '';
                const room = arg.event.extendedProps.room || '';
                const title = document.createElement('div');
                title.innerHTML = `<strong>${arg.event.title}</strong><br><small>${guest}</small>`;
                return { domNodes: [title] };
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                const ev = info.event;
                const p = ev.extendedProps || {};
                const modal = document.getElementById('eventOverlay');
                const box = document.getElementById('eventOverlayBox');
                box.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="m-0">Reservasi #${p.reservation_id || '-'}</h6>
                        <button class="btn btn-sm btn-light" onclick="document.getElementById('eventOverlay').classList.add('d-none')">Tutup</button>
                    </div>
                    <div class="small">
                        <div><strong>Kamar:</strong> ${p.room || '-'}</div>
                        <div><strong>Tamu:</strong> ${p.guest || '-'}</div>
                        <div><strong>Status:</strong> ${p.status || '-'}</div>
                        <div><strong>Check-in:</strong> ${p.check_in || '-'}</div>
                        <div><strong>Check-out:</strong> ${p.check_out || '-'}</div>
                    </div>
                `;
                modal.classList.remove('d-none');
            },
        });
        calendar.render();
        window.calendar = calendar;
        // Close overlay on ESC
        document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ document.getElementById('eventOverlay')?.classList.add('d-none'); } });

        // Listen filter updates from Livewire
        window.addEventListener('calendar:filters-updated', function(e){
            window.calendarFilters = {
                status: e.detail.status || '',
                room_type_id: e.detail.room_type_id || '',
                room: e.detail.room || ''
            };
            calendar.refetchEvents();
        });
    });
</script>

<div id="eventOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0,0,0,.65); z-index: 1080;">
    <div id="eventOverlayBox" class="bg-white rounded shadow p-3" style="position:absolute; left:50%; top:10%; transform:translateX(-50%); width: min(560px, 92vw);"></div>
    <button class="position-absolute btn btn-light" style="top:12px; right:12px;" onclick="document.getElementById('eventOverlay').classList.add('d-none')">Tutup</button>
    <div class="w-100 h-100" onclick="document.getElementById('eventOverlay').classList.add('d-none')"></div>
</div>
@endpush
