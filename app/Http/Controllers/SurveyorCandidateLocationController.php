<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SurveyorCandidateLocationController extends Controller
{
    public function index()
    {
        $locations = DB::table('surveyor_candidate_locations')
            ->orderByDesc('id')
            ->get();

        return view('Surveyor.candidate.index', compact('locations'));
    }

    public function create()
    {
        return view('Surveyor.candidate.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lokasi' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'kota' => ['nullable', 'string', 'max:150'],
            'provinsi' => ['nullable', 'string', 'max:150'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'maps_url' => ['nullable', 'string'],
            'priority' => ['nullable', 'in:LOW,MEDIUM,HIGH'],
            'assigned_surveyor' => ['nullable', 'string', 'max:150'],
            'catatan_admin' => ['nullable', 'string'],
        ]);

        $lat = $request->latitude ? (float) $request->latitude : null;
        $lng = $request->longitude ? (float) $request->longitude : null;
        $mapsUrl = $request->maps_url;

        if (!$mapsUrl && $lat && $lng) {
            $mapsUrl = 'https://www.google.com/maps?q=' . $lat . ',' . $lng;
        }

        DB::table('surveyor_candidate_locations')->insert([
            'kode_lokasi' => 'LOC-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
            'nama_lokasi' => $request->nama_lokasi,
            'alamat' => $request->alamat,
            'kota' => $request->kota,
            'provinsi' => $request->provinsi,
            'latitude' => $lat,
            'longitude' => $lng,
            'maps_url' => $mapsUrl,
            'priority' => $request->priority ?: 'MEDIUM',
            'status' => 'NEW',
            'assigned_surveyor' => $request->assigned_surveyor,
            'catatan_admin' => $request->catatan_admin,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('investor.surveyor.candidate.index')
            ->with('success', 'Titik kandidat survey berhasil dibuat.');
    }

    public function markAssigned(int $id)
    {
        DB::table('surveyor_candidate_locations')
            ->where('id', $id)
            ->update([
                'status' => 'ASSIGNED',
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Titik survey ditandai assigned.');
    }

    public function assignment()
    {
        $locations = DB::table('surveyor_candidate_locations')
            ->orderByDesc('id')
            ->get();

        $stats = (object)[
            'unassigned' => $locations->where('status', 'NEW')->count(),
            'assigned' => $locations->where('status', 'ASSIGNED')->count(),
            'total' => $locations->count(),
        ];

        return view('Surveyor.candidate.assignment', compact('locations', 'stats'));
    }

    public function storeAssignment(Request $request, int $id)
    {
        $request->validate([
            'assigned_surveyor' => ['required', 'string', 'max:150'],
            'due_date' => ['nullable', 'date'],
        ]);

        DB::table('surveyor_candidate_locations')
            ->where('id', $id)
            ->update([
                'assigned_surveyor' => $request->assigned_surveyor,
                'status' => 'ASSIGNED',
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Surveyor berhasil ditugaskan ke lokasi ini.');
    }
}
