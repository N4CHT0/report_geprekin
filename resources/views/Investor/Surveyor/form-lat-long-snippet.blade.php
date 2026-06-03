{{-- Tambahkan field latitude dan longitude di bagian Informasi Lokasi pada form.blade.php --}}

<div>
    <label class="text-sm font-semibold">
        Latitude
    </label>

    <input type="number"
           step="any"
           name="latitude"
           value="{{ old('latitude', $survey->latitude ?? '') }}"
           placeholder="-7.2575"
           class="w-full mt-2 border border-slate-300 rounded-xl px-4 py-3">
</div>

<div>
    <label class="text-sm font-semibold">
        Longitude
    </label>

    <input type="number"
           step="any"
           name="longitude"
           value="{{ old('longitude', $survey->longitude ?? '') }}"
           placeholder="112.7521"
           class="w-full mt-2 border border-slate-300 rounded-xl px-4 py-3">
</div>
