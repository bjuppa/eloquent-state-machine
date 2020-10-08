<?php

namespace Bjuppa\EloquentStateMachine\Concerns;

use Closure;

trait CanLockPessimistically
{
    /**
     * Indicates whether row locking is being used.
     *
     * true: update lock
     * false: shared lock
     * null: no locking (default)
     *
     * @var bool
     */
    protected static $lockPessimistically;

    /**
     * Execute a Closure within a transaction, after refreshing the model and
     * setting an update lock on the database row.
     *
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transactionWithRefreshForUpdate(Closure $callback)
    {
        return $this->getConnection()->transaction(function () use ($callback) {
            $this->refreshForUpdate();
            return $callback();
        });
    }

    /**
     * Reload the current model and set update lock on the database row for the
     * current transaction.
     * @see \Illuminate\Database\Eloquent\Model::refresh()
     *
     * @return $this
     *
     * @throws \Throwable
     */
    public function refreshForUpdate()
    {
        return $this->lockSelectedModelsForUpdate(function () {
            return $this->refresh();
        });
    }

    /**
     * Execute a Closure, adding update locks to selected models.
     *
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Throwable
     */
    public function lockSelectedModelsForUpdate(Closure $callback)
    {
        return $this->lockSelectedModels(true, $callback);
    }

    /**
     * Execute a Closure, adding shared locks to selected models.
     *
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Throwable
     */
    public function lockSelectedModelsForShare(Closure $callback)
    {
        return $this->lockSelectedModels(false, $callback);
    }

    /**
     * Execute a Closure, adding locks to selected models.
     *
     * @param bool $type
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Throwable
     */
    private function lockSelectedModels(bool $type, Closure $callback)
    {
        $previousLock = self::$lockPessimistically;
        self::$lockPessimistically = $type;

        try {
            return $callback();
        } finally {
            self::$lockPessimistically = $previousLock;
        }
    }

    /**
     * Get a new query builder, having locking selects when applicable.
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
