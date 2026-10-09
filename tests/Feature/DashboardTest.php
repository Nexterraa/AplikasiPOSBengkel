<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_admin_dashboard_with_metrics(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Admin');
        $response->assertSee('Total Omzet Lunas');
        $response->assertSee('Transaksi Lunas');
        $response->assertSee('Stok Menipis');
    }

    public function test_kasir_can_view_kasir_dashboard_with_metrics(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $response = $this->actingAs($kasir)->get('/kasir/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Kasir');
        $response->assertSee('Omzet Hari Ini');
        $response->assertSee('Selesai Hari Ini');
    }

    public function test_kasir_cannot_access_admin_dashboard(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $response = $this->actingAs($kasir)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_kasir_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/kasir/dashboard');

        $response->assertStatus(403);
    }
}

