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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique();
            $table->string('no_invoice')->unique()->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('restrict');
            $table->foreignId('mekanik_id')->nullable()->constrained('mekaniks')->onDelete('set null');
            $table->text('keluhan')->nullable();
            $table->string('status')->default('belum_dibayar');
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->string('item_type'); // 'jasa' atau 'sparepart'
            $table->foreignId('jasa_servis_id')->nullable()->constrained('jasa_servis')->onDelete('set null');
            $table->foreignId('sparepart_id')->nullable()->constrained('spareparts')->onDelete('set null');
            $table->string('nama_item');
            $table->string('jenis_harga')->nullable(); // 'satuan', 'grosir', 'promo'
            $table->decimal('harga', 12, 2)->default(0);
            $table->integer('jumlah')->default(1);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
        Schema::dropIfExists('transactions');
    }
};

