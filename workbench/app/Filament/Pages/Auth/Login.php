<?php

namespace Workbench\App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

final class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        if (app()->isLocal()) {
            $this->form->fill([
                'email' => 'test@example.com',
                'password' => 'password',
                'remember' => true,
            ]);
        }
    }
}
