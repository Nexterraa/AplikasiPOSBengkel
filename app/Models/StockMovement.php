<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'sparepart_id',
        'user_id',
        'jenis',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah'       => 'integer',
            'stok_sebelum' => 'integer',
            'stok_sesudah' => 'integer',
        ];
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

