<?php

namespace Tests\Feature;

use App\Enums\EntrySymbol;
use App\Models\Calendar;
use App\Models\Entry;
use App\Models\Label;
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
            ->assertJsonPath('data.label_id', null);
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

    public function test_a_day_can_be_marked_with_a_label_from_its_own_calendar(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        $label = Label::factory()->create(['calendar_id' => $calendar->id]);
        Sanctum::actingAs($user);

        $this->postJson('/api/entries', [
            'calendar_id' => $calendar->id,
            'date' => '2026-09-14',
            'label_id' => $label->id,
        ])->assertCreated()->assertJsonPath('data.label_id', $label->id);
    }

    public function test_a_label_from_another_calendar_is_rejected(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);

        // A label the caller owns, but on a different calendar.
        $otherOwnCalendar = Calendar::factory()->create(['owner_id' => $user->id]);
        $wrongLabel = Label::factory()->create(['calendar_id' => $otherOwnCalendar->id]);

        // And one belonging to someone else entirely.
        $foreignLabel = Label::factory()->create();

        Sanctum::actingAs($user);

        foreach ([$wrongLabel, $foreignLabel] as $label) {
            $this->postJson('/api/entries', [
                'calendar_id' => $calendar->id,
                'date' => '2026-09-14',
                'label_id' => $label->id,
            ])->assertStatus(422)->assertJsonValidationErrors('label_id');
        }

        $this->assertDatabaseCount('entries', 0);
    }

    public function test_a_mark_can_be_cleared_to_null(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        $label = Label::factory()->create(['calendar_id' => $calendar->id]);
        $entry = Entry::factory()->create([
            'calendar_id' => $calendar->id,
            'label_id' => $label->id,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/entries/{$entry->id}", ['label_id' => null])
            ->assertOk()
            ->assertJsonPath('data.label_id', null);

        $this->assertNull($entry->fresh()->label_id);
    }

    public function test_deleting_a_label_unmarks_the_days_that_used_it(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        $label = Label::factory()->create(['calendar_id' => $calendar->id]);
        $entry = Entry::factory()->create([
            'calendar_id' => $calendar->id,
            'label_id' => $label->id,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/labels/{$label->id}")->assertOk();

        $this->getJson("/api/entries/{$entry->id}")
            ->assertOk()
            ->assertJsonPath('data.label_id', null);
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
        $this->assertDatabaseMissing('entries', ['id' => $entry->id]);
    }

    public function test_an_entry_cannot_be_moved_to_another_calendar_by_update(): void
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['owner_id' => $user->id]);
        $entry = Entry::factory()->create(['calendar_id' => $calendar->id]);
        $otherCalendar = Calendar::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson("/api/entries/{$entry->id}", [
            'calendar_id' => $otherCalendar->id,
            'text' => 'Still mine',
        ])->assertOk();

        $this->assertSame($calendar->id, $entry->fresh()->calendar_id);
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
