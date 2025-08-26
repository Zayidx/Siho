<div>
    <div wire:ignore>
        <div id="calendar"></div>
    </div>

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script>
        document.addEventListener('livewire:init', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: '{{ route("admin.calendar.events") }}',
                eventDidMount: function(info) {
                    // Optional: Add a tooltip or popover for more details
                    // Example using Bootstrap's popover (if you have it loaded)
                    // info.el.setAttribute('data-bs-toggle', 'popover');
                    // info.el.setAttribute('data-bs-trigger', 'hover');
                    // info.el.setAttribute('data-bs-content', info.event.title);
                    // new bootstrap.Popover(info.el);
                }
            });
            calendar.render();
        });
    </script>
    @endpush
</div>