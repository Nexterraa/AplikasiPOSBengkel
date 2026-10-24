<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $table = 'transaction_details';

    protected $fillable = [
        'transaction_id',
        'item_type',
        'jasa_servis_id',
        'sparepart_id',
        'nama_item',
        'jenis_harga',
        'harga',
        'jumlah',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'float',
            'jumlah' => 'integer',
            'subtotal' => 'float',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function jasaServis(): BelongsTo
    {
        return $this->belongsTo(JasaServis::class);
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }

    // Aliases for compatibility
    public function getItemNameAttribute(): string
    {
        return $this->nama_item;
    }

    public function setItemNameAttribute($value): void
    {
        $this->attributes['nama_item'] = $value;
    }

    public function getPriceAttribute(): float
    {
        return (float) $this->harga;
    }

    public function setPriceAttribute($value): void
    {
        $this->attributes['harga'] = $value;
    }

    public function getQuantityAttribute(): int
    {
        return (int) $this->jumlah;
    }

    public function setQuantityAttribute($value): void
    {
        $this->attributes['jumlah'] = $value;
    }

    public function getPriceTypeAttribute(): ?string
    {
        return $this->jenis_harga;
    }

    public function setPriceTypeAttribute($value): void
    {
        $this->attributes['jenis_harga'] = $value;
    }

    public function isJasa(): bool
    {
        return $this->item_type === 'jasa';
    }

    public function isSparepart(): bool
    {
        return $this->item_type === 'sparepart';
    }
}

