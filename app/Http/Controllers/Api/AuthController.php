<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register user baru
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'kota' => $request->kota,
            'is_verified' => $request->role === 'Donatur',
            'poin' => 0,
            'rating' => 0.00,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->username_or_email)
                    ->orWhere('username', $request->username_or_email)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username/Email atau Password salah'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'is_verified' => $user->is_verified,
                'poin' => $user->poin,
                'rating' => $user->rating,
            ]
        ]);
    }

    /**
     * Logout user (hapus token saat ini)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Ambil data profile user yang sedang login
     */
    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ]);
    }

    /**
     * Update profile (termasuk upload foto profil)
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $data = $request->only(['nama', 'username', 'email', 'no_hp', 'alamat', 'kota']);

        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil) {
                $oldPath = str_replace('/storage/', '', $user->foto_profil);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $file = $request->file('foto_profil');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profil', $filename, 'public');
            $data['foto_profil'] = Storage::url($path);
        }

        $user->update($data);
        $user->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui',
            'data' => $user
        ]);
    }

    // ✅ TAMBAHKAN METHOD INI
    public function syncGoogleUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'nama' => 'required|string',
            'google_id' => 'required|string',
            'foto_profil' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $user = User::create([
                'nama' => $request->nama,
                'username' => explode('@', $request->email)[0] . '_' . Str::random(4),
                'email' => $request->email,
                'google_id' => $request->google_id,
                'role' => 'Donatur',
                'is_verified' => true,
                'foto_profil' => $request->foto_profil,
                'password' => Hash::make(Str::random(16)),
                'poin' => 0,
                'rating' => 0.00,
            ]);
        } else {
            $user->update([
                'google_id' => $request->google_id,
                'foto_profil' => $request->foto_profil ?? $user->foto_profil,
                'is_verified' => true,
            ]);
        }

        $token = $user->createToken('google-auth')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'User synced successfully',
            'user' => $user,
            'token' => $token,
        ]);
    }
}