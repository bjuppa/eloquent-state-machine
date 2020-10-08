<?php

namespace Bjuppa\EloquentStateMachine\Concerns;

trait CanLockPessimistically
{
    /**
     * The desired lock type for select statements on this model
     *
     * true: lock for update
     * false: shared lock
     * null: no locking (default)
     */
    protected static $lockPessimistically;

    /**
     * Get a new query builder, having locking selects if desired.
     * @see \Illuminate\Database\Eloquent\Model::newModelQuery
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newModelQuery()
    {
        return tap(parent::newModelQuery(), function ($query) {
            if (self::$lockPessimistically === true) {
                $query->lockForUpdate();
            } elseif (self::$lockPessimistically === false) {
                $query->sharedLock();
            }
        });
    }
}
