<?php

namespace App\Traits;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A model the client mirrors offline: deletes are soft, so they can be synced.
 */
trait Syncable
{
    use SoftDeletes;

    /**
     * Rows changed at or after the given moment, deleted rows included so the
     * client can remove them locally.
     *
     * Inclusive because timestamps have one-second precision; the client
     * passes back the newest updated_at it received and skips rows it has.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function changedSince(Builder $query, CarbonInterface $since): void
    {
        $query->withTrashed()
            ->where($query->getModel()->getQualifiedUpdatedAtColumn(), '>=', $since->utc());
    }
}
