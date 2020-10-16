<?php

namespace Bjuppa\EloquentStateMachine\Concerns;

use Bjuppa\EloquentStateMachine\Event;
use Bjuppa\EloquentStateMachine\Exceptions\UnexpectedStateException;
use Bjuppa\EloquentStateMachine\ModelCreatedEvent;
use Bjuppa\EloquentStateMachine\RootState;
use Bjuppa\EloquentStateMachine\SimpleState;
use Bjuppa\EloquentStateMachine\Support\State;
use Closure;
use DomainException;
use Illuminate\Database\Connection;
use InvalidArgumentException;

trait HasState
{
    use CanLockPessimistically;

    protected string $rootStateClass;

    abstract public function getState(): SimpleState;

    protected function initialTransitionEvent(): ModelCreatedEvent
    {
        return new ModelCreatedEvent($this);
    }

    public function dispatchToState(Event $event): SimpleState
    {
        try {
            return tap(
                $this->transactionWithRefreshForUpdate(function () use ($event) {
                    return tap(
                        $this->getState()->dispatch($event),
                        function (State $destination) use ($event) {
                            $this->assertStateAfterEvent($destination, $event);
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

    protected function makeState(string $classname): State
    {
        if (!is_a($classname, State::class)) {
            throw new InvalidArgumentException(
                $classname . ' is not a ' . State::class
            );
        }
        return new $classname($this);
    }

    protected function rootState(): RootState
    {
        if (!is_a($this->rootStateClass, RootState::class)) {
            throw new DomainException(
                get_class($this) . '::rootStateClass ' . $this->rootStateClass . ' is not a ' . RootState::class
            );
        }
        return $this->makeState($this->rootStateClass);
    }

    protected function initialTransition(): void
    {
        $event = $this->initialTransitionEvent();
        $destination = $this->rootState()->defaultEntry($event);
        $this->assertStateAfterEvent($destination, $event);
    }

    /**
     * Ensure the current state matches the given state.
     * Called after processing an event.
     *
     * @throws UnexpectedStateException
     */
    public function assertStateAfterEvent(State $state, Event $event): void
    {
        $this->refresh();
        if (!$state->is($this->getState())) {
            throw new UnexpectedStateException(
                $state,
                $this->getState(),
                $event
            );
        }
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

    protected static function bootHasState()
    {
        static::created(function ($model) {
            $model->initialTransition();
        });
    }

    abstract public function getConnection(): Connection;
}
