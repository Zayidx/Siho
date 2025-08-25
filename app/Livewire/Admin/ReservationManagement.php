<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Reservations;
use App\Models\Guest;
use App\Models\Rooms;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;

class ReservationManagement extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Title("Manajemen Reservasi")]
    public $isModalOpen = false;
    public $reservationId, $guest_id, $check_in_date, $check_out_date, $status, $special_requests;
    
    public $guests = [];
    
    public $availableRoomTypes = [];
    public $selectedRoomTypes = [];

    public $isCreatingGuest = false;
    public $newGuest_name, $newGuest_email, $newGuest_phone;

    public $isRoomModalOpen = false;
    public ?Rooms $viewingRoom = null;

    public $search = '';
    public $perPage = 10;

    protected function rules()
    {
        $rules = [
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'status' => 'required|string|in:Confirmed,Checked-in,Completed,Cancelled',
            'special_requests' => 'nullable|string',
            'selectedRoomTypes' => ['required', 'array', function ($attribute, $value, $fail) {
                if (collect($value)->sum() <= 0) {
                    $fail('Minimal satu kamar harus dipilih.');
                }
            }],
        ];

        if ($this->isCreatingGuest) {
            $rules['newGuest_name'] = 'required|string|max:255';
            $rules['newGuest_email'] = 'required|email|max:255|unique:guests,email';
            $rules['newGuest_phone'] = 'required|string|max:20';
        } else {
            $rules['guest_id'] = 'required|exists:guests,id';
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            'guest_id.required' => 'Tamu wajib dipilih.',
            'check_in_date.required' => 'Tanggal check-in wajib diisi.',
            'check_out_date.required' => 'Tanggal check-out wajib diisi.',
            'check_out_date.after_or_equal' => 'Tanggal check-out harus sama atau setelah tanggal check-in.',
            'newGuest_name.required' => 'Nama tamu baru wajib diisi.',
            'newGuest_email.required' => 'Email tamu baru wajib diisi.',
            'newGuest_email.unique' => 'Email ini sudah terdaftar.',
            'newGuest_phone.required' => 'Telepon tamu baru wajib diisi.',
        ];
    }

    public function mount()
    {
        $this->guests = Guest::orderBy('full_name')->get();
        $this->loadAvailableRoomTypes();
    }

    private function loadAvailableRoomTypes()
    {
        // [DIUBAH] Saat mengedit, kamar yang sedang dipesan juga harus dianggap "tersedia" untuk dipilih kembali
        $query = Rooms::query();
        if ($this->reservationId) {
            // [DIPERBAIKI] Spesifikasikan kolom 'id' dari tabel 'rooms'
            $currentRoomIds = Reservations::find($this->reservationId)->rooms()->pluck('rooms.id');
            $query->where('status', 'Available')->orWhereIn('id', $currentRoomIds);
        } else {
            $query->where('status', 'Available');
        }

        $this->availableRoomTypes = $query
            ->select('room_type', DB::raw('count(*) as available_count'))
            ->groupBy('room_type')
            ->get()
            ->keyBy('room_type')
            ->toArray();

        // Inisialisasi counter, jika belum ada (saat create)
        if (!$this->reservationId) {
            $this->selectedRoomTypes = collect($this->availableRoomTypes)
                ->mapWithKeys(function ($type, $key) {
                    return [$key => 0];
                })
                ->toArray();
        }
    }

    public function incrementRoomType($type)
    {
        $availableCount = $this->availableRoomTypes[$type]['available_count'] ?? 0;
        if ($this->selectedRoomTypes[$type] < $availableCount) {
            $this->selectedRoomTypes[$type]++;
        }
    }

    public function decrementRoomType($type)
    {
        if ($this->selectedRoomTypes[$type] > 0) {
            $this->selectedRoomTypes[$type]--;
        }
    }

    public function render()
    {
        $searchTerm = '%' . $this->search . '%';
        $reservations = Reservations::with('guest', 'rooms')
            ->whereHas('guest', function ($query) use ($searchTerm) {
                $query->where('full_name', 'like', $searchTerm);
            })
            ->orWhere('status', 'like', $searchTerm)
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.reservation-management', [
            'reservations' => $reservations
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $reservation = Reservations::with('rooms')->findOrFail($id);
        $this->reservationId = $id;
        $this->guest_id = $reservation->guest_id;
        $this->check_in_date = $reservation->check_in_date->format('Y-m-d');
        $this->check_out_date = $reservation->check_out_date->format('Y-m-d');
        $this->status = $reservation->status;
        $this->special_requests = $reservation->special_requests;
        
        $this->selectedRoomTypes = $reservation->rooms
            ->groupBy('room_type')
            ->map(fn ($group) => $group->count())
            ->toArray();

        $this->loadAvailableRoomTypes(); // Muat ulang data kamar yang tersedia
        $this->isModalOpen = true;
    }

    public function store()
    {
        $validatedData = $this->validate();
        $guestIdToUse = $this->guest_id;

        if ($this->isCreatingGuest) {
            $newGuest = Guest::create([
                'full_name' => $this->newGuest_name,
                'email' => $this->newGuest_email,
                'phone' => $this->newGuest_phone,
            ]);
            $guestIdToUse = $newGuest->id;
            $this->guests = Guest::orderBy('full_name')->get();
        }

        $reservationData = [
            'guest_id' => $guestIdToUse,
            'check_in_date' => $validatedData['check_in_date'],
            'check_out_date' => $validatedData['check_out_date'],
            'status' => $validatedData['status'],
            'special_requests' => $validatedData['special_requests'],
        ];

        $reservation = Reservations::updateOrCreate(['id' => $this->reservationId], $reservationData);

        $roomIdsToSync = [];
        foreach ($this->selectedRoomTypes as $type => $count) {
            if ($count > 0) {
                // Ambil kamar yang tersedia ATAU kamar yang sudah terikat dengan reservasi ini
                $availableRoomIds = Rooms::where('room_type', $type)
                    ->where(function($query) use ($reservation) {
                        $query->where('status', 'Available')
                              ->orWhereHas('reservations', fn($q) => $q->where('reservation_id', $reservation->id));
                    })
                    ->take($count)
                    ->pluck('id')
                    ->toArray();
                $roomIdsToSync = array_merge($roomIdsToSync, $availableRoomIds);
            }
        }
        
        // [LOGIKA BARU] Gunakan sync untuk menangani status kamar secara otomatis
        $syncResult = $reservation->rooms()->sync($roomIdsToSync);

        // Kamar yang baru ditambahkan -> Occupied
        if (!empty($syncResult['attached'])) {
            Rooms::whereIn('id', $syncResult['attached'])->update(['status' => 'Occupied']);
        }
        // Kamar yang dilepaskan -> Available
        if (!empty($syncResult['detached'])) {
            Rooms::whereIn('id', $syncResult['detached'])->update(['status' => 'Available']);
        }

        // [LOGIKA BARU] Perbarui status kamar jika status reservasi berubah menjadi selesai/batal
        if (in_array($validatedData['status'], ['Completed', 'Cancelled'])) {
            Rooms::whereIn('id', $roomIdsToSync)->update(['status' => 'Available']);
        }

        $this->dispatch('swal:success', [
            'message' => $this->reservationId ? 'Reservasi berhasil diperbarui.' : 'Reservasi baru berhasil dibuat.'
        ]);

        $this->closeModal();
    }

    #[On('destroy')]
    public function destroy($id)
    {
        $reservation = Reservations::findOrFail($id);
        if ($reservation->status === 'Checked-in') {
            $this->dispatch('swal:error', ['message' => 'Aksi Gagal! Tidak dapat menghapus reservasi yang sedang aktif (checked-in).']);
            return;
        }
        
        // [LOGIKA BARU] Ubah status kamar menjadi 'Available' sebelum dihapus
        $reservation->rooms()->update(['status' => 'Available']);
        $reservation->rooms()->detach();
        $reservation->delete();

        $this->dispatch('swal:success', ['message' => 'Reservasi berhasil dihapus.']);
    }

    public function viewRoom($roomId)
    {
        $this->viewingRoom = Rooms::find($roomId);
        if ($this->viewingRoom) {
            $this->isRoomModalOpen = true;
        }
    }

    public function closeRoomModal()
    {
        $this->isRoomModalOpen = false;
        $this->viewingRoom = null;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['reservationId', 'guest_id', 'check_in_date', 'check_out_date', 'status', 'special_requests', 'isCreatingGuest', 'newGuest_name', 'newGuest_email', 'newGuest_phone']);
        $this->loadAvailableRoomTypes();
        $this->resetErrorBag();
    }
}
