<?php

namespace App\Livewire\Audit;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class MasterDataOutlet extends Component
{
    use WithPagination;

    // Tetapkan tema pagination ke Tailwind murni
    protected $paginationTheme = 'tailwind';

    public $keyword = '';
    public $kota = '';
    public $status = '';

    // Reset pagination ketika filter berubah
    public function updating($property)
    {
        if (in_array($property, ['keyword', 'kota', 'status'])) {
            $this->resetPage();
        }
    }

    public function resetFilter()
    {
        $this->keyword = '';
        $this->kota = '';
        $this->status = '';
        $this->resetPage();
    }

    #[Computed]
    public function kotaList()
    {
        return DB::table('tbl_outlets')
            ->select('kota')
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->distinct()
            ->orderBy('kota', 'asc')
            ->get();
    }

    public function render()
    {
        $query = DB::table('tbl_outlets')->orderBy('nama_outlet', 'asc');

        if (!empty($this->kota)) {
            $query->where('kota', $this->kota);
        }

        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        if (!empty($this->keyword)) {
            $query->where(function ($q) {
                $q->where('nama_outlet', 'like', '%' . $this->keyword . '%')
                    ->orWhere('kode_outlet', 'like', '%' . $this->keyword . '%')
                    ->orWhere('alamat', 'like', '%' . $this->keyword . '%')
                    ->orWhere('kota', 'like', '%' . $this->keyword . '%');
            });
        }

        return view('livewire.audit.master-data-outlet', [
            'data' => $query->paginate(20)
        ]);
    }
}
