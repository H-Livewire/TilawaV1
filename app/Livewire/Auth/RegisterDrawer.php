<?php

namespace App\Livewire\Auth;

class RegisterDrawer extends Register
{
    public function clearPasswords(): void
    {
        $this->reset('password', 'password_confirmation');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.auth.register-drawer');
    }
}
