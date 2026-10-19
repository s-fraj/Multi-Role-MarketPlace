<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Boutique;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['required', Rule::in(['client', 'vendeur'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'telephone' => ['required', 'string', 'max:30'],
            'adresse' => ['required', 'string', 'max:255'],
            'ville' => ['required', 'string', 'max:255'],
            'code_postal' => ['required', 'string', 'max:10'],
            'nom_boutique' => ['required_if:role,vendeur', 'nullable', 'string', 'max:255'],
            'categorie_principale' => ['required_if:role,vendeur', 'nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->telephone,
            'address' => $request->adresse,
            'city' => $request->ville,
            'postal_code' => $request->code_postal,
        ]);

        if ($request->role === 'vendeur') {
            Boutique::create([
                'user_id' => $user->id,
                'nom' => $request->nom_boutique,
                'categorie' => $request->categorie_principale,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route($user->homeRouteName(), absolute: false));
    }
}
