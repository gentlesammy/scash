<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Component;

class Settings extends Component
{
    public bool $requireEmailVerification;
    public bool $requirePhoneVerification;

    public function mount(): void
    {
        // Gate: only admins can access this panel
        abort_unless(auth()->user()?->isAdmin(), 403);

        $this->requireEmailVerification = Setting::getValue('require_email_verification', '0') === '1';
        $this->requirePhoneVerification = Setting::getValue('require_phone_verification', '0') === '1';
    }

    /**
     * Toggle email verification requirement and persist immediately.
     */
    public function toggleEmailVerification(): void
    {
        $this->requireEmailVerification = !$this->requireEmailVerification;
        Setting::setValue('require_email_verification', $this->requireEmailVerification ? '1' : '0');

        session()->flash('success', 'Email verification requirement ' . ($this->requireEmailVerification ? 'enabled' : 'disabled') . '.');
    }

    /**
     * Toggle phone verification requirement and persist immediately.
     */
    public function togglePhoneVerification(): void
    {
        $this->requirePhoneVerification = !$this->requirePhoneVerification;
        Setting::setValue('require_phone_verification', $this->requirePhoneVerification ? '1' : '0');

        session()->flash('success', 'Phone verification requirement ' . ($this->requirePhoneVerification ? 'enabled' : 'disabled') . '.');
    }

    public function render()
    {
        return view('livewire.admin.settings')->layout('layouts.app');
    }
}
