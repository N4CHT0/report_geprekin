<?php

namespace App\Livewire\Audit;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class MasterDataPertanyaan extends Component
{
    use WithPagination;

    // Menggunakan template Tailwind bawaan TALL Stack
    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $isModalOpen = false;
    public $isEditMode = false;

    // State Form
    public $pertanyaan_id;
    public $pertanyaan = '';
    public $jam = '';

    // Reset paginasi jika user mengetik di kotak pencarian
    public function updatingSearch()
    {
        $this->resetPage();
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
        $this->pertanyaan_id = null;
        $this->pertanyaan = '';
        $this->jam = '';
        $this->isEditMode = false;
    }

    public function edit($id)
    {
        $this->resetValidation();

        $data = DB::table('tbl_pertanyaan_dcr')->where('id', $id)->first();

        if ($data) {
            $this->pertanyaan_id = $data->id;
            $this->pertanyaan = $data->pertanyaan;
            $this->jam = $data->jam;
            $this->isEditMode = true;
            $this->isModalOpen = true;
        }
    }

    public function save()
    {
        $this->validate([
            'pertanyaan' => 'required|string',
            'jam' => 'required',
        ], [
            'pertanyaan.required' => 'Pertanyaan wajib diisi.',
            'jam.required' => 'Jam target wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            if ($this->isEditMode) {
                DB::table('tbl_pertanyaan_dcr')
                    ->where('id', $this->pertanyaan_id)
                    ->update([
                        'pertanyaan' => $this->pertanyaan,
                        'jam' => $this->jam,
                        'updated_at' => now(),
                    ]);
                session()->flash('success', 'Pertanyaan berhasil diperbarui.');
            } else {
                DB::table('tbl_pertanyaan_dcr')->insert([
                    'pertanyaan' => $this->pertanyaan,
                    'jam' => $this->jam,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                session()->flash('success', 'Pertanyaan berhasil ditambahkan.');
            }

            DB::commit();
            $this->closeModal();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->addError('database', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::table('tbl_pertanyaan_dcr')->where('id', $id)->delete();
        session()->flash('success', 'Pertanyaan berhasil dihapus.');
    }

    #[Computed]
    public function questions()
    {
        return DB::table('tbl_pertanyaan_dcr')
            ->when($this->search, function ($query) {
                $query->where('pertanyaan', 'like', '%' . $this->search . '%')
                    ->orWhere('jam', 'like', '%' . $this->search . '%');
            })
            ->orderBy('jam', 'asc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.audit.master-data-pertanyaan');
    }
}
