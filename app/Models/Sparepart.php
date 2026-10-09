<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Status stok: Habis | Menipis | Aman
     */
    public function getStatusStokAttribute(): string
    {
        if ($this->stok <= 0) {
            return 'Habis';
        }
        if ($this->stok <= $this->stok_minimum) {
            return 'Menipis';
        }
        return 'Aman';
    }
}

