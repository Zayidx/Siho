<?php

namespace App\Livewire\Admin;

use App\Models\Reservation;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ReservationManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Manajemen Reservasi')]
    public $isModalOpen = false; // legacy flag

    public $reservationId;

    public $guest_id;

    public $check_in_date;

    public $check_out_date;

    public $status;

    public $special_requests;

    public $guests = [];

    public $availableRoomTypes = [];

    public $selectedRoomTypes = [];

    public $isCreatingGuest = false;

    public $newGuest_name;

    public $newGuest_email;

    public $newGuest_phone;

    public $isRoomModalOpen = false;

    public ?Room $viewingRoom = null;

    #[Url]
    public $search = '';

    #[Url]
    public $perPage = 10;

    #[Url]
    public $filterStatus = '';

    #[Url]
    public $startDate = null;

    #[Url]
    public $endDate = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

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
            $rules['newGuest_email'] = 'required|email|max:255|unique:users,email';
            $rules['newGuest_phone'] = 'required|string|max:20';
        } else {
            $rules['guest_id'] = 'required|exists:users,id';
        }

        return $rules;
    }

    protected $messages = [
        'check_in_date.required' => 'Tanggal check-in wajib diisi.',
        'check_in_date.date' => 'Tanggal check-in tidak valid.',
        'check_out_date.required' => 'Tanggal check-out wajib diisi.',
        'check_out_date.date' => 'Tanggal check-out tidak valid.',
        'check_out_date.after_or_equal' => 'Check-out harus sama atau setelah check-in.',
        'status.required' => 'Status reservasi wajib dipilih.',
        'status.in' => 'Status reservasi tidak valid.',
        'special_requests.string' => 'Permintaan khusus tidak valid.',
        'selectedRoomTypes.required' => 'Pilih minimal satu tipe kamar.',
        'selectedRoomTypes.array' => 'Format pilihan kamar tidak valid.',
        // New guest
        'newGuest_name.required' => 'Nama tamu wajib diisi.',
        'newGuest_email.required' => 'Email tamu wajib diisi.',
        'newGuest_email.email' => 'Format email tamu tidak valid.',
        'newGuest_email.unique' => 'Email tamu sudah terdaftar.',
        'newGuest_phone.required' => 'Nomor telepon tamu wajib diisi.',
        // Existing guest
        'guest_id.required' => 'Pilih tamu.',
        'guest_id.exists' => 'Tamu tidak ditemukan.',
    ];

    protected $validationAttributes = [
        'check_in_date' => 'Tanggal check-in',
        'check_out_date' => 'Tanggal check-out',
        'status' => 'Status',
        'special_requests' => 'Permintaan khusus',
        'selectedRoomTypes' => 'Pilihan kamar',
        'guest_id' => 'Tamu',
        'newGuest_name' => 'Nama tamu',
        'newGuest_email' => 'Email tamu',
        'newGuest_phone' => 'No. telepon tamu',
    ];

    public function mount()
    {
        $this->guests = User::orderBy('full_name')->get();
        $this->loadAvailableRoomTypes();
    }

    private function loadAvailableRoomTypes()
    {
        $query = Room::query();
        if ($this->reservationId) {
            $currentRoomIds = Reservation::find($this->reservationId)->rooms()->pluck('rooms.id');
            $query->where('status', 'Available')->orWhereIn('rooms.id', $currentRoomIds);
        } else {
            $query->where('status', 'Available');
        }

        $this->availableRoomTypes = $query
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->select('room_types.id as type_id', 'room_types.name as type_name', DB::raw('count(*) as available_count'))
            ->groupBy('room_types.id', 'room_types.name')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->type_id => ['name' => $item->type_name, 'available_count' => $item->available_count]];
            })
            ->toArray();

        if (! $this->reservationId) {
            $this->selectedRoomTypes = collect($this->availableRoomTypes)
                ->mapWithKeys(function ($type, $key) {
                    return [$key => 0];
                })
                ->toArray();
        }
    }

    public function incrementRoomType($typeId)
    {
        $availableCount = $this->availableRoomTypes[$typeId]['available_count'] ?? 0;
        if ($this->selectedRoomTypes[$typeId] < $availableCount) {
            $this->selectedRoomTypes[$typeId]++;
        }
    }

    public function decrementRoomType($typeId)
    {
        if ($this->selectedRoomTypes[$typeId] > 0) {
            $this->selectedRoomTypes[$typeId]--;
        }
    }

    public function render()
    {
        $searchTerm = '%'.$this->search.'%';
        $reservations = Reservation::with('guest', 'rooms')
            ->when($this->search, function ($q) use ($searchTerm) {
                $q->where(function ($qq) use ($searchTerm) {
                    $qq->whereHas('guest', function ($g) use ($searchTerm) {
                        $g->where('full_name', 'like', $searchTerm)
                            ->orWhere('email', 'like', $searchTerm);
                    })
                        ->orWhere('status', 'like', $searchTerm);
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->startDate, fn ($q) => $q->whereDate('check_in_date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('check_out_date', '<=', $this->endDate))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.reservation-management', [
            'reservations' => $reservations,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->isModalOpen = true;
        $this->dispatch('modal:show', id: 'reservationModal');
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->startDate = null;
        $this->endDate = null;
        $this->resetPage();
    }

    public function edit($id)
    {
        $reservation = Reservation::with('rooms.roomType')->findOrFail($id);
        $this->reservationId = $id;
        $this->guest_id = $reservation->guest_id;
        $this->check_in_date = Carbon::parse($reservation->check_in_date)->format('Y-m-d');
        $this->check_out_date = Carbon::parse($reservation->check_out_date)->format('Y-m-d');
        $this->status = $reservation->status;
        $this->special_requests = $reservation->special_requests;

        $this->selectedRoomTypes = $reservation->rooms
            ->groupBy('room_type_id')
            ->map(fn ($group) => $group->count())
            ->toArray();

        $this->loadAvailableRoomTypes();
        $this->isModalOpen = true;
        $this->dispatch('modal:show', id: 'reservationModal');
    }

    public function store()
    {
        $validatedData = $this->validate();
        $guestIdToUse = $this->guest_id;

        if ($this->isCreatingGuest) {
            $newGuest = User::create([
                'full_name' => $this->newGuest_name,
                'username' => $this->newGuest_email,
                'email' => $this->newGuest_email,
                'phone' => $this->newGuest_phone,
                'password' => bcrypt(Str::random(12)),
                'role_id' => Role::where('name', 'users')->value('id') ?? 2,
            ]);
            $guestIdToUse = $newGuest->id;
            $this->guests = User::orderBy('full_name')->get();
        }

        $reservationData = [
            'guest_id' => $guestIdToUse,
            'check_in_date' => $validatedData['check_in_date'],
            'check_out_date' => $validatedData['check_out_date'],
            'status' => $validatedData['status'],
            'special_requests' => $validatedData['special_requests'],
        ];

        $reservation = Reservation::updateOrCreate(['id' => $this->reservationId], $reservationData);

        $syncResult = app(\App\Services\ReservationService::class)
            ->syncRoomsForReservation($reservation, $this->selectedRoomTypes);

        // If reservation is ended or cancelled, mark all associated rooms available
        if (in_array($validatedData['status'], ['Completed', 'Cancelled'])) {
            $allRoomIds = $reservation->rooms()->pluck('rooms.id')->all();
            if (! empty($allRoomIds)) {
                Room::whereIn('id', $allRoomIds)->update(['status' => 'Available']);
            }
        }

        $this->dispatch('swal:success', [
            'message' => $this->reservationId ? 'Reservasi berhasil diperbarui.' : 'Reservasi baru berhasil dibuat.',
        ]);

        $this->closeModal();
    }

    #[On('destroy')]
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);
        if ($reservation->status === 'Checked-in') {
            $this->dispatch('swal:error', ['message' => 'Aksi Gagal! Tidak dapat menghapus reservasi yang sedang aktif (checked-in).']);

            return;
        }

        $reservation->rooms()->update(['status' => 'Available']);
        $reservation->rooms()->detach();
        $reservation->delete();

        $this->dispatch('swal:success', ['message' => 'Reservasi berhasil dihapus.']);
    }

    public function viewRoom($roomId)
    {
        $this->viewingRoom = Room::find($roomId);
        if ($this->viewingRoom) {
            $this->isRoomModalOpen = true;
            $this->dispatch('modal:show', id: 'roomDetailModal');
        }
    }

    public function closeRoomModal()
    {
        $this->isRoomModalOpen = false;
        $this->dispatch('modal:hide', id: 'roomDetailModal');
        $this->viewingRoom = null;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->dispatch('modal:hide', id: 'reservationModal');
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['reservationId', 'guest_id', 'check_in_date', 'check_out_date', 'status', 'special_requests', 'isCreatingGuest', 'newGuest_name', 'newGuest_email', 'newGuest_phone']);
        $this->loadAvailableRoomTypes();
        $this->resetErrorBag();
    }
}
