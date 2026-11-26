<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 10, Bandung',
            'birth_date' => '2000-05-20',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 10, Bandung',
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertSame('2000-05-20', $user->birth_date->toDateString());
    }

    public function test_registration_requires_phone_address_and_birth_information(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'phone',
            'address',
            'birth_date',
        ]);
        $this->assertGuest();
    }

    public function test_birth_date_cannot_be_in_the_future(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 10, Bandung',
            'birth_date' => '2999-01-01',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['birth_date']);
        $this->assertGuest();
    }
}
