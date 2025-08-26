<div>
    <div class="page-heading">
        <h3>Housekeeping Management</h3>
        <p class="text-muted">Manage room cleaning status and maintenance.</p>
    </div>
    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <div class="btn-group" role="group">
                        <button wire:click="setFilter('')" class="btn {{ $statusFilter == '' ? 'btn-primary' : 'btn-secondary' }}">All</button>
                        @foreach($statuses as $status)
                            <button wire:click="setFilter('{{ $status }}')" class="btn {{ $statusFilter == $status ? 'btn-primary' : 'btn-secondary' }}">{{ $status }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Room No.</th>
                                <th>Room Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rooms as $room)
                                <tr>
                                    <td>{{ $room->room_number }}</td>
                                    <td>{{ $room->roomType->name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $badgeColor = match($room->status) {
                                                'Available' => 'success',
                                                'Occupied' => 'danger',
                                                'Dirty' => 'warning',
                                                'Cleaning' => 'info',
                                                'Maintenance' => 'dark',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-light-{{ $badgeColor }}">{{ $room->status }}</span>
                                    </td>
                                    <td>
                                        @if($room->status == 'Dirty')
                                            <button wire:click="changeStatus({{ $room->id }}, 'Cleaning')" class="btn btn-sm btn-info">Start Cleaning</button>
                                        @elseif($room->status == 'Cleaning')
                                            <button wire:click="changeStatus({{ $room->id }}, 'Available')" class="btn btn-sm btn-success">Mark as Clean</button>
                                        @elseif($room->status == 'Occupied')
                                            <button wire:click="changeStatus({{ $room->id }}, 'Dirty')" class="btn btn-sm btn-warning">Mark as Dirty (Checkout)</button>
                                        @elseif($room->status == 'Available')
                                            <button wire:click="changeStatus({{ $room->id }}, 'Maintenance')" class="btn btn-sm btn-dark">Set for Maintenance</button>
                                        @elseif($room->status == 'Maintenance')
                                            <button wire:click="changeStatus({{ $room->id }}, 'Available')" class="btn btn-sm btn-light-secondary">Set as Available</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No rooms found with status: {{ $statusFilter }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $rooms->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>