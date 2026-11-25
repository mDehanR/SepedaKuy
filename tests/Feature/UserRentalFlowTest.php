<?php

namespace Tests\Feature;

use App\Models\Bike;
use App\Models\PaymentMethod;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserRentalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_a_rental_form_with_a_bike_selector(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $bike = $this->createBike();

        $response = $this->actingAs($user)->get(route('user.rentals.quick-create'));

        $response->assertOk();
        $response->assertSee('Pilih Sepeda');
        $response->assertSee($bike->name);
    }

    public function test_user_can_submit_a_rental_from_the_bike_selector(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'user']);
        $bike = $this->createBike();
        $paymentMethod = PaymentMethod::create([
            'name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'SepedaKuy',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('user.rentals.quick-store'), [
            'bike_id' => $bike->id,
            'duration_hours' => 2,
            'payment_method_id' => $paymentMethod->id,
            'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
        ]);

        $response->assertRedirect(route('user.rentals.index', absolute: false));
        $this->assertDatabaseHas('rentals', [
            'user_id' => $user->id,
            'bike_id' => $bike->id,
            'duration_hours' => 2,
            'locked_price_per_hour' => 30000,
            'rental_fee' => 60000,
            'status' => 'pending',
        ]);
    }

    public function test_user_can_edit_pending_rental_bike_and_duration_without_replacing_payment_proof(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $originalBike = $this->createBike('Polygon Cascade', 'MTB', 'MTB-01', 30000);
        $selectedBike = $this->createBike('Polygon Path', 'City Bike', 'CITY-01', 45000);
        $rental = $this->createRental($user, $originalBike, [
            'payment_proof' => 'payment_proofs/original.jpg',
        ]);

        $response = $this->actingAs($user)->put(route('user.rentals.update', $rental), [
            'bike_id' => $selectedBike->id,
            'duration_hours' => 3,
        ]);

        $response->assertRedirect(route('user.rentals.index', absolute: false));
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id,
            'bike_id' => $selectedBike->id,
            'duration_hours' => 3,
            'locked_price_per_hour' => 45000,
            'rental_fee' => 135000,
            'payment_proof' => 'payment_proofs/original.jpg',
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_edit_another_users_rental(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $owner = User::factory()->create(['role' => 'user']);
        $bike = $this->createBike();
        $rental = $this->createRental($owner, $bike);

        $this->actingAs($user)
            ->put(route('user.rentals.update', $rental), [
                'bike_id' => $bike->id,
                'duration_hours' => 2,
            ])
            ->assertForbidden();
    }

    public function test_user_cannot_edit_a_rental_after_approval_has_started_it(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $bike = $this->createBike();
        $rental = $this->createRental($user, $bike, ['status' => 'active']);

        $response = $this->actingAs($user)->put(route('user.rentals.update', $rental), [
            'bike_id' => $bike->id,
            'duration_hours' => 3,
        ]);

        $response->assertRedirect(route('user.rentals.index', absolute: false));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id,
            'duration_hours' => 1,
            'rental_fee' => 30000,
            'status' => 'active',
        ]);
    }

    private function createBike(
        string $name = 'Polygon Cascade',
        string $type = 'MTB',
        string $code = 'MTB-01',
        int $pricePerHour = 30000,
    ): Bike {
        return Bike::create([
            'name' => $name,
            'type' => $type,
            'code' => $code,
            'price_per_hour' => $pricePerHour,
            'stock_total' => 4,
            'stock_available' => 4,
            'status' => 'available',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createRental(User $user, Bike $bike, array $attributes = []): Rental
    {
        return Rental::create(array_merge([
            'user_id' => $user->id,
            'bike_id' => $bike->id,
            'duration_hours' => 1,
            'locked_price_per_hour' => $bike->price_per_hour,
            'rental_fee' => $bike->price_per_hour,
            'payment_proof' => 'payment_proofs/proof.jpg',
            'status' => 'pending',
        ], $attributes));
    }
}
