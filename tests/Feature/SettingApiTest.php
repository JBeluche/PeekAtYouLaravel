<?php

namespace Tests\Feature;

use App\Enums\DayMarkStyle;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SettingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_require_authentication(): void
    {
        $this->getJson('/api/settings')->assertUnauthorized();
    }

    public function test_a_user_reads_their_own_settings(): void
    {
        $user = User::factory()->create();
        Setting::create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/settings')
            ->assertOk()
            ->assertJsonPath('data.note_font_size', 14)
            ->assertJsonPath('data.day_mark_style', DayMarkStyle::Stroke->value);
    }

    public function test_settings_are_created_on_demand_for_older_accounts(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/settings')->assertOk()->assertJsonPath('data.note_font_size', 14);

        $this->assertDatabaseHas('settings', ['user_id' => $user->id]);
    }

    public function test_a_user_can_update_their_settings(): void
    {
        $user = User::factory()->create();
        Setting::create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->putJson('/api/settings', [
            'note_font_size' => 18,
            'note_font' => 'serif',
            'day_mark_style' => DayMarkStyle::Fill->value,
        ])
            ->assertOk()
            ->assertJsonPath('data.note_font_size', 18)
            ->assertJsonPath('data.day_mark_style', DayMarkStyle::Fill->value);
    }

    public function test_an_out_of_range_font_size_is_rejected(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/settings', ['note_font_size' => 400])
            ->assertStatus(422)
            ->assertJsonValidationErrors('note_font_size');
    }

    public function test_one_user_cannot_change_another_users_settings(): void
    {
        $victim = User::factory()->create();
        Setting::create(['user_id' => $victim->id, 'note_font_size' => 12]);

        $attacker = User::factory()->create();
        Sanctum::actingAs($attacker);

        $this->putJson('/api/settings', ['note_font_size' => 40])->assertOk();

        // The attacker changed only their own row.
        $this->assertSame(12.0, $victim->fresh()->setting->note_font_size);
    }

    public function test_font_size_accepts_quarter_steps_only(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/settings', ['note_font_size' => 12.25])
            ->assertOk()
            ->assertJsonPath('data.note_font_size', 12.25);

        $this->putJson('/api/settings', ['note_font_size' => 12.3])
            ->assertStatus(422)
            ->assertJsonValidationErrors('note_font_size');
    }
}
