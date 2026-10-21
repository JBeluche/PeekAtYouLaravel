<?php

namespace App\Policies;

use App\Models\Label;
use App\Models\User;

/**
 * Labels carry no owner of their own; access follows the calendar they sit on.
 */
class LabelPolicy
{
    public function view(User $user, Label $label): bool
    {
        return $user->can('view', $label->calendar);
    }

    public function update(User $user, Label $label): bool
    {
        return $user->can('update', $label->calendar);
    }

    public function delete(User $user, Label $label): bool
    {
        return $user->can('update', $label->calendar);
    }
}
