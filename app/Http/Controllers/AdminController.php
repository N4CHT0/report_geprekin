<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    public function users()
    {
        $users = DB::table('ticket_users')->orderBy('role')->orderBy('name')->get();
        return view('admin.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'=>['required','max:120'], 'email'=>['required','email','unique:ticket_users,email'],
            'role'=>['required','in:admin,pic,vendor,pelapor'], 'division'=>['nullable','max:100'], 'area'=>['nullable','max:100'],
            'password'=>['required','min:6'],
        ]);
        DB::table('ticket_users')->insert([
            'name'=>$data['name'], 'email'=>$data['email'], 'role'=>$data['role'], 'division'=>$data['division'] ?? null,
            'area'=>$data['area'] ?? null, 'password'=>Hash::make($data['password']), 'is_active'=>true,
            'created_at'=>now(), 'updated_at'=>now(),
        ]);
        return back()->with('success','User dibuat.');
    }

    public function toggleUser(int $user)
    {
        $row = DB::table('ticket_users')->where('id',$user)->first();
        abort_if(!$row, 404);
        DB::table('ticket_users')->where('id',$user)->update(['is_active'=>!$row->is_active, 'updated_at'=>now()]);
        return back()->with('success','Status user diubah.');
    }

    public function mappings()
    {
        $mappings = DB::table('ticket_mappings')
            ->leftJoin('ticket_users as pic','pic.id','=','ticket_mappings.pic_user_id')
            ->leftJoin('ticket_users as vendor','vendor.id','=','ticket_mappings.vendor_user_id')
            ->select('ticket_mappings.*','pic.name as pic_name','vendor.name as vendor_name')
            ->orderByDesc('ticket_mappings.id')->get();
        $users = DB::table('ticket_users')->where('is_active',true)->whereIn('role',['pic','vendor'])->get();
        return view('admin.mappings', compact('mappings','users'));
    }

    public function storeMapping(Request $request)
    {
        $data = $request->validate([
            'division'=>['required','max:100'], 'ticket_type'=>['required','max:100'], 'area'=>['required','max:100'],
            'item'=>['nullable','max:150'], 'pic_user_id'=>['required','integer'], 'vendor_user_id'=>['nullable','integer'],
        ]);
        DB::table('ticket_mappings')->insert(array_merge($data, ['created_at'=>now(), 'updated_at'=>now()]));
        Cache::forget('ticket:lookups');
        return back()->with('success','Mapping dibuat.');
    }

    public function deleteMapping(int $mapping)
    {
        DB::table('ticket_mappings')->where('id',$mapping)->delete();
        return back()->with('success','Mapping dihapus.');
    }
}
