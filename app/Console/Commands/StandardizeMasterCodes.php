<?php

namespace App\Console\Commands;

use App\Models\JasaServis;
use App\Models\Sparepart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StandardizeMasterCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:standardize-codes {--dry-run : Preview perubahan tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menyeragamkan format kode lama Jasa Servis (JS-xxx) dan Sparepart (SP-xxx) berdasarkan ID';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $this->info('=== Standardisasi Kode Jasa Servis & Sparepart ===');
        if ($isDryRun) {
            $this->warn('[DRY-RUN MODE] Tidak ada perubahan yang akan disimpan ke database.');
        }

        // 1. Jasa Servis
        $jasaList = JasaServis::orderBy('id', 'asc')->get();
        $this->newLine();
        $this->info("Menemukan {$jasaList->count()} data Jasa Servis.");

        $jasaRows = [];
        $jasaUpdates = [];
        foreach ($jasaList as $index => $jasa) {
            $newCode = 'JS-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $jasaRows[] = [
                'ID' => $jasa->id,
                'Nama' => $jasa->nama_jasa,
                'Kode Lama' => $jasa->kode_jasa,
                'Kode Baru' => $newCode,
                'Status' => $jasa->kode_jasa === $newCode ? 'Tetap' : 'Diperbarui',
            ];
            $jasaUpdates[$jasa->id] = $newCode;
        }
        $this->table(['ID', 'Nama Jasa', 'Kode Lama', 'Kode Baru', 'Status'], $jasaRows);

        // 2. Sparepart
        $sparepartList = Sparepart::orderBy('id', 'asc')->get();
        $this->newLine();
        $this->info("Menemukan {$sparepartList->count()} data Sparepart.");

        $sparepartRows = [];
        $sparepartUpdates = [];
        foreach ($sparepartList as $index => $sp) {
            $newCode = 'SP-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $sparepartRows[] = [
                'ID' => $sp->id,
                'Nama' => $sp->nama_sparepart,
                'Kode Lama' => $sp->kode_sparepart,
                'Kode Baru' => $newCode,
                'Status' => $sp->kode_sparepart === $newCode ? 'Tetap' : 'Diperbarui',
            ];
            $sparepartUpdates[$sp->id] = $newCode;
        }
        $this->table(['ID', 'Nama Sparepart', 'Kode Lama', 'Kode Baru', 'Status'], $sparepartRows);

        if ($isDryRun) {
            $this->newLine();
            $this->comment('Selesai pengecekan. Jalankan tanpa opsi --dry-run untuk menerapkan perubahan.');
            return Command::SUCCESS;
        }

        // Jika interaktif, minta konfirmasi
        if ($this->input->isInteractive()) {
            if (!$this->confirm('Apakah Anda yakin ingin memperbarui kode di atas? Pastikan Anda sudah membackup database!', false)) {
                $this->warn('Perubahan dibatalkan oleh pengguna.');
                return Command::FAILURE;
            }
        }

        // Terapkan perubahan dalam DB Transaction secara 2-phase agar bebas benturan unique constraint
        DB::transaction(function () use ($jasaList, $jasaUpdates, $sparepartList, $sparepartUpdates) {
            // Phase 1: Set temporary codes
            foreach ($jasaList as $jasa) {
                DB::table('jasa_servis')
                    ->where('id', $jasa->id)
                    ->update(['kode_jasa' => 'TEMP-JS-' . $jasa->id]);
            }
            foreach ($sparepartList as $sp) {
                DB::table('spareparts')
                    ->where('id', $sp->id)
                    ->update(['kode_sparepart' => 'TEMP-SP-' . $sp->id]);
            }

            // Phase 2: Set standardized codes
            foreach ($jasaUpdates as $id => $code) {
                DB::table('jasa_servis')
                    ->where('id', $id)
                    ->update(['kode_jasa' => $code]);
            }
            foreach ($sparepartUpdates as $id => $code) {
                DB::table('spareparts')
                    ->where('id', $id)
                    ->update(['kode_sparepart' => $code]);
            }
        });

        $this->newLine();
        $this->info('✓ Seluruh kode Jasa Servis dan Sparepart berhasil diseragamkan.');
        return Command::SUCCESS;
    }
}
