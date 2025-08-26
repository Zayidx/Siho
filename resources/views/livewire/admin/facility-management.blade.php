<div>
    <div class="page-heading">
        <h3>Facility Management</h3>
    </div>
    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <button wire:click="create()" class="btn btn-primary">Add New Facility</button>
                </div>
                <div class="card-body">
                    @if($isOpen)
                        @include('livewire.admin.facility-form')
                    @endif

                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Icon</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($facilities as $facility)
                                <tr>
                                    <td><i class="{{ $facility->icon }}"></i></td>
                                    <td>{{ $facility->name }}</td>
                                    <td>
                                        <button wire:click="edit({{ $facility->id }})" class="btn btn-sm btn-info">Edit</button>
                                        <button wire:click="delete({{ $facility->id }})" class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No facilities found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $facilities->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>