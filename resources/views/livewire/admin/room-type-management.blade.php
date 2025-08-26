<div>
    <div class="page-heading">
        <h3>Room Type Management</h3>
    </div>
    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <button wire:click="create()" class="btn btn-primary">Add New Room Type</button>
                </div>
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success">{{ session('message') }}</div>
                    @endif

                    @if($isOpen)
                        @include('livewire.admin.room-type-form')
                    @endif

                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Base Price</th>
                                <th>Capacity</th>
                                <th>Facilities</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roomTypes as $roomType)
                                <tr>
                                    <td>{{ $roomType->name }}</td>
                                    <td>Rp{{ number_format($roomType->base_price, 0, ',', '.') }}</td>
                                    <td>{{ $roomType->capacity }} people</td>
                                    <td>
                                        @foreach($roomType->facilities as $facility)
                                            <span class="badge bg-secondary">{{ $facility->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <button wire:click="edit({{ $roomType->id }})" class="btn btn-sm btn-info">Edit</button>
                                        <button wire:click="delete({{ $roomType->id }})" class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No room types found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $roomTypes->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
