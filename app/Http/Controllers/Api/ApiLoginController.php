<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Pastikan model User diimpor
use Illuminate\Support\Facades\Hash; // Untuk cek password
use Illuminate\Validation\ValidationException; // Untuk menangani error validasi

class ApiLoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string', // Nama perangkat untuk token (mis. 'postman-test')
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
            ]);
        }

        // Hapus token lama dengan nama perangkat yang sama jika ada (opsional, tapi baik untuk kebersihan)
        // $user->tokens()->where('name', $request->device_name)->delete();

        // Buat token baru
        $token = $user->createToken($request->device_name);

        return response()->json([
            'message' => 'Login berhasil',
            'user' => [ // Opsional: kirim data user jika perlu
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token->plainTextToken, // Ini token yang akan Anda gunakan
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil logout dari perangkat ini']);
    }
}