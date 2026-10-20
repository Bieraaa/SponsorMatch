<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'sponsor',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'sponsor',
        ]);

        // Blok A: pendaftar baru otomatis terverifikasi (lihat CreateNewUser)
        // agar langsung bisa masuk dashboard tanpa klik link verifikasi email.
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_role_is_required_when_registering()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    public function test_role_must_be_a_valid_value()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test3@example.com',
            'role' => 'hacker',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }
}
