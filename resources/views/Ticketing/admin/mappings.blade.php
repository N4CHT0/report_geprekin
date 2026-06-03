@extends('Ticketing.layouts.app')
@section('title','Mapping Ticketing')
@section('content')
<h1 class="text-2xl font-bold mb-5">Mapping Auto Assign</h1>
<form action="{{ route('ticketing.mappings.store') }}" method="POST" class="bg-white rounded-2xl border p-5 grid md:grid-cols-3 gap-3 mb-6">
    @csrf
    <input name="division" class="border rounded-xl px-3 py-2" placeholder="Divisi" required>
    <input name="ticket_type" class="border rounded-xl px-3 py-2" placeholder="Jenis Ticket" required>
    <input name="area" class="border rounded-xl px-3 py-2" placeholder="Area" required>
    <input name="item" class="border rounded-xl px-3 py-2" placeholder="Item opsional">
    <select name="pic_user_id" class="border rounded-xl px-3 py-2" required><option value="">Pilih PIC</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }} - {{ $u->role }}</option>@endforeach</select>
    <select name="vendor_user_id" class="border rounded-xl px-3 py-2"><option value="">Vendor opsional</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }} - {{ $u->role }}</option>@endforeach</select>
    <button class="md:col-span-3 px-4 py-2 rounded-xl bg-blue-600 text-white">Simpan Mapping</button>
</form>

<div class="bg-white rounded-2xl border overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-left"><tr><th class="p-3">Divisi</th><th>Jenis</th><th>Area</th><th>Item</th><th>PIC</th><th>Vendor</th><th></th></tr></thead>
        <tbody>
            @forelse($mappings as $m)
            <tr class="border-t">
                <td class="p-3">{{ $m->division }}</td><td>{{ $m->ticket_type }}</td><td>{{ $m->area }}</td><td>{{ $m->item ?? '*' }}</td><td>{{ $m->pic_name ?? '-' }}</td><td>{{ $m->vendor_name ?? '-' }}</td>
                <td><form method="POST" action="{{ route('ticketing.mappings.delete',$m->id) }}" onsubmit="return confirm('Hapus mapping?')">@csrf @method('DELETE')<button class="text-red-600">Hapus</button></form></td>
            </tr>
            @empty
            <tr><td colspan="7" class="p-6 text-center text-slate-500">Belum ada mapping.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
