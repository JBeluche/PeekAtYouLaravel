<?php

namespace Tests\Feature;

use App\Enums\Plan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_receives_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jerimie',
            'email' => 'jerimie@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.email', 'jerimie@example.com')
            ->assertJsonPath('data.user.plan', Plan::Free->value)
            ->assertJsonStructure(['status', 'message', 'data' => ['user' => ['id', 'name', 'email'], 'token']]);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertDatabaseHas('users', ['email' => 'jerimie@example.com', 'signed_in' => true]);
    }

    public function test_registration_creates_default_settings_for_the_user(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Jerimie',
            'email' => 'jerimie@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ])->assertCreated();

        $user = User::where('email', 'jerimie@example.com')->sole();

        $this->assertInstanceOf(Setting::class, $user->setting);
        $this->assertSame(14, $user->setting->note_font_size);
    }

    public function test_registration_never_returns_the_password_hash(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jerimie',
            'email' => 'jerimie@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ]);

        $this->assertArrayNotHasKey('password', $response->json('data.user'));
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Jerimie',
            'email' => 'taken@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_registration_requires_a_confirmed_password(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Jerimie',
            'email' => 'jerimie@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'something-else',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_a_user_can_log_in_with_correct_credentials(): void
    {
        User::factory()->create([
            'email' => 'jerimie@example.com',
            'password' => Hash::make('password-123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'jerimie@example.com',
            'password' => 'password-123',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.email', 'jerimie@example.com');

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertDatabaseHas('users', ['email' => 'jerimie@example.com', 'signed_in' => true]);
    }

    public function test_login_fails_with_a_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'jerimie@example.com',
            'password' => Hash::make('password-123'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'jerimie@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized()->assertJsonPath('status', 'error');
    }

    public function test_logout_revokes_every_token_and_clears_signed_in(): void
    {
        $user = User::factory()->create(['signed_in' => true]);
        Sanctum::actingAs($user);
        $user->createToken('test')->plainTextToken;

        $this->postJson('/api/logout')->assertOk();

        $this->assertSame(0, $user->tokens()->count());
        $this->assertFalse($user->fresh()->signed_in);
    }

    public function test_the_user_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_an_authenticated_user_can_read_their_own_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }
}
