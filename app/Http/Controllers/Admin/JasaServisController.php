<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JasaServis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JasaServisController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $jasaServis = JasaServis::when($search, function ($query, $search) {
                return $query->where('kode_jasa', 'like', "%{$search}%")
                    ->orWhere('nama_jasa', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.jasa.index', compact('jasaServis', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_jasa' => ['required', 'string', 'max:50', 'unique:jasa_servis,kode_jasa'],
            'nama_jasa' => ['required', 'string', 'max:255'],
            'harga' => ['required', 'numeric', 'min:0'],
        ], [
            'kode_jasa.required' => 'Kode jasa wajib diisi.',
            'kode_jasa.unique' => 'Kode jasa tersebut sudah ada.',
            'nama_jasa.required' => 'Nama jasa servis wajib diisi.',
            'harga.required' => 'Harga tarif servis wajib diisi.',
            'harga.min' => 'Harga tarif tidak boleh negatif.',
        ]);

        JasaServis::create([
            'kode_jasa' => strtoupper($validated['kode_jasa']),
            'nama_jasa' => $validated['nama_jasa'],
            'harga' => $validated['harga'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Jasa servis berhasil ditambahkan.');
    }

    public function update(Request $request, JasaServis $jasa): RedirectResponse
    {
        $validated = $request->validate([
            'kode_jasa' => ['required', 'string', 'max:50', Rule::unique('jasa_servis', 'kode_jasa')->ignore($jasa->id)],
            'nama_jasa' => ['required', 'string', 'max:255'],
            'harga' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ], [
            'kode_jasa.required' => 'Kode jasa wajib diisi.',
            'nama_jasa.required' => 'Nama jasa servis wajib diisi.',
            'harga.required' => 'Harga tarif servis wajib diisi.',
        ]);

        $jasa->update([
            'kode_jasa' => strtoupper($validated['kode_jasa']),
            'nama_jasa' => $validated['nama_jasa'],
            'harga' => $validated['harga'],
            'is_active' => $validated['is_active'],
        ]);

        return back()->with('success', 'Data jasa servis berhasil diperbarui.');
    }

    public function destroy(JasaServis $jasa): RedirectResponse
    {
        $jasa->update(['is_active' => false]);

        return back()->with('success', 'Status jasa servis berhasil diubah menjadi nonaktif.');
    }
}

