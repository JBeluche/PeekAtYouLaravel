<?php

namespace App\Policies;

use App\Models\Entry;
use App\Models\User;

/**
 * Entrys carry no owner of their own; access follows the calendar they sit on.
 */
class EntryPolicy
{
    public function view(User $user, Entry $entry): bool
    {
        return $user->can('view', $entry->calendar);
    }

    public function update(User $user, Entry $entry): bool
    {
        return $user->can('update', $entry->calendar);
    }

    public function delete(User $user, Entry $entry): bool
    {
        return $user->can('update', $entry->calendar);
    }
}
