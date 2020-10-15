<?php

namespace Bjuppa\EloquentStateMachine\Concerns;

use Bjuppa\EloquentStateMachine\Event;
use Bjuppa\EloquentStateMachine\Exceptions\UnexpectedStateException;
use Bjuppa\EloquentStateMachine\SimpleState;
use Bjuppa\EloquentStateMachine\Support\State;
use Closure;
use Illuminate\Database\Connection;

trait HasState
{
    use CanLockPessimistically;

    abstract public function getState(): SimpleState;

    public function dispatchToState(Event $event): SimpleState
    {
        try {
            return tap(
                $this->transactionWithRefreshForUpdate(function () use ($event) {
                    return tap(
                        $this->getState()->dispatch($event),
                        function (State $destination) use ($event) {
                            $this->refresh();
                            if (!$destination->is($this->getState())) {
                                throw new UnexpectedStateException(
                                    $destination,
                                    $this->getState(),
                                    $event
                                );
                            }
                        }
                    );
                }),
                function () use ($event) {
                    collect($event->getSideEffects())->each(fn (Closure $callback) => $callback());
                }
            );
        } finally {
            $this->refresh();
        }
    }

    protected function makeState($classname): State
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

    abstract public function getConnection(): Connection;
}
