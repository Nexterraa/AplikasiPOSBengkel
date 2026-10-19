<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerVehicleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->kasir = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);
    }

    public function test_admin_and_kasir_can_access_customers_and_vehicles(): void
    {
        // Admin
        $responseAdminCust = $this->actingAs($this->admin)->get(route('customers.index'));
        $responseAdminCust->assertStatus(200);

        $responseAdminVeh = $this->actingAs($this->admin)->get(route('vehicles.index'));
        $responseAdminVeh->assertStatus(200);

        // Kasir
        $responseKasirCust = $this->actingAs($this->kasir)->get(route('customers.index'));
        $responseKasirCust->assertStatus(200);

        $responseKasirVeh = $this->actingAs($this->kasir)->get(route('vehicles.index'));
        $responseKasirVeh->assertStatus(200);
    }

    public function test_guest_cannot_access_customers_or_vehicles(): void
    {
        $this->get(route('customers.index'))->assertRedirect(route('login'));
        $this->get(route('vehicles.index'))->assertRedirect(route('login'));
        $this->get(route('customers.create'))->assertRedirect(route('login'));
        $this->get(route('vehicles.create'))->assertRedirect(route('login'));
    }

    public function test_inactive_user_cannot_access_customers_or_vehicles(): void
    {
        $inactiveUser = User::factory()->create([
            'role' => 'kasir',
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveUser)->get(route('customers.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_crud_customer_successfully(): void
    {
        // Create
        $responseStore = $this->actingAs($this->kasir)->post(route('customers.store'), [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
        ]);

        $responseStore->assertRedirect(route('customers.index'));
        $responseStore->assertSessionHas('success');
        $this->assertDatabaseHas('customers', [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
        ]);

        $customer = Customer::where('phone', '081234567890')->first();

        // Index
        $responseIndex = $this->actingAs($this->kasir)->get(route('customers.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Budi Santoso');

        // Show
        $responseShow = $this->actingAs($this->kasir)->get(route('customers.show', $customer));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Budi Santoso');

        // Edit
        $responseEdit = $this->actingAs($this->kasir)->get(route('customers.edit', $customer));
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('Budi Santoso');

        // Update
        $responseUpdate = $this->actingAs($this->kasir)->put(route('customers.update', $customer), [
            'name' => 'Budi Santoso Update',
            'phone' => '081299998888',
        ]);

        $responseUpdate->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Budi Santoso Update',
            'phone' => '081299998888',
        ]);

        // Delete (without vehicles)
        $responseDestroy = $this->actingAs($this->kasir)->delete(route('customers.destroy', $customer));
        $responseDestroy->assertRedirect(route('customers.index'));
        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_customer_validation_requires_name_and_phone(): void
    {
        $response = $this->actingAs($this->kasir)->post(route('customers.store'), []);

        $response->assertSessionHasErrors(['name', 'phone']);
    }

    public function test_crud_vehicle_successfully(): void
    {
        $customer = Customer::create([
            'name' => 'Agus Pratama',
            'phone' => '085712345678',
        ]);

        // Create
        $responseStore = $this->actingAs($this->kasir)->post(route('vehicles.store'), [
            'customer_id' => $customer->id,
            'plate_number' => 'b 1234 cde',
            'brand' => 'Honda',
            'model' => 'Vario 160',
            'year' => 2022,
        ]);

        $vehicle = Vehicle::where('customer_id', $customer->id)->first();
        $this->assertNotNull($vehicle);
        $responseStore->assertRedirect(route('vehicles.show', $vehicle));
        $responseStore->assertSessionHas('success');

        // Plate number is stored in uppercase
        $this->assertEquals('B 1234 CDE', $vehicle->plate_number);
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'plate_number' => 'B 1234 CDE',
            'brand' => 'Honda',
            'model' => 'Vario 160',
            'year' => 2022,
        ]);

        // Index
        $responseIndex = $this->actingAs($this->kasir)->get(route('vehicles.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('B 1234 CDE');

        // Show
        $responseShow = $this->actingAs($this->kasir)->get(route('vehicles.show', $vehicle));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('B 1234 CDE');
        $responseShow->assertSee('Agus Pratama');

        // Edit
        $responseEdit = $this->actingAs($this->kasir)->get(route('vehicles.edit', $vehicle));
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('B 1234 CDE');

        // Update
        $responseUpdate = $this->actingAs($this->kasir)->put(route('vehicles.update', $vehicle), [
            'customer_id' => $customer->id,
            'plate_number' => 'B 1234 CDE',
            'brand' => 'Honda',
            'model' => 'Vario 160 CBS',
            'year' => 2023,
        ]);

        $responseUpdate->assertRedirect(route('vehicles.show', $vehicle));
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'model' => 'Vario 160 CBS',
            'year' => 2023,
        ]);

        // Delete
        $responseDestroy = $this->actingAs($this->kasir)->delete(route('vehicles.destroy', $vehicle));
        $responseDestroy->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseMissing('vehicles', [
            'id' => $vehicle->id,
        ]);
    }

    public function test_vehicle_validation_requires_mandatory_fields(): void
    {
        $response = $this->actingAs($this->kasir)->post(route('vehicles.store'), []);

        $response->assertSessionHasErrors(['customer_id', 'plate_number', 'brand', 'model']);
    }

    public function test_duplicate_plate_number_is_rejected(): void
    {
        $customer = Customer::create([
            'name' => 'Siti Nurhaliza',
            'phone' => '087812345678',
        ]);

        Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'D 9999 XYZ',
            'brand' => 'Yamaha',
            'model' => 'NMAX',
            'year' => 2021,
        ]);

        // Attempt to create another vehicle with the exact same plate
        $responseDuplicate = $this->actingAs($this->kasir)->post(route('vehicles.store'), [
            'customer_id' => $customer->id,
            'plate_number' => 'D 9999 XYZ',
            'brand' => 'Yamaha',
            'model' => 'Aerox',
            'year' => 2022,
        ]);

        $responseDuplicate->assertSessionHasErrors('plate_number');
    }

    public function test_one_customer_can_have_multiple_vehicles(): void
    {
        $customer = Customer::create([
            'name' => 'Pak Bambang',
            'phone' => '081398765432',
        ]);

        $v1 = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 1111 AAA',
            'brand' => 'Honda',
            'model' => 'Supra X 125',
            'year' => 2015,
        ]);

        $v2 = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'B 2222 BBB',
            'brand' => 'Yamaha',
            'model' => 'Mio M3',
            'year' => 2019,
        ]);

        $this->assertCount(2, $customer->fresh()->vehicles);
        $this->assertEquals($customer->id, $v1->customer->id);
        $this->assertEquals($customer->id, $v2->customer->id);

        // Check customer detail page displays both vehicles
        $responseShow = $this->actingAs($this->kasir)->get(route('customers.show', $customer));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('B 1111 AAA');
        $responseShow->assertSee('B 2222 BBB');
    }

    public function test_customer_with_vehicles_cannot_be_deleted(): void
    {
        $customer = Customer::create([
            'name' => 'Dedi Kurniawan',
            'phone' => '081233445566',
        ]);

        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'plate_number' => 'AD 4567 EF',
            'brand' => 'Suzuki',
            'model' => 'Satria F150',
            'year' => 2018,
        ]);

        // Attempt to delete customer while vehicle still exists
        $response = $this->actingAs($this->kasir)->delete(route('customers.destroy', $customer));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Customer still exists in database (safe deletion protected)
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
        ]);

        // After vehicle is deleted, customer can now be safely deleted
        $vehicle->delete();
        $responseAfter = $this->actingAs($this->kasir)->delete(route('customers.destroy', $customer));
        $responseAfter->assertRedirect(route('customers.index'));
        $responseAfter->assertSessionHas('success');

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_search_customers_and_vehicles(): void
    {
        $c1 = Customer::create(['name' => 'Rina Wijaya', 'phone' => '081111111111']);
        $c2 = Customer::create(['name' => 'Doni Setiawan', 'phone' => '082222222222']);

        $v1 = Vehicle::create([
            'customer_id' => $c1->id,
            'plate_number' => 'AB 1234 CD',
            'brand' => 'Honda',
            'model' => 'Scoopy',
            'year' => 2020,
        ]);

        $v2 = Vehicle::create([
            'customer_id' => $c2->id,
            'plate_number' => 'DK 5678 EF',
            'brand' => 'Kawasaki',
            'model' => 'KLX 150',
            'year' => 2021,
        ]);

        // Customer search by name
        $responseCust = $this->actingAs($this->kasir)->get(route('customers.index', ['search' => 'Rina']));
        $responseCust->assertSee('Rina Wijaya');
        $responseCust->assertDontSee('Doni Setiawan');

        // Vehicle search by plate
        $responseVeh = $this->actingAs($this->kasir)->get(route('vehicles.index', ['search' => 'AB 1234 CD']));
        $responseVeh->assertSee('AB 1234 CD');
        $responseVeh->assertDontSee('DK 5678 EF');

        // Vehicle search by customer name
        $responseVehOwner = $this->actingAs($this->kasir)->get(route('vehicles.index', ['customer_name' => 'Rina']));
        $responseVehOwner->assertSee('AB 1234 CD');
        $responseVehOwner->assertDontSee('DK 5678 EF');
    }
}

