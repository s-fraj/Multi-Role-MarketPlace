<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfilePhotoController extends Controller
{
    /**
     * POST /profile/photo — used by the sidebar pencil icon and every
     * "Changer la photo" button, for both client and vendeur accounts.
     */
    public function update(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $user = Auth::user();

        // Remove the previous photo so old files don't pile up in storage.
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('photo')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Photo de profil mise à jour.',
            'url' => $user->avatarUrl(),
        ]);
    }

    /**
     * DELETE /profile/photo — remove the current photo and fall back to initials.
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Photo de profil supprimée.',
            'initials' => $user->initials(),
        ]);
    }
}
