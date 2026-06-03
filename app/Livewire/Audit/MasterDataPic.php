<?php

namespace App\Livewire\Audit;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class MasterDataPic extends Component
{
    use WithPagination;

    // KEMBALI MENGGUNAKAN TAILWIND (Bawaan TALL Stack)
    protected $paginationTheme = 'tailwind';

    public $filterPic = '';
    public $filterLevel = '';
    public $filterOutlet = '';

    public $isModalOpen = false;
    public $isEditMode = false;

    public $pic_id;
    public $nama_lengkap;
    public $level_pic = '';
    public $old_level_pic = '';
    public $selected_outlets = [];

    public function updating($property)
    {
        if (in_array($property, ['filterPic', 'filterLevel', 'filterOutlet'])) {
            $this->resetPage();
        }
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm()
    {
        $this->pic_id = null;
        $this->nama_lengkap = '';
        $this->level_pic = '';
        $this->old_level_pic = '';
        $this->selected_outlets = [];
        $this->isEditMode = false;
    }

    public function editMapping($pic_id, $level_pic)
    {
        $this->resetValidation();
        $this->pic_id = $pic_id;
        $this->level_pic = $level_pic;
        $this->old_level_pic = $level_pic;
        $this->isEditMode = true;

        $pic = DB::table('tbl_pic')->where('id', $pic_id)->first();
        $this->nama_lengkap = $pic ? $pic->nama_lengkap : '';

        // Ambil data lama, pastikan tidak ada array kotor/kosong
        $this->selected_outlets = DB::table('tbl_pic_mapping')
            ->where('pic_id', $pic_id)
            ->where('level_pic', $level_pic)
            ->pluck('outlet_id')
            ->filter(fn($id) => !empty($id))
            ->map(fn($id) => (string) $id)
            ->toArray();

        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate([
            'nama_lengkap' => 'required|string|max:255',
            'level_pic' => 'required|in:LEADER,SPV,TM',
            'selected_outlets' => 'nullable|array',
        ], [
            'nama_lengkap.required' => 'Nama PIC wajib diisi.',
            'level_pic.required' => 'Level PIC wajib dipilih.',
        ]);

        // Sanitasi: Ubah ke murni Integer dan buang yang kosong
        $validOutlets = collect($this->selected_outlets)
            ->filter(fn($id) => !empty($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        DB::beginTransaction();
        try {
            if (!$this->isEditMode) {
                // ── INSERT DATA BARU ──
                $newPicId = DB::table('tbl_pic')->insertGetId([
                    'nama_lengkap' => $this->nama_lengkap,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Jika tidak ada outlet yang dipilih, simpan dengan outlet_id = null
                if ($validOutlets->isEmpty()) {
                    DB::table('tbl_pic_mapping')->insert([
                        'pic_id' => $newPicId,
                        'outlet_id' => null,
                        'level_pic' => $this->level_pic,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $insertData = $validOutlets->map(function ($outletId) use ($newPicId) {
                        return [
                            'pic_id' => $newPicId,
                            'outlet_id' => $outletId,
                            'level_pic' => $this->level_pic,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    })->toArray();
                    DB::table('tbl_pic_mapping')->insert($insertData);
                }

                session()->flash('success', 'Data PIC dan mapping berhasil ditambahkan.');
            } else {
                // ── UPDATE DATA EKSISTING ──
                DB::table('tbl_pic')
                    ->where('id', $this->pic_id)
                    ->update(['nama_lengkap' => $this->nama_lengkap, 'updated_at' => now()]);

                // Hapus mapping level lama jika level diganti
                if (!empty($this->old_level_pic)) {
                    DB::table('tbl_pic_mapping')
                        ->where('pic_id', $this->pic_id)
                        ->where('level_pic', $this->old_level_pic)
                        ->delete();
                }

                // Bersihkan mapping level saat ini
                DB::table('tbl_pic_mapping')
                    ->where('pic_id', $this->pic_id)
                    ->where('level_pic', $this->level_pic)
                    ->delete();

                // Insert kembali. Jika Hapus Semua ditekan, sisakan role-nya dengan outlet = null
                if ($validOutlets->isEmpty()) {
                    DB::table('tbl_pic_mapping')->insert([
                        'pic_id' => $this->pic_id,
                        'outlet_id' => null,
                        'level_pic' => $this->level_pic,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $insertData = $validOutlets->map(function ($outletId) {
                        return [
                            'pic_id' => $this->pic_id,
                            'outlet_id' => $outletId,
                            'level_pic' => $this->level_pic,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    })->toArray();
                    DB::table('tbl_pic_mapping')->insert($insertData);
                }

                session()->flash('success', 'Data mapping PIC berhasil diperbarui.');
            }

            DB::commit();
            $this->closeModal();
        } catch (\Throwable $e) {
            DB::rollBack();
            // Lemparkan error agar ditangkap oleh UI Modal
            $this->addError('database', 'Terjadi kesalahan SQL: ' . $e->getMessage());
        }
    }

    public function deleteMapping($pic_id, $level_pic)
    {
        DB::table('tbl_pic_mapping')->where('pic_id', $pic_id)->where('level_pic', $level_pic)->delete();
        session()->flash('success', 'Data mapping PIC berhasil dihapus.');
    }

    public function selectAllOutlets()
    {
        $this->selected_outlets = collect($this->allOutlets)->pluck('id')->map(fn($id) => (string)$id)->toArray();
    }

    public function removeAllOutlets()
    {
        $this->selected_outlets = [];
    }

    #[Computed]
    public function dropdownPics()
    {
        return DB::table('tbl_pic')->orderBy('nama_lengkap')->pluck('nama_lengkap')->unique()->filter();
    }

    #[Computed]
    public function dropdownOutlets()
    {
        return collect($this->allOutlets)->pluck('nama_outlet')->unique()->filter();
    }

    #[Computed]
    public function allOutlets()
    {
        return DB::table('tbl_outlets')->select('id', 'nama_outlet')->orderBy('nama_outlet')->get();
    }

    #[Computed]
    public function outletsMap()
    {
        return collect($this->allOutlets)->pluck('nama_outlet', 'id')->toArray();
    }

    #[Computed]
    public function stats()
    {
        return [
            'totalData' => DB::table('tbl_pic_mapping')->count(),
            'totalPic' => DB::table('tbl_pic_mapping')->distinct('pic_id')->count('pic_id'),
            'totalOutlet' => DB::table('tbl_pic_mapping')->distinct('outlet_id')->count('outlet_id'),
        ];
    }

    public function render()
    {
        $query = DB::table('tbl_pic_mapping as m')
            ->leftJoin('tbl_pic as p', 'm.pic_id', '=', 'p.id')
            ->select(
                'm.pic_id',
                'm.level_pic',
                'p.nama_lengkap',
                DB::raw('GROUP_CONCAT(m.outlet_id) as outlet_ids')
            )
            ->groupBy('m.pic_id', 'm.level_pic', 'p.nama_lengkap');

        if (!empty($this->filterPic)) {
            $query->where('p.nama_lengkap', $this->filterPic);
        }

        if (!empty($this->filterLevel)) {
            $query->where('m.level_pic', $this->filterLevel);
        }

        if (!empty($this->filterOutlet)) {
            $query->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('tbl_pic_mapping as m2')
                    ->join('tbl_outlets as o2', 'm2.outlet_id', '=', 'o2.id')
                    ->whereColumn('m2.pic_id', 'm.pic_id')
                    ->whereColumn('m2.level_pic', 'm.level_pic')
                    ->where('o2.nama_outlet', $this->filterOutlet);
            });
        }

        return view('livewire.audit.master-data-pic', [
            'data' => $query->orderBy('p.nama_lengkap', 'asc')->paginate(10),
        ]);
    }
}
