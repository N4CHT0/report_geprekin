<!doctype html><html><head><meta charset="utf-8"><title>Print Ticketing</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="p-6" onload="window.print()">
<h1 class="text-xl font-bold mb-4">Rekap Ticketing</h1>
<table class="w-full text-xs border-collapse" border="1">
<thead><tr class="bg-slate-100"><th>No</th><th>Outlet</th><th>Area</th><th>Divisi</th><th>Item</th><th>Status</th><th>Priority</th><th>Dibuat</th></tr></thead>
<tbody>@foreach($tickets as $t)<tr><td>{{ $t->ticket_number }}</td><td>{{ $t->outlet_name }}</td><td>{{ $t->area }}</td><td>{{ $t->division }}</td><td>{{ $t->item }}</td><td>{{ $t->status }}</td><td>{{ $t->priority }}</td><td>{{ $t->created_at }}</td></tr>@endforeach</tbody>
</table>
</body></html>
