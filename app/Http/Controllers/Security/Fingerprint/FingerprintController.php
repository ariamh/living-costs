<?php

namespace App\Http\Controllers\Security\Fingerprint;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FingerprintController extends Controller
{
    /**
     * Generate device fingerprint hash dari request.
     */
    protected function getDeviceFingerprint(Request $request): string
    {
        return hash(
            'sha256',
            $request->header('User-Agent') .
                $request->ip() .
                $request->header('Accept-Language')
        );
    }

    /**
     * Simulasi login dengan pemeriksaan fingerprint device.
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);


        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // $request->session()->regenerate(); // untuk keamanan session hijack

        $user = User::find(Auth::id());

        $fingerprint = $this->getDeviceFingerprint($request);

        if ($user->device_fingerprint && $user->device_fingerprint !== $fingerprint) {
            // Fingerprint tidak cocok → anggap sebagai device baru
            return response()->json([
                'message' => 'Device baru terdeteksi, verifikasi ulang diperlukan.'
            ], 403);
        }

        // Fingerprint belum tersimpan → simpan fingerprint saat pertama kali login
        if (is_null($user->device_fingerprint)) {
            $user->device_fingerprint = $fingerprint;
            $user->save();
        }

        return response()->json([
            'message' => 'Login sukses, fingerprint cocok!',
            'user' => $user->only('id', 'name', 'email'),
        ]);
    }
}
