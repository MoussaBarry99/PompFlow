<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            return parent::authenticate();
        } catch (ValidationException $exception) {
            $user = User::where('email', $this->data['email'] ?? '')->first();

            if (
                $user
                && Hash::check($this->data['password'] ?? '', $user->password)
                && ! $user->canAccessPanel(Filament::getCurrentPanel())
            ) {
                throw ValidationException::withMessages([
                    'data.email' => "Ce compte n'a pas accès à l'espace de gestion. Chauffeurs et employés : connecte-toi sur " . url('/login') . '.',
                ]);
            }

            throw $exception;
        }
    }
}