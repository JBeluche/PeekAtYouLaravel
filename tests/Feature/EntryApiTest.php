<?php

namespace Tests\Feature;

use App\Enums\EntrySymbol;
use App\Models\Calendar;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EntryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_write_a_day_note_on_their_own_calendar(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->postJson('/api/entries', [
            'calendar_id' => $calendar->id,
            'date' => '2026-09-14',
            'text' => 'At home | Standup',
            'pen_color' => '#111111',
            'symbol' => EntrySymbol::Check->value,
        ])
            ->assertCreated()
            ->assertJsonPath('data.date', '2026-09-14')
            ->assertJsonPath('data.symbol', EntrySymbol::Check->value)
            ->assertJsonPath('data.text', 'At home | Standup')
            ->assertJsonPath('data.mark_color', null);
    }

    public function test_a_user_cannot_write_onto_another_users_calendar(): void
    {
        $otherCalendar = Calendar::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/entries', [
            'calendar_id' => $otherCalendar->id,
            'date' => '2026-09-14',
            'text' => 'Injected',
        ])->assertStatus(422)->assertJsonValidationErrors('calendar_id');

        $this->assertDatabaseCount('entries', 0);
    }

    public function test_a_day_can_be_marked_with_any_colour_named_or_not(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->postJson('/api/entries', [
            'calendar_id' => $calendar->id,
            'date' => '2026-09-14',
            'mark_color' => '#6F9461',
        ])->assertCreated()->assertJsonPath('data.mark_color', '#6f9461');
    }

    public function test_a_mark_colour_must_be_a_hex_colour(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->postJson('/api/entries', [
            'calendar_id' => $calendar->id,
            'date' => '2026-09-14',
            'mark_color' => 'red',
        ])->assertStatus(422)->assertJsonValidationErrors('mark_color');
    }

    public function test_a_mark_can_be_cleared_to_null(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        $entry = Entry::factory()->create(['calendar_id' => $calendar->id, 'mark_color' => '#ff0000']);

        Sanctum::actingAs($user);

        $this->putJson("/api/entries/{$entry->id}", ['mark_color' => null])
            ->assertOk()
            ->assertJsonPath('data.mark_color', null);
    }

    public function test_writing_a_day_that_already_has_a_note_updates_it(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        $entry = Entry::factory()->create(['calendar_id' => $calendar->id, 'date' => '2026-09-14', 'text' => 'First']);

        Sanctum::actingAs($user);

        $this->postJson('/api/entries', [
            'calendar_id' => $calendar->id,
            'date' => '2026-09-14',
            'text' => 'Second',
        ])->assertOk()->assertJsonPath('data.id', $entry->id)->assertJsonPath('data.text', 'Second');

        $this->assertDatabaseCount('entries', 1);
    }

    public function test_writing_a_deleted_day_restores_it_blank(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        $entry = Entry::factory()->create([
            'calendar_id' => $calendar->id,
            'date' => '2026-09-14',
            'text' => 'Old note',
            'mark_color' => '#ff0000',
        ]);
        $entry->delete();

        Sanctum::actingAs($user);

        $this->postJson('/api/entries', [
            'calendar_id' => $calendar->id,
            'date' => '2026-09-14',
            'symbol' => EntrySymbol::Check->value,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $entry->id)
            ->assertJsonPath('data.deleted_at', null)
            ->assertJsonPath('data.text', null)
            ->assertJsonPath('data.mark_color', null)
            ->assertJsonPath('data.symbol', EntrySymbol::Check->value);
    }

    public function test_since_returns_changed_and_deleted_entries_only(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        Entry::factory()->create(['calendar_id' => $calendar->id]);

        $this->travel(1)->hour();
        $since = now()->toISOString();
        $changed = Entry::factory()->create(['calendar_id' => $calendar->id]);
        $deleted = Entry::factory()->create(['calendar_id' => $calendar->id]);
        $deleted->delete();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/entries?since='.urlencode($since))->assertOk();

        $this->assertEqualsCanonicalizing([$changed->id, $deleted->id], $response->json('data.*.id'));
        $this->assertNotNull(collect($response->json('data'))->firstWhere('id', $deleted->id)['deleted_at']);
    }

    public function test_since_reports_entries_of_a_deleted_calendar(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        $entry = Entry::factory()->create(['calendar_id' => $calendar->id]);

        $this->travel(1)->hour();
        $since = now()->toISOString();
        $calendar->delete();

        Sanctum::actingAs($user);

        $this->getJson('/api/entries?since='.urlencode($since))
            ->assertOk()
            ->assertJsonPath('data.0.id', $entry->id)
            ->assertJsonPath('data.0.deleted_at', fn ($value) => $value !== null);
    }

    public function test_index_returns_only_entries_from_the_callers_calendars(): void
    {
        $user = User::factory()->create();
        $mine = Calendar::factory()->create(['owner_id' => $user->id]);
        Entry::factory()->count(2)->create(['calendar_id' => $mine->id]);
        Entry::factory()->count(3)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/entries')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_index_can_filter_by_month_and_year(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        Entry::factory()->create(['calendar_id' => $calendar->id, 'date' => '2026-09-14']);
        Entry::factory()->create(['calendar_id' => $calendar->id, 'date' => '2026-08-14']);

        Sanctum::actingAs($user);

        $this->getJson('/api/entries?month=9&year=2026')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.date', '2026-09-14');
    }

    public function test_a_month_filter_includes_its_last_day_and_nothing_after(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        Entry::factory()->create(['calendar_id' => $calendar->id, 'date' => '2026-09-01']);
        Entry::factory()->create(['calendar_id' => $calendar->id, 'date' => '2026-09-30']);
        Entry::factory()->create(['calendar_id' => $calendar->id, 'date' => '2026-10-01']);

        Sanctum::actingAs($user);

        $this->getJson('/api/entries?month=9&year=2026')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.1.date', '2026-09-30');
    }

    public function test_a_month_filter_requires_a_year(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/entries?month=9')
            ->assertStatus(422)
            ->assertJsonValidationErrors('year');
    }

    public function test_a_non_owner_is_forbidden_before_their_payload_is_validated(): void
    {
        $entry = Entry::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/entries/{$entry->id}", ['symbol' => 'explosion'])->assertForbidden();
    }

    public function test_a_user_cannot_read_update_or_delete_another_users_entry(): void
    {
        $entry = Entry::factory()->create(['text' => 'Private']);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/entries/{$entry->id}")->assertForbidden();
        $this->putJson("/api/entries/{$entry->id}", ['text' => 'Hijacked'])->assertForbidden();
        $this->deleteJson("/api/entries/{$entry->id}")->assertForbidden();

        $this->assertSame('Private', $entry->fresh()->text);
    }

    public function test_an_owner_can_update_and_delete_their_entry(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        $entry = Entry::factory()->create(['calendar_id' => $calendar->id]);

        Sanctum::actingAs($user);

        $this->putJson("/api/entries/{$entry->id}", ['text' => 'Updated note'])
            ->assertOk()
            ->assertJsonPath('data.text', 'Updated note');

        $this->deleteJson("/api/entries/{$entry->id}")->assertOk();
        $this->assertSoftDeleted($entry);
        $this->getJson("/api/entries/{$entry->id}")->assertNotFound();
    }

    public function test_an_entry_cannot_be_moved_to_another_calendar_or_day_by_update(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        $entry = Entry::factory()->create(['calendar_id' => $calendar->id, 'date' => '2026-09-14']);
        $otherCalendar = Calendar::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson("/api/entries/{$entry->id}", [
            'calendar_id' => $otherCalendar->id,
            'date' => '2026-09-15',
            'text' => 'Still mine',
        ])->assertOk();

        $this->assertSame($calendar->id, $entry->fresh()->calendar_id);
        $this->assertSame('2026-09-14', $entry->fresh()->date->toDateString());
    }

    public function test_an_invalid_symbol_is_rejected(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->postJson('/api/entries', [
            'calendar_id' => $calendar->id,
            'date' => '2026-09-14',
            'symbol' => 'explosion',
        ])->assertStatus(422)->assertJsonValidationErrors('symbol');
    }
}
