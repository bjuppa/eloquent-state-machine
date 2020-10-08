<?php

namespace Bjuppa\EloquentStateMachine\Concerns;

trait HasState
{
    use CanLockPessimistically;

    public function dispatchToState($event)
    {
        try {
            return $this->transactionWithRefreshForUpdate(function () use ($event) {
                return tap($this->getState()->dispatch($event), function ($newState) {
                    $this->refresh();
                    if (get_class($this->getState()) != get_class($newState)) {
                        //TODO: throw UnexpectedStateException
                    }
                });
            });
        } finally {
            $this->refresh();
        }
    }

    protected function makeState($classname)
    {
        return new $classname($this);
    }

    /**
     * Always save the model to the database using transaction.
     * @see \Illuminate\Database\Eloquent\Model::save
     * @see \Illuminate\Database\Eloquent\Model::saveOrFail
     *
     * @param  array  $options
     * @return bool
     *
     * @throws \Throwable
     */
    public function save(array $options = [])
    {
        return $this->getConnection()->transaction(function () use ($options) {
            return parent::save($options);
        });
    }

    /**
     * Avoid double transactions
     * @see \Illuminate\Database\Eloquent\Model::saveOrFail
     *
     * @param  array  $options
     * @return bool
     *
     * @throws \Throwable
     */
    public function saveOrFail(array $options = [])
    {
        return $this->save();
    }
}
