<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Sparepart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SparepartController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $kategoriId = $request->input('kategori_id');

        $spareparts = Sparepart::with('kategori')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('kode_sparepart', 'like', "%{$search}%")
                        ->orWhere('nama_sparepart', 'like', "%{$search}%")
                        ->orWhere('merek', 'like', "%{$search}%");
                });
            })
            ->when($kategoriId, function ($query, $kategoriId) {
                return $query->where('kategori_id', $kategoriId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $kategoris = Kategori::where('is_active', true)->orderBy('nama_kategori')->get();

        return view('admin.sparepart.index', compact('spareparts', 'kategoris', 'search', 'kategoriId'));
    }

    public function create(): View
    {
        $kategoris = Kategori::where('is_active', true)->orderBy('nama_kategori')->get();

        return view('admin.sparepart.create', compact('kategoris'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_sparepart' => ['required', 'string', 'max:50', 'unique:spareparts,kode_sparepart'],
            'nama_sparepart' => ['required', 'string', 'max:255'],
            'merek' => ['nullable', 'string', 'max:100'],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0', 'gte:harga_beli'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
        ], [
            'kode_sparepart.required' => 'Kode sparepart wajib diisi.',
            'kode_sparepart.unique' => 'Kode sparepart tersebut sudah digunakan.',
            'nama_sparepart.required' => 'Nama sparepart wajib diisi.',
            'kategori_id.required' => 'Kategori wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            'harga_beli.required' => 'Harga beli wajib diisi.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'harga_jual.gte' => 'Harga jual tidak boleh lebih kecil dari harga beli.',
            'stok.required' => 'Stok awal wajib diisi.',
            'stok_minimum.required' => 'Stok minimum wajib diisi.',
        ]);

        Sparepart::create([
            'kode_sparepart' => strtoupper($validated['kode_sparepart']),
            'nama_sparepart' => $validated['nama_sparepart'],
            'merek' => $validated['merek'],
            'kategori_id' => $validated['kategori_id'],
            'harga_beli' => $validated['harga_beli'],
            'harga_jual' => $validated['harga_jual'],
            'stok' => $validated['stok'],
            'stok_minimum' => $validated['stok_minimum'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.spareparts.index')->with('success', 'Data sparepart berhasil ditambahkan.');
    }

    public function edit(Sparepart $sparepart): View
    {
        $kategoris = Kategori::where('is_active', true)->orderBy('nama_kategori')->get();

        return view('admin.sparepart.edit', compact('sparepart', 'kategoris'));
    }

    public function update(Request $request, Sparepart $sparepart): RedirectResponse
    {
        $validated = $request->validate([
            'kode_sparepart' => ['required', 'string', 'max:50', Rule::unique('spareparts', 'kode_sparepart')->ignore($sparepart->id)],
            'nama_sparepart' => ['required', 'string', 'max:255'],
            'merek' => ['nullable', 'string', 'max:100'],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0', 'gte:harga_beli'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ], [
            'kode_sparepart.required' => 'Kode sparepart wajib diisi.',
            'kode_sparepart.unique' => 'Kode sparepart tersebut sudah digunakan.',
            'nama_sparepart.required' => 'Nama sparepart wajib diisi.',
            'harga_jual.gte' => 'Harga jual tidak boleh lebih kecil dari harga beli.',
        ]);

        // Perubahan stok tidak diperbolehkan melalui form edit produk (sesuai aturan PRD Tahap 4 & 5)
        $sparepart->update([
            'kode_sparepart' => strtoupper($validated['kode_sparepart']),
            'nama_sparepart' => $validated['nama_sparepart'],
            'merek' => $validated['merek'],
            'kategori_id' => $validated['kategori_id'],
            'harga_beli' => $validated['harga_beli'],
            'harga_jual' => $validated['harga_jual'],
            'stok_minimum' => $validated['stok_minimum'],
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->route('admin.spareparts.index')->with('success', 'Data sparepart berhasil diperbarui.');
    }

    public function destroy(Sparepart $sparepart): RedirectResponse
    {
        $sparepart->update(['is_active' => false]);

        return back()->with('success', 'Status sparepart berhasil diubah menjadi nonaktif.');
    }
}

