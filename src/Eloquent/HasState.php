<?php

namespace Bjuppa\EloquentStateMachine\Eloquent;

use Bjuppa\EloquentStateMachine\StateEvent;
use Bjuppa\EloquentStateMachine\Exceptions\UnexpectedStateException;
use Bjuppa\EloquentStateMachine\ModelCreatedStateEvent;
use Bjuppa\EloquentStateMachine\RootState;
use Bjuppa\EloquentStateMachine\SimpleState;
use Bjuppa\EloquentStateMachine\Support\State;
use Closure;
use DomainException;
use Exception;
use Illuminate\Database\Connection;
use InvalidArgumentException;

trait HasState
{
    use CanLockPessimistically;

    protected string $rootStateClass;

    abstract public function getState(): SimpleState;

    protected function initialTransitionEvent(): ModelCreatedStateEvent
    {
        return new ModelCreatedStateEvent($this);
    }

    public function dispatchToState(StateEvent $event): SimpleState
    {
        try {
            return tap(
                $this->transactionWithRefreshForUpdate(function () use ($event) {
                    return tap(
                        $this->getState()->dispatch($event),
                        // Validate the new state before committing the transaction
                        function (State $destination) use ($event) {
                            $this->assertStateAfterEvent($destination, $event);
                        }
                    );
                }),
                // Process side effects after committing the transaction
                function () use ($event) {
                    $this->processEventSideEffects($event);
                }
            );
        } catch (Exception $e) {
            // If transaction was aborted, make sure this model matches the database
            $this->refresh();
            throw $e;
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
        $this->processEventSideEffects($event);
    }

    /**
     * Ensure the current state matches the given state.
     * Called after processing an event.
     *
     * @throws UnexpectedStateException
     */
    public function assertStateAfterEvent(State $state, StateEvent $event): void
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

    protected function processEventSideEffects(StateEvent $event)
    {
        collect($event->getSideEffects())->each(fn (Closure $callback) => $callback());
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
