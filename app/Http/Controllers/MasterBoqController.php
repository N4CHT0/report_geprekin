<?php

namespace App\Http\Controllers;

use App\Models\MasterBoq;
use Illuminate\Http\Request;

class MasterBoqController extends Controller
{
    public function index()
    {
        $items = MasterBoq::orderBy('kategori')->orderBy('id')->get();
        return view('Surveyor.boq.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'required|string',
            'slug_id' => 'required|string|unique:master_boqs',
            'nama_item' => 'required|string',
            'harga_satuan' => 'required|numeric',
        ]);

        MasterBoq::create([
            'kategori' => $request->kategori,
            'slug_id' => $request->slug_id,
            'nama_item' => $request->nama_item,
            'harga_satuan' => $request->harga_satuan,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->back()->with('success', 'Item BOQ berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kategori' => 'required|string',
            'slug_id' => 'required|string|unique:master_boqs,slug_id,'.$id,
            'nama_item' => 'required|string',
            'harga_satuan' => 'required|numeric',
        ]);

        $item = MasterBoq::findOrFail($id);
        $item->update([
            'kategori' => $request->kategori,
            'slug_id' => $request->slug_id,
            'nama_item' => $request->nama_item,
            'harga_satuan' => $request->harga_satuan,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->back()->with('success', 'Item BOQ berhasil diupdate!');
    }

    public function destroy($id)
    {
        MasterBoq::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Item BOQ berhasil dihapus!');
    }
}
