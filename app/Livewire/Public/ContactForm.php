<?php

namespace App\Livewire\Public;

use App\Mail\ContactMessageMail;
use App\Mail\ContactThanksMail;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Component;

class ContactForm extends Component
{
    public $name = '';
    public $email = '';
    public $subject = '';
    public $phone = '';
    public $message = '';
    // Honeypot field (should remain empty by humans)
    public $website = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:100',
            'email' => 'required|email|max:160',
            'subject' => 'nullable|string|max:160',
            'phone' => 'nullable|string|max:30',
            'message' => 'required|string|min:10|max:2000',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama wajib diisi.',
        'name.min' => 'Nama minimal 3 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'message.required' => 'Pesan wajib diisi.',
        'message.min' => 'Pesan minimal 10 karakter.',
    ];

    public function render()
    {
        return view('livewire.public.contact-form');
    }

    public function submit()
    {
        $data = $this->validate();
        // Honeypot check
        if (!empty($this->website)) {
            // Silently ignore bots
            $this->dispatch('swal:info', ['message' => 'Terima kasih!']);
            $this->reset(['name', 'email', 'message', 'website']);
            return;
        }

        // Simple rate limit: max 5 submissions per 10 minutes per IP
        $ip = request()->ip();
        $key = 'contact_rate:'.md5($ip);
        $count = (int) Cache::get($key, 0);
        if ($count >= 5) {
            $this->dispatch('swal:error', ['message' => 'Terlalu banyak percobaan. Coba lagi beberapa menit nanti.']);
            return;
        }
        Cache::put($key, $count + 1, now()->addMinutes(10));

        $toAddress = config('mail.contact_to')
            ?? config('mail.from.address');

        try {
            // Save to DB
            $record = ContactMessage::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'] ?? null,
                'phone' => $data['phone'] ?? null,
                'message' => $data['message'],
                'ip' => $ip,
            ]);

            // Send to admin
            Mail::to($toAddress)->queue(new ContactMessageMail(
                $data['name'],
                $data['email'],
                $data['message'],
                $data['subject'] ?? null,
                $data['phone'] ?? null,
            ));
            // Confirmation to sender
            Mail::to($data['email'])->queue(new ContactThanksMail($data['name']));
            $this->reset(['name', 'email', 'subject', 'phone', 'message']);
            $this->dispatch('swal:success', ['message' => 'Pesan berhasil dikirim. Terima kasih!']);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('swal:error', ['message' => 'Gagal mengirim pesan. Silakan coba lagi.']);
        }
    }
}
