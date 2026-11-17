<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sparepart extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_sparepart',
        'nama_sparepart',
        'merek',
        'kategori_id',
        'harga_beli',
        'harga_jual',
        'stok',
        'stok_minimum',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli' => 'float',
            'harga_jual' => 'float',
            'stok' => 'integer',
            'stok_minimum' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    /**
     * Generate kode sparepart otomatis (contoh: SP-001, SP-002) berdasarkan data terakhir.
     */
    public static function generateNextKode(): string
    {
        $codes = static::where('kode_sparepart', 'like', 'SP-%')->pluck('kode_sparepart');

        $maxNumber = 0;
        foreach ($codes as $code) {
            if (preg_match('/^SP-(\d+)$/i', $code, $matches)) {
                $number = (int) $matches[1];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }

        $nextNumber = $maxNumber + 1;
        $kode = 'SP-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

        while (static::where('kode_sparepart', $kode)->exists()) {
            $nextNumber++;
            $kode = 'SP-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        }

        return $kode;
    }
}

