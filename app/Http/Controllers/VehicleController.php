<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleController extends Controller
{
    /**
     * Tampilkan daftar kendaraan dengan pencarian dan filter pelanggan.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $customerId = $request->input('customer_id');

        $vehicles = Vehicle::with('customer')
            ->when($customerId, function ($query, $customerId) {
                return $query->where('customer_id', $customerId);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('plate_number', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($cq) use ($search) {
                          $cq->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        return view('vehicles.index', compact('vehicles', 'search', 'customerId', 'customers'));
    }

    /**
     * Tampilkan formulir tambah kendaraan baru.
     */
    public function create(Request $request): View
    {
        $customerId = $request->input('customer_id');
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        return view('vehicles.create', compact('customers', 'customerId'));
    }

    /**
     * Simpan data kendaraan baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $currentYear = (int) date('Y');

        $validated = $request->validate([
            'customer_id'  => ['required', 'integer', 'exists:customers,id'],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')],
            'brand'        => ['required', 'string', 'max:100'],
            'model'        => ['required', 'string', 'max:100'],
            'year'         => ['nullable', 'integer', 'min:1900', 'max:' . ($currentYear + 1)],
        ], [
            'customer_id.required'  => 'Pemilik kendaraan (pelanggan) wajib dipilih.',
            'customer_id.exists'    => 'Pelanggan yang dipilih tidak valid.',
            'plate_number.required' => 'Nomor polisi kendaraan wajib diisi.',
            'plate_number.unique'   => 'Nomor polisi sudah terdaftar dalam sistem.',
            'plate_number.max'      => 'Nomor polisi maksimal 20 karakter.',
            'brand.required'        => 'Merek kendaraan wajib diisi.',
            'brand.max'             => 'Merek kendaraan maksimal 100 karakter.',
            'model.required'        => 'Model/tipe kendaraan wajib diisi.',
            'model.max'             => 'Model kendaraan maksimal 100 karakter.',
            'year.integer'          => 'Tahun pembuatan harus berupa angka.',
            'year.min'              => 'Tahun pembuatan minimal 1900.',
            'year.max'              => 'Tahun pembuatan tidak boleh melebihi tahun depan.',
        ]);

        $validated['plate_number'] = strtoupper(trim($validated['plate_number']));

        $vehicle = Vehicle::create($validated);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', "Kendaraan dengan plat \"{$vehicle->plate_number}\" berhasil ditambahkan.");
    }

    /**
     * Tampilkan detail kendaraan beserta data pemiliknya.
     */
    public function show(Vehicle $vehicle): View
    {
        $vehicle->load('customer');

        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * Tampilkan formulir edit kendaraan.
     */
    public function edit(Vehicle $vehicle): View
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        return view('vehicles.edit', compact('vehicle', 'customers'));
    }

    /**
     * Perbarui data kendaraan di database.
     */
    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $currentYear = (int) date('Y');

        $validated = $request->validate([
            'customer_id'  => ['required', 'integer', 'exists:customers,id'],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')->ignore($vehicle->id)],
            'brand'        => ['required', 'string', 'max:100'],
            'model'        => ['required', 'string', 'max:100'],
            'year'         => ['nullable', 'integer', 'min:1900', 'max:' . ($currentYear + 1)],
        ], [
            'customer_id.required'  => 'Pemilik kendaraan (pelanggan) wajib dipilih.',
            'customer_id.exists'    => 'Pelanggan yang dipilih tidak valid.',
            'plate_number.required' => 'Nomor polisi kendaraan wajib diisi.',
            'plate_number.unique'   => 'Nomor polisi sudah terdaftar dalam sistem.',
            'plate_number.max'      => 'Nomor polisi maksimal 20 karakter.',
            'brand.required'        => 'Merek kendaraan wajib diisi.',
            'brand.max'             => 'Merek kendaraan maksimal 100 karakter.',
            'model.required'        => 'Model/tipe kendaraan wajib diisi.',
            'model.max'             => 'Model kendaraan maksimal 100 karakter.',
            'year.integer'          => 'Tahun pembuatan harus berupa angka.',
            'year.min'              => 'Tahun pembuatan minimal 1900.',
            'year.max'              => 'Tahun pembuatan tidak boleh melebihi tahun depan.',
        ]);

        $validated['plate_number'] = strtoupper(trim($validated['plate_number']));

        $vehicle->update($validated);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', "Data kendaraan plat \"{$vehicle->plate_number}\" berhasil diperbarui.");
    }

    /**
     * Hapus data kendaraan.
     */
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $plate = $vehicle->plate_number;
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', "Kendaraan dengan plat \"{$plate}\" berhasil dihapus.");
    }
}

