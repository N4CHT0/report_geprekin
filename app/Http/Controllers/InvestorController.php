<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class InvestorController extends Controller
{
    // public function edit($id)
    // {
    // //     $investor = User::findOrFail($id);
    // //     return view('Investor.Profile.editProfile', compact('investor'));
    
    //     $investor = Users::where('id', $id)->first();
    //     return view('Investor.Profile.editProfile', compact('investor'));
    // }
        public function edit($id)
    {
        $user = Users::findOrFail($id);
    
        return view('Investor.Profile.editProfile', compact('user'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        //$investor = Users::where('id', $id)->firstOrFail();
        $user = Users::findOrFail($id);
        
        $user->name  = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            //$user->password = Hash::make($request->password);
            $user->password = bcrypt($request->password);
        }

        // $investor->update([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'password' => $request->password,
        // ]);
        
        $user->save();
        $user->refresh();
        Auth::setUser($user);
        //auth()->user()->refresh();
        return redirect()
        ->route('investor.profile.edit', $user->id)
        ->with('success', 'Profil berhasil diperbarui');


        // return redirect()
        //     ->route('investor.sales.dashboard')
        //     ->with('success', 'Profile berhasil diperbarui');
    }
}
    // public function update(Request $request, $id)
    //{
    //     $request->validate([
    //         'name' => 'required',
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);
        
    //     $user = User::findOrFail($id);
        
    //      // SIMPAN DATA
    //     $user->name  = $request->name;
    //     $user->email = $request->email;

    //     if ($request->password) {
    //         $user->password = Hash::make($request->password);
    //     }

    //     // ⬅️ INI YANG BENAR-BENAR MENYIMPAN KE DATABASE
    //     $request->save();

    //     return redirect()
    //         ->route('investor.sales.dashboard')
    //         ->with('success', 'Profil berhasil diperbarui');

        // Users::where('id', $id)->update([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'password' => $request->password,
        // ]);
        // Users::where('id', $id)->update([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'password' => $request->password,
        // ]);
        
        // $request->save();

        // return redirect()
        //     ->route('investor.sales.dashboard')
        //     ->with('success', 'Profile berhasil diperbarui');