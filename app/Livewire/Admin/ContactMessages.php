<?php

namespace App\Livewire\Admin;
use Livewire\Attributes\Layout;

use App\Models\ContactMessage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ContactMessages extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Title('Pesan Kontak')]
    public $search = '';
    public $status = '';
    public $perPage = 10;
    public $startDate = null;
    public $endDate = null;
    public $showModal = false;
    public $selected = null;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }

    public function render()
    {
        $q = ContactMessage::query()->latest();
        if ($this->status === 'unread') {
            $q->whereNull('read_at');
        } elseif ($this->status === 'read') {
            $q->whereNotNull('read_at');
        }
        if ($this->search) {
            $term = '%'.$this->search.'%';
            $q->where(function ($qq) use ($term) {
                $qq->where('name', 'like', $term)
                   ->orWhere('email', 'like', $term)
                   ->orWhere('message', 'like', $term);
            });
        }
        if ($this->startDate) {
            $q->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $q->whereDate('created_at', '<=', $this->endDate);
        }
        return view('livewire.admin.contact-messages', [
            'items' => $q->paginate($this->perPage),
        ]);
    }

    public function view($id)
    {
        $this->selected = ContactMessage::findOrFail($id);
        if (!$this->selected->read_at) {
            $this->selected->update(['read_at' => now()]);
        }
        $this->showModal = true;
    }

    public function close()
    {
        $this->showModal = false;
        $this->selected = null;
    }

    public function markRead($id)
    {
        $m = ContactMessage::findOrFail($id);
        if (!$m->read_at) {
            $m->update(['read_at' => now()]);
            $this->dispatch('swal:success', ['message' => 'Ditandai sebagai dibaca.']);
        }
    }

    public function delete($id)
    {
        ContactMessage::findOrFail($id)->delete();
        $this->dispatch('swal:success', ['message' => 'Pesan dihapus.']);
    }
}
