<div class="modal fade show" id="facilityModal" tabindex="-1" style="display: block;" aria-modal="true" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $facilityId ? 'Edit Facility' : 'Create New Facility' }}</h5>
                <button type="button" class="close" wire:click="closeModal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" wire:model="name" placeholder="e.g., Free Wi-Fi">
                        @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    {{-- Icon input removed as requested --}}
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                <button type="button" wire:click.prevent="store()" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show"></div>
