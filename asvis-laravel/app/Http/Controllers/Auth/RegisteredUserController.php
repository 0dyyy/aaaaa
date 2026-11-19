<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'height_cm' => ['nullable', 'numeric', 'min:50', 'max:250'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'weight_kg' => $request->weight_kg,
            'height_cm' => $request->height_cm,
        ]);

        event(new Registered($user));

        Auth::login($user);

        if ($request->wantsJson()) {
            return $this->success('Registrasi berhasil', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ], 201);
        }

        return redirect('/');
    }
}