<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mekanik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MekanikController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $mekaniks = Mekanik::when($search, function ($query, $search) {
                return $query->where('nama_mekanik', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.mekanik.index', compact('mekaniks', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_mekanik' => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:20'],
        ], [
            'nama_mekanik.required' => 'Nama mekanik wajib diisi.',
        ]);

        Mekanik::create([
            'nama_mekanik' => $validated['nama_mekanik'],
            'telepon' => $validated['telepon'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Data mekanik berhasil ditambahkan.');
    }

    public function update(Request $request, Mekanik $mekanik): RedirectResponse
    {
        $validated = $request->validate([
            'nama_mekanik' => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'is_active' => ['required', 'boolean'],
        ], [
            'nama_mekanik.required' => 'Nama mekanik wajib diisi.',
        ]);

        $mekanik->update($validated);

        return back()->with('success', 'Data mekanik berhasil diperbarui.');
    }

    public function destroy(Mekanik $mekanik): RedirectResponse
    {
        $mekanik->update(['is_active' => false]);

        return back()->with('success', 'Status mekanik berhasil diubah menjadi nonaktif.');
    }
}

