<?php

namespace App\Livewire\Auth;

class LoginDrawer extends Login
{
    public function clearPassword(): void
    {
        $this->password = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.auth.login-drawer');
    }
}
