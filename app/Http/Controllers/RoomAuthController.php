<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RoomAuthController extends Controller
{
    // 1. Tampilkan Halaman Login Khusus HOSpace
    public function index()
    {
        // Pastikan nama file view-nya sesuai dengan yang kita buat sebelumnya
        return view('Room.users.login');
    }

    // 2. Proses Login HOSpace
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect langsung ke dashboard reservasi ruangan
            return redirect()->route('dashboard')->with('success', 'Selamat datang di HOSpace!');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    // 3. Proses Logout khusus HOSpace
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Kembalikan ke halaman login khusus HOSpace
        return redirect()->route('hospace.login');
    }

    // ==========================================
    // BAGIAN REGISTRASI KARYAWAN
    // ==========================================

    // Menampilkan halaman form register
    public function showRegister()
    {
        return view('Room.users.register');
    }

    // Memproses data pendaftaran
    public function processRegister(Request $request)
    {
        // 1. Validasi Input dan Domain Email
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                function ($attribute, $value, $fail) {
                    if (!str_ends_with(strtolower($value), '@geprekin.com')) {
                        $fail('Hanya email resmi perusahaan (@geprekin.com) yang diizinkan mendaftar.');
                    }
                }
            ],
            'password' => 'required|string|min:6|confirmed',
        ]);

        // 2. Simpan ke database langsung menggunakan Query Builder murni
        // Kita pakai insertGetId agar ID user yang baru terdaftar langsung kita dapatkan
        $userId = DB::table('users')->insertGetId([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => 'userhospace',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Login otomatis menggunakan ID user tersebut
        Auth::loginUsingId($userId);

        // 4. Lempar ke halaman dashboard
        return redirect()->route('dashboard')->with('success', 'Akun berhasil dibuat! Selamat datang di HOSpace.');
    }
}
