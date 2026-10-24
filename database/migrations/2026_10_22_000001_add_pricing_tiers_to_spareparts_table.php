<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->decimal('wholesale_price', 12, 2)->nullable()->after('harga_jual');
            $table->integer('wholesale_min_qty')->nullable()->after('wholesale_price');
            $table->decimal('promo_price', 12, 2)->nullable()->after('wholesale_min_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->dropColumn(['wholesale_price', 'wholesale_min_qty', 'promo_price']);
        });
    }
};

