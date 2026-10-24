<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\JasaServis;
use App\Models\Mekanik;
use App\Models\Sparepart;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Tampilkan daftar transaksi bengkel.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $date = $request->input('date');

        $transactions = Transaction::with(['customer', 'vehicle', 'mekanik', 'user'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('no_transaksi', 'like', "%{$search}%")
                        ->orWhere('no_invoice', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('vehicle', function ($vq) use ($search) {
                            $vq->where('plate_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($date, function ($query, $date) {
                return $query->whereDate('created_at', $date);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('transactions.index', compact('transactions', 'search', 'status', 'date'));
    }

    /**
     * Tampilkan form pembuatan transaksi baru.
     */
    public function create(Request $request): View
    {
        $mechanics = Mekanik::where('is_active', true)->orderBy('nama_mekanik')->get();
        $services = JasaServis::where('is_active', true)->orderBy('nama_jasa')->get();
        $spareparts = Sparepart::where('is_active', true)->where('stok', '>', 0)->orderBy('nama_sparepart')->get();
        $customers = Customer::with('vehicles')->orderBy('name')->get();
        $vehicles = Vehicle::with('customer')->orderBy('plate_number')->get();

        $selectedVehicleId = $request->input('vehicle_id');
        $selectedCustomerId = $request->input('customer_id');

        return view('transactions.create', compact(
            'mechanics',
            'services',
            'spareparts',
            'customers',
            'vehicles',
            'selectedVehicleId',
            'selectedCustomerId'
        ));
    }

    /**
     * Simpan transaksi baru ke database secara atomik.
     */
    public function store(Request $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            // 1. Tangani Pelanggan dan Kendaraan
            $customerId = $request->input('customer_id');
            $vehicleId = $request->input('vehicle_id');
            $plateNumberInput = $request->input('plate_number');

            $vehicle = null;

            if ($vehicleId) {
                $vehicle = Vehicle::with('customer')->findOrFail($vehicleId);
                $customerId = $vehicle->customer_id;
            } elseif ($plateNumberInput) {
                $normalizedPlate = strtoupper(trim($plateNumberInput));
                $vehicle = Vehicle::with('customer')->where('plate_number', $normalizedPlate)->first();

                if ($vehicle) {
                    $customerId = $vehicle->customer_id;
                } else {
                    // Kendaraan baru
                    if (!$customerId) {
                        $request->validate([
                            'customer_name' => ['required', 'string', 'max:255'],
                            'customer_phone' => ['nullable', 'string', 'max:30'],
                        ], [
                            'customer_name.required' => 'Nama pemilik wajib diisi untuk pelanggan baru.',
                        ]);

                        $customer = Customer::create([
                            'name' => $request->input('customer_name'),
                            'phone' => $request->input('customer_phone'),
                        ]);
                        $customerId = $customer->id;
                    }

                    $request->validate([
                        'brand' => ['required', 'string', 'max:100'],
                        'model' => ['required', 'string', 'max:100'],
                        'year' => ['nullable', 'integer', 'min:1900', 'max:' . ((int) date('Y') + 1)],
                    ], [
                        'brand.required' => 'Merek kendaraan wajib diisi untuk kendaraan baru.',
                        'model.required' => 'Model kendaraan wajib diisi untuk kendaraan baru.',
                    ]);

                    $vehicle = Vehicle::create([
                        'customer_id' => $customerId,
                        'plate_number' => $normalizedPlate,
                        'brand' => $request->input('brand'),
                        'model' => $request->input('model'),
                        'year' => $request->input('year'),
                    ]);
                }
            } else {
                throw ValidationException::withMessages([
                    'plate_number' => ['Kendaraan atau nomor plat wajib dipilih/diisi.'],
                ]);
            }

            // 2. Parse dan validasi items
            $jasaItems = $request->input('jasa_items', []);
            $sparepartItems = $request->input('sparepart_items', []);

            // Filter out empty rows
            $cleanJasa = [];
            foreach ($jasaItems as $item) {
                if (!empty($item['jasa_id'])) {
                    $cleanJasa[] = $item;
                }
            }

            $cleanSpareparts = [];
            foreach ($sparepartItems as $item) {
                if (!empty($item['sparepart_id'])) {
                    $cleanSpareparts[] = $item;
                }
            }

            if (empty($cleanJasa) && empty($cleanSpareparts)) {
                throw ValidationException::withMessages([
                    'items' => ['Transaksi harus memiliki minimal satu jasa servis atau satu sparepart.'],
                ]);
            }

            // 3. Validasi Mekanik
            $mekanikId = $request->input('mekanik_id');
            if (!empty($cleanJasa)) {
                if (!$mekanikId) {
                    throw ValidationException::withMessages([
                        'mekanik_id' => ['Mekanik wajib dipilih jika transaksi memiliki jasa servis.'],
                    ]);
                }
            }

            if ($mekanikId) {
                $mekanik = Mekanik::find($mekanikId);
                if (!$mekanik || !$mekanik->is_active) {
                    throw ValidationException::withMessages([
                        'mekanik_id' => ['Mekanik yang dipilih tidak aktif atau tidak ditemukan.'],
                    ]);
                }
            } else {
                $mekanikId = null;
            }

            // 4. Hitung subtotal dan validasi harga/stok di server
            $detailsData = [];
            $grandTotal = 0;

            // Proses Jasa Servis
            foreach ($cleanJasa as $item) {
                $jasa = JasaServis::findOrFail($item['jasa_id']);
                if (!$jasa->is_active) {
                    throw ValidationException::withMessages([
                        'jasa_items' => ["Jasa servis '{$jasa->nama_jasa}' saat ini tidak aktif."],
                    ]);
                }

                $qty = isset($item['qty']) ? (int) $item['qty'] : 1;
                if ($qty < 1) {
                    $qty = 1;
                }

                $price = (float) $jasa->harga;
                $subtotal = $price * $qty;
                $grandTotal += $subtotal;

                $detailsData[] = [
                    'item_type' => 'jasa',
                    'jasa_servis_id' => $jasa->id,
                    'sparepart_id' => null,
                    'nama_item' => $jasa->nama_jasa,
                    'jenis_harga' => null,
                    'harga' => $price,
                    'jumlah' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            // Proses Sparepart
            foreach ($cleanSpareparts as $item) {
                $sparepart = Sparepart::findOrFail($item['sparepart_id']);
                if (!$sparepart->is_active) {
                    throw ValidationException::withMessages([
                        'sparepart_items' => ["Sparepart '{$sparepart->nama_sparepart}' saat ini tidak aktif."],
                    ]);
                }

                $qty = (int) ($item['qty'] ?? 1);
                if ($qty < 1) {
                    throw ValidationException::withMessages([
                        'sparepart_items' => ["Jumlah sparepart '{$sparepart->nama_sparepart}' harus minimal 1."],
                    ]);
                }

                if ($qty > $sparepart->stok) {
                    throw ValidationException::withMessages([
                        'sparepart_items' => ["Jumlah untuk sparepart '{$sparepart->nama_sparepart}' melebihi stok yang tersedia ({$sparepart->stok})."],
                    ]);
                }

                $priceType = $item['price_type'] ?? 'satuan';
                $price = 0;

                if ($priceType === 'grosir') {
                    if (empty($sparepart->wholesale_price) || empty($sparepart->wholesale_min_qty)) {
                        throw ValidationException::withMessages([
                            'sparepart_items' => ["Sparepart '{$sparepart->nama_sparepart}' tidak memiliki harga grosir."],
                        ]);
                    }
                    if ($qty < $sparepart->wholesale_min_qty) {
                        throw ValidationException::withMessages([
                            'sparepart_items' => ["Harga grosir untuk '{$sparepart->nama_sparepart}' hanya berlaku untuk pembelian minimal {$sparepart->wholesale_min_qty} unit."],
                        ]);
                    }
                    $price = (float) $sparepart->wholesale_price;
                } elseif ($priceType === 'promo') {
                    if (empty($sparepart->promo_price) || $sparepart->promo_price <= 0) {
                        throw ValidationException::withMessages([
                            'sparepart_items' => ["Harga promo tidak tersedia untuk sparepart '{$sparepart->nama_sparepart}'."],
                        ]);
                    }
                    $price = (float) $sparepart->promo_price;
                } else {
                    $priceType = 'satuan';
                    $price = (float) $sparepart->harga_jual;
                }

                $subtotal = $price * $qty;
                $grandTotal += $subtotal;

                $detailsData[] = [
                    'item_type' => 'sparepart',
                    'jasa_servis_id' => null,
                    'sparepart_id' => $sparepart->id,
                    'nama_item' => $sparepart->nama_sparepart,
                    'jenis_harga' => $priceType,
                    'harga' => $price,
                    'jumlah' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            // 5. Generate nomor invoice unik (INV-YYYYMMDD-0001) aman dari race condition
            $prefix = 'INV-' . date('Ymd') . '-';
            $lastInvoice = Transaction::where('no_transaksi', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('no_transaksi');

            $nextNumber = 1;
            if ($lastInvoice) {
                $lastSeq = (int) substr($lastInvoice, strlen($prefix));
                $nextNumber = $lastSeq + 1;
            }
            $noTransaksi = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

            // 6. Buat Record Transaksi
            $transaction = Transaction::create([
                'no_transaksi' => $noTransaksi,
                'no_invoice' => $noTransaksi,
                'user_id' => Auth::id() ?? 1,
                'customer_id' => $customerId,
                'vehicle_id' => $vehicle->id,
                'mekanik_id' => $mekanikId,
                'keluhan' => $request->input('keluhan'),
                'status' => 'belum_dibayar',
                'total' => $grandTotal,
            ]);

            // 7. Simpan detail transaksi
            foreach ($detailsData as $detail) {
                $detail['transaction_id'] = $transaction->id;
                TransactionDetail::create($detail);
            }

            return redirect()->route('transactions.show', $transaction)
                ->with('success', "Transaksi {$noTransaksi} berhasil dibuat dengan status BELUM DIBAYAR.");
        });
    }

    /**
     * Tampilkan detail transaksi.
     */
    public function show(Transaction $transaction): View
    {
        $transaction->load(['customer', 'vehicle', 'mekanik', 'user', 'details.jasaServis', 'details.sparepart']);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Tampilkan formulir edit transaksi yang belum dibayar.
     */
    public function edit(Transaction $transaction): View
    {
        if (!$transaction->isBelumDibayar()) {
            abort(403, 'Transaksi yang sudah lunas tidak boleh diedit.');
        }

        $transaction->load(['customer', 'vehicle', 'mekanik', 'details']);

        $mechanics = Mekanik::where('is_active', true)->orderBy('nama_mekanik')->get();
        $services = JasaServis::where('is_active', true)->orderBy('nama_jasa')->get();
        $spareparts = Sparepart::where('is_active', true)->orderBy('nama_sparepart')->get();

        return view('transactions.edit', compact('transaction', 'mechanics', 'services', 'spareparts'));
    }

    /**
     * Perbarui transaksi belum dibayar secara atomik.
     */
    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        if (!$transaction->isBelumDibayar()) {
            abort(403, 'Transaksi yang sudah lunas tidak boleh diedit.');
        }

        return DB::transaction(function () use ($request, $transaction) {
            $jasaItems = $request->input('jasa_items', []);
            $sparepartItems = $request->input('sparepart_items', []);

            $cleanJasa = [];
            foreach ($jasaItems as $item) {
                if (!empty($item['jasa_id'])) {
                    $cleanJasa[] = $item;
                }
            }

            $cleanSpareparts = [];
            foreach ($sparepartItems as $item) {
                if (!empty($item['sparepart_id'])) {
                    $cleanSpareparts[] = $item;
                }
            }

            if (empty($cleanJasa) && empty($cleanSpareparts)) {
                throw ValidationException::withMessages([
                    'items' => ['Transaksi harus memiliki minimal satu jasa servis atau satu sparepart.'],
                ]);
            }

            // Validasi Mekanik
            $mekanikId = $request->input('mekanik_id');
            if (!empty($cleanJasa)) {
                if (!$mekanikId) {
                    throw ValidationException::withMessages([
                        'mekanik_id' => ['Mekanik wajib dipilih jika transaksi memiliki jasa servis.'],
                    ]);
                }
            }

            if ($mekanikId) {
                $mekanik = Mekanik::find($mekanikId);
                if (!$mekanik || !$mekanik->is_active) {
                    throw ValidationException::withMessages([
                        'mekanik_id' => ['Mekanik yang dipilih tidak aktif atau tidak ditemukan.'],
                    ]);
                }
            } else {
                $mekanikId = null;
            }

            // Hitung ulang detail di server
            $detailsData = [];
            $grandTotal = 0;

            foreach ($cleanJasa as $item) {
                $jasa = JasaServis::findOrFail($item['jasa_id']);
                if (!$jasa->is_active) {
                    throw ValidationException::withMessages([
                        'jasa_items' => ["Jasa servis '{$jasa->nama_jasa}' saat ini tidak aktif."],
                    ]);
                }

                $qty = isset($item['qty']) ? (int) $item['qty'] : 1;
                if ($qty < 1) {
                    $qty = 1;
                }

                $price = (float) $jasa->harga;
                $subtotal = $price * $qty;
                $grandTotal += $subtotal;

                $detailsData[] = [
                    'item_type' => 'jasa',
                    'jasa_servis_id' => $jasa->id,
                    'sparepart_id' => null,
                    'nama_item' => $jasa->nama_jasa,
                    'jenis_harga' => null,
                    'harga' => $price,
                    'jumlah' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            foreach ($cleanSpareparts as $item) {
                $sparepart = Sparepart::findOrFail($item['sparepart_id']);
                if (!$sparepart->is_active) {
                    throw ValidationException::withMessages([
                        'sparepart_items' => ["Sparepart '{$sparepart->nama_sparepart}' saat ini tidak aktif."],
                    ]);
                }

                $qty = (int) ($item['qty'] ?? 1);
                if ($qty < 1) {
                    throw ValidationException::withMessages([
                        'sparepart_items' => ["Jumlah sparepart '{$sparepart->nama_sparepart}' harus minimal 1."],
                    ]);
                }

                if ($qty > $sparepart->stok) {
                    throw ValidationException::withMessages([
                        'sparepart_items' => ["Jumlah untuk sparepart '{$sparepart->nama_sparepart}' melebihi stok yang tersedia ({$sparepart->stok})."],
                    ]);
                }

                $priceType = $item['price_type'] ?? 'satuan';
                $price = 0;

                if ($priceType === 'grosir') {
                    if (empty($sparepart->wholesale_price) || empty($sparepart->wholesale_min_qty)) {
                        throw ValidationException::withMessages([
                            'sparepart_items' => ["Sparepart '{$sparepart->nama_sparepart}' tidak memiliki harga grosir."],
                        ]);
                    }
                    if ($qty < $sparepart->wholesale_min_qty) {
                        throw ValidationException::withMessages([
                            'sparepart_items' => ["Harga grosir untuk '{$sparepart->nama_sparepart}' hanya berlaku untuk pembelian minimal {$sparepart->wholesale_min_qty} unit."],
                        ]);
                    }
                    $price = (float) $sparepart->wholesale_price;
                } elseif ($priceType === 'promo') {
                    if (empty($sparepart->promo_price) || $sparepart->promo_price <= 0) {
                        throw ValidationException::withMessages([
                            'sparepart_items' => ["Harga promo tidak tersedia untuk sparepart '{$sparepart->nama_sparepart}'."],
                        ]);
                    }
                    $price = (float) $sparepart->promo_price;
                } else {
                    $priceType = 'satuan';
                    $price = (float) $sparepart->harga_jual;
                }

                $subtotal = $price * $qty;
                $grandTotal += $subtotal;

                $detailsData[] = [
                    'item_type' => 'sparepart',
                    'jasa_servis_id' => null,
                    'sparepart_id' => $sparepart->id,
                    'nama_item' => $sparepart->nama_sparepart,
                    'jenis_harga' => $priceType,
                    'harga' => $price,
                    'jumlah' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            // Pelanggan dan kendaraan tidak boleh diganti saat edit
            $transaction->update([
                'mekanik_id' => $mekanikId,
                'keluhan' => $request->input('keluhan'),
                'total' => $grandTotal,
            ]);

            // Hapus detail lama dan masukkan detail baru
            $transaction->details()->delete();

            foreach ($detailsData as $detail) {
                $detail['transaction_id'] = $transaction->id;
                TransactionDetail::create($detail);
            }

            return redirect()->route('transactions.show', $transaction)
                ->with('success', "Transaksi {$transaction->no_transaksi} berhasil diperbarui.");
        });
    }
}

