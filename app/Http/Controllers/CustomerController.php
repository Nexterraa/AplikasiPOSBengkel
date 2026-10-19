<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Tampilkan daftar pelanggan dengan pencarian dan pagination.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $customers = Customer::withCount('vehicles')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    /**
     * Tampilkan formulir tambah pelanggan baru.
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Simpan data pelanggan baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ], [
            'name.required'  => 'Nama pelanggan wajib diisi.',
            'name.max'       => 'Nama pelanggan maksimal 255 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.max'      => 'Nomor telepon maksimal 20 karakter.',
        ]);

        $customer = Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', "Pelanggan \"{$customer->name}\" berhasil ditambahkan.");
    }

    /**
     * Tampilkan detail data pelanggan beserta daftar kendaraannya.
     */
    public function show(Customer $customer): View
    {
        $customer->load('vehicles');

        return view('customers.show', compact('customer'));
    }

    /**
     * Tampilkan formulir edit data pelanggan.
     */
    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Perbarui data pelanggan di database.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ], [
            'name.required'  => 'Nama pelanggan wajib diisi.',
            'name.max'       => 'Nama pelanggan maksimal 255 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.max'      => 'Nomor telepon maksimal 20 karakter.',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', "Data pelanggan \"{$customer->name}\" berhasil diperbarui.");
    }

    /**
     * Hapus data pelanggan dengan penanganan relasi yang aman.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->vehicles()->exists()) {
            return back()->with('error', "Pelanggan \"{$customer->name}\" tidak dapat dihapus karena masih memiliki data kendaraan terkait. Hapus kendaraan terlebih dahulu.");
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', "Data pelanggan \"{$customer->name}\" berhasil dihapus.");
    }
}

