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

    public function test_an_entry_resolves_its_label_through_the_mark_colour(): void
    {
        $calendar = Calendar::factory()->create();
        $label = Label::factory()->create([
            'calendar_id' => $calendar->id,
            'color' => '#ff0000',
            'name' => 'At home',
        ]);

        $entry = Entry::factory()->create([
            'calendar_id' => $calendar->id,
            'mark_color' => '#ff0000',
        ]);

        $this->assertTrue($entry->label->is($label));
        $this->assertSame('At home', $entry->label->name);
    }

    public function test_an_entry_may_have_no_mark_colour_and_no_symbol(): void
    {
        $entry = Entry::factory()->create([
            'mark_color' => null,
            'symbol' => EntrySymbol::None,
        ]);

        $this->assertNull($entry->mark_color);
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
