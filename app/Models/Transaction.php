<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'no_transaksi',
        'no_invoice',
        'user_id',
        'customer_id',
        'vehicle_id',
        'mekanik_id',
        'keluhan',
        'status',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'float',
        ];
    }

    public function getNoInvoiceAttribute(): ?string
    {
        return $this->attributes['no_invoice'] ?? $this->attributes['no_transaksi'] ?? null;
    }

    public function setNoInvoiceAttribute($value): void
    {
        $this->attributes['no_invoice'] = $value;
        if (empty($this->attributes['no_transaksi'])) {
            $this->attributes['no_transaksi'] = $value;
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function mekanik(): BelongsTo
    {
        return $this->belongsTo(Mekanik::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function jasaDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class)->where('item_type', 'jasa');
    }

    public function sparepartDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class)->where('item_type', 'sparepart');
    }

    public function isBelumDibayar(): bool
    {
        return strtolower($this->status) === 'belum_dibayar';
    }

    public function isLunas(): bool
    {
        return strtolower($this->status) === 'lunas';
    }
}

