<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CrewMenuController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'crew') {
            abort(403, 'Hanya crew yang dapat mengakses halaman ini.');
        }

        $outlet = null;

        if (!empty($user->outlet_id)) {
            $outlet = DB::table('tbl_outlets')
                ->select('id', 'nama_outlet', 'kode_outlet', 'kota')
                ->where('id', $user->outlet_id)
                ->first();
        }

        return view('Auth.crewMenus', compact('user', 'outlet'));
    }

    public function goFormulir()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'crew') {
            abort(403, 'Hanya crew yang dapat mengakses menu ini.');
        }

        return redirect()->route('master.dscFormulir.index');
    }

    public function goDailyChecklist()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'crew') {
            abort(403, 'Hanya crew yang dapat mengakses menu ini.');
        }

        session([
            'responden_id'       => $user->id,
            'responden_nama'     => $user->name,
            'responden_username' => $user->email,
            'crew_outlet_id'     => $user->outlet_id,
            'crew_role'          => $user->role,
        ]);

        return redirect()->route('auditDashboard.index');
    }
    
    public function goPurchaseOrder()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'crew') {
            abort(403, 'Hanya crew yang dapat mengakses menu ini.');
        }

        return redirect()->route('purchasing.dashboardOutlet');
    }

public function showProfileForm()
{
    $user = Auth::user();

    if (!$user || $user->role !== 'crew') {
        abort(403, 'Hanya crew yang dapat mengakses halaman ini.');
    }

    $outlet = null;

    if (!empty($user->outlet_id)) {
        $outlet = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet', 'kode_outlet', 'kota')
            ->where('id', $user->outlet_id)
            ->first();
    }

    return view('Auth.crewProfileForm', compact('user', 'outlet'));
}

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'crew') {
            abort(403, 'Hanya crew yang dapat mengakses halaman ini.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'name' => $request->name,
                'email' => $request->email,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('crew.menus.index')
            ->with('success', 'Data akun berhasil diperbarui.');
    }

    public function showPasswordForm()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'crew') {
            abort(403, 'Hanya crew yang dapat mengakses halaman ini.');
        }

        return view('Auth.crewPasswordForm', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'crew') {
            abort(403, 'Hanya crew yang dapat mengakses halaman ini.');
        }

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password lama tidak sesuai.'])
                ->withInput();
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => Hash::make($request->password),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('crew.menus.index')
            ->with('success', 'Password berhasil diubah.');
    }
}