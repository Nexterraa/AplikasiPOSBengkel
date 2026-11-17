<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JasaServis extends Model
{
    use HasFactory;

    protected $table = 'jasa_servis';

    protected $fillable = [
        'kode_jasa',
        'nama_jasa',
        'harga',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Generate kode jasa otomatis (contoh: JS-001, JS-002) berdasarkan data terakhir.
     */
    public static function generateNextKode(): string
    {
        $codes = static::where('kode_jasa', 'like', 'JS-%')->pluck('kode_jasa');

        $maxNumber = 0;
        foreach ($codes as $code) {
            if (preg_match('/^JS-(\d+)$/i', $code, $matches)) {
                $number = (int) $matches[1];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }

        $nextNumber = $maxNumber + 1;
        $kode = 'JS-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

        while (static::where('kode_jasa', $kode)->exists()) {
            $nextNumber++;
            $kode = 'JS-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        }

        return $kode;
    }
}

