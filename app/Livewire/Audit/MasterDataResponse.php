<?php

namespace App\Livewire\Audit;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class DataResponses extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $tanggal_awal = '';
    public $tanggal_akhir = '';
    public $responden = '';
    public $outlet = '';

    public function updating($property)
    {
        if (in_array($property, ['tanggal_awal', 'tanggal_akhir', 'responden', 'outlet'])) {
            $this->resetPage();
        }
    }

    public function resetFilter()
    {
        $this->tanggal_awal = '';
        $this->tanggal_akhir = '';
        $this->responden = '';
        $this->outlet = '';
        $this->resetPage();
    }

    #[Computed]
    public function outletsList()
    {
        return DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet')
            ->get();
    }

    #[Computed]
    public function respondenList()
    {
        return DB::table('tbl_user_responden')
            ->select('id', 'nama_lengkap')
            ->orderBy('nama_lengkap')
            ->get();
    }

    // Fungsi normalisasi foto URL yang diambil dari AuditController Anda
    private function normalizeAuditPhotoUrls($rawValue): array
    {
        if (empty($rawValue)) return [];
        $paths = is_array($rawValue) ? $rawValue : (json_decode(trim($rawValue), true) ?: [trim($rawValue)]);

        $urls = [];
        foreach ($paths as $p) {
            $p = trim((string) $p);
            if ($p !== '') {
                $urls[] = filter_var($p, FILTER_VALIDATE_URL) ? $p : asset('storage/' . ltrim(preg_replace('#^(public/|storage/)#', '', str_replace(['\\/', '\\'], '/', $p)), '/'));
            }
        }
        return $urls;
    }

    public function deleteData($id)
    {
        // Fungsi Hapus yang aslinya di AuditController di-porting ke sini
        DB::table('audit_harian')->where('id', $id)->delete();
        session()->flash('success', 'Data response berhasil dihapus.');
    }

    public function render()
    {
        $query = DB::table('audit_harian as ah')
            ->leftJoin('tbl_user_responden as ur', 'ah.id_responden', '=', 'ur.id')
            ->leftJoin('tbl_outlets as o', 'ah.outlet_id', '=', 'o.id');

        if (!empty($this->tanggal_awal) && !empty($this->tanggal_akhir)) {
            $query->whereBetween('ah.tanggal', [$this->tanggal_awal, $this->tanggal_akhir]);
        } elseif (!empty($this->tanggal_awal)) {
            $query->where('ah.tanggal', '>=', $this->tanggal_awal);
        } elseif (!empty($this->tanggal_akhir)) {
            $query->where('ah.tanggal', '<=', $this->tanggal_akhir);
        }

        if (!empty($this->responden)) {
            $query->where('ah.id_responden', $this->responden);
        }

        if (!empty($this->outlet)) {
            $query->where('ah.outlet_id', $this->outlet);
        }

        $data = $query->select(
            'ah.id',
            'ah.tanggal',
            'ah.jam_aktivitas',
            'ah.outlet_id',
            'ah.id_responden',
            'ah.nama_pic',
            'ah.pertanyaan',
            'ah.jawaban',
            'ah.alasan',
            'ah.foto',
            'ah.foto_perbaikan',
            'ah.created_at',
            'o.nama_outlet',
            DB::raw('COALESCE(NULLIF(ur.nama_lengkap, ""), NULLIF(ah.nama_pic, "")) as pic_nama')
        )
            ->orderByDesc('ah.tanggal')
            ->orderByDesc('ah.created_at')
            ->paginate(10);

        // Memproses Foto seperti di controller Anda
        $data->getCollection()->transform(function ($item) {
            $item->foto_urls = $this->normalizeAuditPhotoUrls($item->foto);
            return $item;
        });

        return view('livewire.audit.data-responses', [
            'data' => $data
        ]);
    }
}
