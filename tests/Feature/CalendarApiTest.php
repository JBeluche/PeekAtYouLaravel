<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Entry;
use App\Models\Label;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CalendarApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_endpoints_require_authentication(): void
    {
        $this->getJson('/api/calendars')->assertUnauthorized();
        $this->postJson('/api/calendars', ['name' => 'Work'])->assertUnauthorized();
    }

    public function test_index_returns_only_the_callers_calendars(): void
    {
        $user = User::factory()->create();
        Calendar::factory()->count(2)->create(['owner_id' => $user->id]);
        Calendar::factory()->count(3)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/calendars')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_user_can_create_a_calendar_with_its_legend(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/calendars', [
            'name' => 'Work',
            'labels' => [
                ['color' => '#ff0000', 'name' => 'At home'],
                ['color' => '#00ff00', 'name' => 'Standup'],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Work')
            ->assertJsonPath('data.owner_id', $user->id)
            ->assertJsonCount(2, 'data.labels');

        $this->assertDatabaseHas('calendars', ['name' => 'Work', 'owner_id' => $user->id]);
    }

    public function test_a_legend_with_a_repeated_colour_is_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/calendars', [
            'name' => 'Work',
            'labels' => [
                ['color' => '#ff0000', 'name' => 'At home'],
                ['color' => '#ff0000', 'name' => 'Standup'],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors('labels.1.color');

        $this->assertDatabaseCount('calendars', 0);
    }

    public function test_a_calendar_is_always_owned_by_the_caller(): void
    {
        $user = User::factory()->create();
        $someoneElse = User::factory()->create();
        Sanctum::actingAs($user);

        // owner_id in the payload must be ignored.
        $this->postJson('/api/calendars', [
            'name' => 'Work',
            'owner_id' => $someoneElse->id,
        ])->assertCreated()->assertJsonPath('data.owner_id', $user->id);
    }

    public function test_a_user_cannot_read_another_users_calendar(): void
    {
        $calendar = Calendar::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/calendars/{$calendar->id}")->assertForbidden();
    }

    public function test_a_user_cannot_rename_another_users_calendar(): void
    {
        $calendar = Calendar::factory()->create(['name' => 'Original']);
        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/calendars/{$calendar->id}", ['name' => 'Hijacked'])
            ->assertForbidden();

        $this->assertSame('Original', $calendar->fresh()->name);
    }

    public function test_a_user_cannot_delete_another_users_calendar(): void
    {
        $calendar = Calendar::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/calendars/{$calendar->id}")->assertForbidden();

        $this->assertDatabaseHas('calendars', ['id' => $calendar->id]);
    }

    public function test_an_owner_can_update_and_delete_their_calendar(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->putJson("/api/calendars/{$calendar->id}", ['name' => 'Renamed'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');

        $this->deleteJson("/api/calendars/{$calendar->id}")->assertOk();
        $this->assertSoftDeleted($calendar);
        $this->getJson('/api/calendars')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_a_retried_create_returns_the_same_calendar(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $payload = [
            'client_uuid' => '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d',
            'name' => 'Work',
            'labels' => [['color' => '#ff0000', 'name' => 'At home']],
        ];

        $id = $this->postJson('/api/calendars', $payload)->assertCreated()->json('data.id');
        $this->postJson('/api/calendars', $payload)->assertOk()->assertJsonPath('data.id', $id);

        $this->assertDatabaseCount('calendars', 1);
        $this->assertDatabaseCount('labels', 1);
    }

    public function test_the_same_client_uuid_on_two_accounts_makes_two_calendars(): void
    {
        $uuid = '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d';
        Calendar::factory()->create(['client_uuid' => $uuid]);

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/calendars', ['client_uuid' => $uuid, 'name' => 'Mine'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Mine');
    }

    public function test_since_returns_changed_and_deleted_calendars(): void
    {
        $user = User::factory()->create();
        Calendar::factory()->create(['owner_id' => $user->id]);
        $deleted = Calendar::factory()->create(['owner_id' => $user->id]);

        $this->travel(1)->hour();
        $since = now()->toISOString();
        $deleted->delete();

        Sanctum::actingAs($user);

        $this->getJson('/api/calendars?since='.urlencode($since))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $deleted->id)
            ->assertJsonPath('data.0.deleted_at', fn ($value) => $value !== null);
    }

    public function test_the_entries_aggregate_filters_by_month_and_year(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        Label::factory()->create(['calendar_id' => $calendar->id]);

        Entry::factory()->create(['calendar_id' => $calendar->id, 'date' => '2026-09-14']);
        Entry::factory()->create(['calendar_id' => $calendar->id, 'date' => '2026-09-20']);
        Entry::factory()->create(['calendar_id' => $calendar->id, 'date' => '2026-08-14']);

        Sanctum::actingAs($user);

        $this->getJson('/api/calendars/entries?month=9&year=2026')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.0.entries')
            ->assertJsonCount(1, 'data.0.labels');
    }

    public function test_the_entries_aggregate_never_leaks_another_users_calendar(): void
    {
        $user = User::factory()->create();
        $otherCalendar = Calendar::factory()->create();
        Entry::factory()->create(['calendar_id' => $otherCalendar->id, 'date' => '2026-09-14']);

        Sanctum::actingAs($user);

        $this->getJson('/api/calendars/entries?month=9&year=2026')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
