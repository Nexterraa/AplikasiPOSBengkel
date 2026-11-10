<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'metode',
        'total_bayar',
        'jumlah_diterima',
        'kembalian',
        'catatan',
        'waktu_bayar',
    ];

    protected function casts(): array
    {
        return [
            'total_bayar'     => 'float',
            'jumlah_diterima' => 'float',
            'kembalian'       => 'float',
            'waktu_bayar'     => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCash(): bool
    {
        return $this->metode === 'cash';
    }

    public function isQris(): bool
    {
        return $this->metode === 'qris';
    }

    public function getMethodAttribute(): string
    {
        return $this->metode;
    }

    public function getAmountPaidAttribute(): float
    {
        return (float) $this->jumlah_diterima;
    }

    public function getChangeAmountAttribute(): float
    {
        return (float) $this->kembalian;
    }

    public function getPaidAtAttribute()
    {
        return $this->waktu_bayar;
    }
}

