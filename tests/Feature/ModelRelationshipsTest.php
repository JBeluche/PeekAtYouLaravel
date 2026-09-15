<?php

namespace Tests\Feature;

use App\Enums\DayMarkStyle;
use App\Enums\EntrySymbol;
use App\Enums\Plan;
use App\Models\Calendar;
use App\Models\Entry;
use App\Models\Label;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_owns_calendars_and_has_one_setting(): void
    {
        $user = User::factory()->create();
        Calendar::factory()->count(2)->create(['owner_id' => $user->id]);
        Setting::factory()->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->calendars);
        $this->assertInstanceOf(Setting::class, $user->setting);
        $this->assertTrue($user->calendars->first()->owner->is($user));
    }

    public function test_user_defaults_to_the_free_plan_with_backups_off(): void
    {
        $user = User::factory()->create();

        $this->assertSame(Plan::Free, $user->plan);
        $this->assertFalse($user->isPro());
        $this->assertFalse($user->backup_on);
        $this->assertFalse($user->signed_in);
        $this->assertNull($user->last_backup_at);
    }

    public function test_a_calendar_has_labels_and_entries(): void
    {
        $calendar = Calendar::factory()->create();
        Label::factory()->count(3)->create(['calendar_id' => $calendar->id]);
        Entry::factory()->count(4)->create(['calendar_id' => $calendar->id]);

        $this->assertCount(3, $calendar->labels);
        $this->assertCount(4, $calendar->entries);
    }

    public function test_an_entry_belongs_to_the_label_it_is_marked_with(): void
    {
        $calendar = Calendar::factory()->create();
        $label = Label::factory()->create([
            'calendar_id' => $calendar->id,
            'color' => '#ff0000',
            'name' => 'At home',
        ]);

        $entry = Entry::factory()->create([
            'calendar_id' => $calendar->id,
            'label_id' => $label->id,
        ]);

        $this->assertTrue($entry->label->is($label));
        $this->assertSame('At home', $entry->label->name);
        $this->assertTrue($label->entries->first()->is($entry));
    }

    public function test_recolouring_a_label_carries_through_to_its_entries(): void
    {
        $label = Label::factory()->create(['color' => '#ff0000']);
        $entry = Entry::factory()->create([
            'calendar_id' => $label->calendar_id,
            'label_id' => $label->id,
        ]);

        $label->update(['color' => '#0000ff']);

        $this->assertSame('#0000ff', $entry->fresh()->label->color);
    }

    public function test_deleting_a_label_unmarks_its_days_without_deleting_them(): void
    {
        $label = Label::factory()->create();
        $entry = Entry::factory()->create([
            'calendar_id' => $label->calendar_id,
            'label_id' => $label->id,
            'text' => 'Keep this note',
        ]);

        $label->delete();

        $this->assertDatabaseHas('entries', ['id' => $entry->id, 'text' => 'Keep this note']);
        $this->assertNull($entry->fresh()->label_id);
        $this->assertNull($entry->fresh()->label);
    }

    public function test_an_entry_may_be_unmarked_and_carry_no_symbol(): void
    {
        $entry = Entry::factory()->create([
            'label_id' => null,
            'symbol' => EntrySymbol::None,
        ]);

        $this->assertNull($entry->label_id);
        $this->assertNull($entry->label);
        $this->assertSame(EntrySymbol::None, $entry->symbol);
    }

    public function test_entry_casts_date_and_symbol(): void
    {
        $entry = Entry::factory()->create([
            'date' => '2026-09-14',
            'symbol' => EntrySymbol::Check,
        ]);

        $this->assertSame('2026-09-14', $entry->fresh()->date->toDateString());
        $this->assertSame(EntrySymbol::Check, $entry->fresh()->symbol);
    }

    public function test_settings_default_to_stroke_day_marks(): void
    {
        $user = User::factory()->create();
        $setting = Setting::query()->create(['user_id' => $user->id]);

        $this->assertSame(DayMarkStyle::Stroke, $setting->fresh()->day_mark_style);
        $this->assertSame(14, $setting->fresh()->note_font_size);
        $this->assertSame('sans', $setting->fresh()->note_font);
    }

    public function test_deleting_a_calendar_cascades_to_labels_and_entries(): void
    {
        $calendar = Calendar::factory()->create();
        Label::factory()->create(['calendar_id' => $calendar->id]);
        Entry::factory()->create(['calendar_id' => $calendar->id]);

        $calendar->delete();

        $this->assertSame(0, Label::query()->count());
        $this->assertSame(0, Entry::query()->count());
    }
}
