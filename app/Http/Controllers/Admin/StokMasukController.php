<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StokMasukController extends Controller
{
    /**
     * Form tambah stok masuk.
     */
    public function create(): View
    {
        $spareparts = Sparepart::where('is_active', true)
            ->orderBy('nama_sparepart')
            ->get(['id', 'nama_sparepart', 'kode_sparepart', 'stok', 'stok_minimum']);

        return view('admin.stok.create', compact('spareparts'));
    }

    /**
     * Simpan stok masuk dengan database transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sparepart_id' => ['required', 'integer', 'exists:spareparts,id'],
            'jumlah'       => ['required', 'integer', 'min:1'],
            'catatan'      => ['nullable', 'string', 'max:500'],
        ], [
            'sparepart_id.required' => 'Sparepart harus dipilih.',
            'sparepart_id.exists'   => 'Sparepart tidak ditemukan.',
            'jumlah.required'       => 'Jumlah stok masuk harus diisi.',
            'jumlah.integer'        => 'Jumlah harus berupa bilangan bulat.',
            'jumlah.min'            => 'Jumlah stok masuk minimal 1.',
            'catatan.max'           => 'Catatan maksimal 500 karakter.',
        ]);

        // Pastikan sparepart yang dipilih masih aktif
        $sparepart = Sparepart::where('id', $validated['sparepart_id'])
            ->where('is_active', true)
            ->firstOrFail();

        DB::transaction(function () use ($sparepart, $validated) {
            $stokSebelum = $sparepart->stok;
            $stokSesudah = $stokSebelum + (int) $validated['jumlah'];

            // Update stok sparepart
            $sparepart->increment('stok', (int) $validated['jumlah']);

            // Catat riwayat pergerakan stok
            StockMovement::create([
                'sparepart_id' => $sparepart->id,
                'user_id'      => Auth::id(),
                'jenis'        => 'masuk',
                'jumlah'       => (int) $validated['jumlah'],
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'catatan'      => $validated['catatan'] ?? null,
            ]);
        });

        return redirect()->route('admin.stok.create')
            ->with('success', "Stok masuk berhasil ditambahkan untuk sparepart \"{$sparepart->nama_sparepart}\".");
    }

    /**
     * Riwayat pergerakan stok.
     */
    public function index(Request $request): View
    {
        $query = StockMovement::with(['sparepart', 'user'])
            ->latest();

        if ($request->filled('sparepart_id')) {
            $query->where('sparepart_id', $request->input('sparepart_id'));
        }

        $movements = $query->paginate(15)->withQueryString();
        $spareparts = Sparepart::orderBy('nama_sparepart')->get(['id', 'nama_sparepart']);

        return view('admin.stok.index', compact('movements', 'spareparts'));
    }
}

