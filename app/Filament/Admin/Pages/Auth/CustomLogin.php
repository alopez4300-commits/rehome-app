<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent()
                    ->label('Email')
                    ->email()
                    ->required()
                    ->autocomplete()
                    ->autofocus()
                    ->extraInputAttributes(['tabindex' => 1]),

                $this->getPasswordFormComponent()
                    ->label('Password')
                    ->password()
                    ->required()
                    ->extraInputAttributes(['tabindex' => 2]),

                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function validateUser(): void
    {
        parent::validateUser();
        
        // Check if user is admin after authentication
        if (auth()->check() && !auth()->user()->isAdmin()) {
            // Log them out and show error
            auth()->logout();
            
            throw ValidationException::withMessages([
                'data.email' => [
                    __('Only administrators can access this panel. Please contact your system administrator for access.'),
                ],
            ]);
        }
    }

    public function getTitle(): string
    {
        return 'Admin Panel Login';
    }

    public function getHeading(): string
    {
        return 'Welcome to Admin Panel';
    }

    public function getSubheading(): string
    {
        return 'Please sign in with your administrator credentials';
    }
}
