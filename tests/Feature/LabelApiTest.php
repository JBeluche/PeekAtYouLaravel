<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Label;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LabelApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_add_a_label_to_their_own_calendar(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->postJson('/api/labels', [
            'calendar_id' => $calendar->id,
            'color' => '#ff0000',
            'name' => 'At home',
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'At home')
            ->assertJsonPath('data.color', '#ff0000');
    }

    public function test_a_user_cannot_add_a_label_to_another_users_calendar(): void
    {
        $otherCalendar = Calendar::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/labels', [
            'calendar_id' => $otherCalendar->id,
            'color' => '#ff0000',
            'name' => 'Injected',
        ])->assertStatus(422)->assertJsonValidationErrors('calendar_id');

        $this->assertDatabaseCount('labels', 0);
    }

    public function test_a_colour_is_unique_within_one_calendar(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        Label::factory()->create(['calendar_id' => $calendar->id, 'color' => '#ff0000']);

        Sanctum::actingAs($user);

        $this->postJson('/api/labels', [
            'calendar_id' => $calendar->id,
            'color' => '#ff0000',
            'name' => 'Duplicate',
        ])->assertStatus(422)->assertJsonValidationErrors('color');
    }

    public function test_the_same_colour_may_be_reused_on_a_different_calendar(): void
    {
        $user = User::factory()->create();
        $first = Calendar::factory()->create(['owner_id' => $user->id]);
        $second = Calendar::factory()->create(['owner_id' => $user->id]);
        Label::factory()->create(['calendar_id' => $first->id, 'color' => '#ff0000']);

        Sanctum::actingAs($user);

        $this->postJson('/api/labels', [
            'calendar_id' => $second->id,
            'color' => '#ff0000',
            'name' => 'Same colour, other calendar',
        ])->assertCreated();
    }

    public function test_index_returns_only_labels_from_the_callers_calendars(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        Label::factory()->count(2)->create(['calendar_id' => $calendar->id]);
        Label::factory()->count(3)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/labels')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_a_user_cannot_read_update_or_delete_another_users_label(): void
    {
        $label = Label::factory()->create(['name' => 'Private']);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/labels/{$label->id}")->assertForbidden();
        $this->putJson("/api/labels/{$label->id}", ['name' => 'Hijacked'])->assertForbidden();
        $this->deleteJson("/api/labels/{$label->id}")->assertForbidden();

        $this->assertSame('Private', $label->fresh()->name);
    }

    public function test_an_owner_can_rename_and_delete_their_label(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        $label = Label::factory()->create(['calendar_id' => $calendar->id]);

        Sanctum::actingAs($user);

        $this->putJson("/api/labels/{$label->id}", ['name' => 'Renamed'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');

        $this->deleteJson("/api/labels/{$label->id}")->assertOk();
        $this->assertDatabaseMissing('labels', ['id' => $label->id]);
    }
}
