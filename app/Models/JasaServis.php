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
}

